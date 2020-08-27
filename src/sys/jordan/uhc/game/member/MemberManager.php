<?php


namespace sys\jordan\uhc\game\member;


use Exception;
use pocketmine\block\BlockIds;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\game\task\CalculatePositionsTask;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use function floor;
use function in_array;
use function mt_rand;

abstract class MemberManager {

	use GameTrait;

	/** @var bool[] */
	private $dead = [];

	/** @var Position[] */
	private $scatterPositions = [];

	/**
	 * MemberManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	abstract public function addPlayer(GamePlayer $player): bool;

	/**
	 * @param Player|string $player
	 * @return bool
	 */
	abstract public function isPlayer($player): bool;

	/**
	 * @param Player|string $player
	 * @return bool
	 */
	abstract public function removePlayer($player): bool;

	/**
	 * @param GamePlayer $player
	 */
	abstract public function updateInstance(GamePlayer $player): void;

	/**
	 * @return GamePlayer[]
	 */
	abstract public function getPlayers(): array;

	/**
	 * @return GamePlayer[]
	 */
	public function getOnlinePlayers(): array {
		return array_filter($this->getPlayers(), function (GamePlayer $player): bool {
			return $player->isOnline() && $player->hasSpawned() && $player->isValid();
		});
	}

	abstract public function getMembers(): array;

	/**
	 * @return array
	 */
	abstract public function getRemaining(): array;

	public function removeNullifiedPlayers(): void {
		foreach($this->getPlayers() as $name => $player) {
			if($player === null || ($player instanceof GamePlayer && ($player->getPlayer() === null || !$player->getPlayer()->isOnline()))) {
				$this->removePlayer($name);
			}
		}
	}

	/**
	 * @return int
	 */
	public function getCount(): int {
		return count($this->getRemaining());
	}

	public function checkPlayers(): void {
		foreach($this->getPlayers() as $player) {
			if($this->getGame()->getSettings()->isMobileOnly() && !$player->isMobile()) {
				$this->removePlayer($player);
				if($this->getGame()->createSpectator($player)) {
					$player->sendMessage(TextFormat::RED . "You have been transferred to a spectator due to being on a non-mobile device!");
				}
			}
		}
	}

	public function calculateScatterPositions(): void {
		new CalculatePositionsTask($this->getGame());
	}

	/**
	 * @param Team|GamePlayer $member
	 * @param Position $position
	 */
	public function addScatterPosition($member, Position $position): void {
		$this->scatterPositions[$member->getName()] = $position;
	}

	/**
	 * @param Team|GamePlayer $member
	 * @return bool
	 */
	public function hasScatterPosition($member): bool {
		return isset($this->scatterPositions[$member->getName()]);
	}

	/**
	 * @param Team|GamePlayer $member
	 * @return Position
	 */
	public function getScatterPosition($member): Position {
		return $this->scatterPositions[$member->getName()];
	}

	/**
	 * @param Team|GamePlayer $member
	 */
	public function removeScatterPosition($member): void {
		if($this->hasScatterPosition($member)) {
			unset($this->scatterPositions[$member->getName()]);
		}
	}

	/**
	 * @param GamePlayer|Team $member
	 * @param bool $giveEffects
	 * @param bool $isRespawn
	 * @param callable|null $done
	 */
	public function scatter($member, bool $giveEffects = true, bool $isRespawn = false, callable $done = null): void {
		if($this->hasScatterPosition($member)) {
			$position = $this->getScatterPosition($member);
			if(!$position->getLevel()->isChunkLoaded($position->getFloorX() >> 4, $position->getFloorZ() >> 4)) {
				$position->getLevel()->loadChunk($position->getFloorX() >> 4, $position->getFloorZ() >> 4);
			}
			$position->y = $position->getLevel()->getHighestBlockAt($position->getFloorX(), $position->getFloorZ()) + 1;
			if($position->y <= 1) {
				$position->y = 128;
			}
			$member->teleport($position);
			$member->reset();
			if($giveEffects) $member->giveCountdownEffects();
			$this->removeScatterPosition($member);
			if($done !== null) ($done)();
		}
	}

	/**
	 * @param GamePlayer $member
	 * @param callable $done
	 * @return bool
	 */
	public function calculate($member, callable $done): bool {
		try {
			$size = $this->getGame()->getBorder()->getSize();
			$x = mt_rand(-$size, $size);
			$z = mt_rand(-$size, $size);
			$level = $this->getGame()->getLevel();
			$y = $level->getHighestBlockAt($x, $z);
			if($y <= 0) $y = 128;
			if(in_array($level->getBlockIdAt($x, $y, $z), [BlockIds::FLOWING_WATER, BlockIds::WATER, BlockIds::FLOWING_LAVA, BlockIds::LAVA]) || $y <= 0) {
				$this->calculate($member, $done);
				return false;
			}
			($done)(new Position(floor($x) + 0.5, $y + 1, floor($z) + 0.5, $level));
			return true;
		} catch(Exception $exception) {}
		return false;
	}

	/**
	 * @return array
	 */
	abstract public function getScoreboardData(): array;


	abstract public function setup(): void;

	abstract public function stop(): void;

	abstract public function check(): void;

	/**
	 * @param EntityDamageEvent $event
	 * @priority HIGHEST
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			if($damager instanceof GamePlayer) {
				if($damager->inGame() && (!$damager->getGame()->getManager()->isPlayer($damager) || $this->isDead($damager)) && !$damager->getGame()->isHost($damager)) {
					$event->setCancelled();
				}
			}
		}
		if($player instanceof DisconnectedPlayerMob) {
			$player = $player->getDisconnectedPlayer()->getName();
		}
		if($this->isPlayer($player) && $this->isDead($player)) {
			$event->setCancelled();
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority MONITOR
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($this->isPlayer($player) && !$this->isDead($player)) {
			$this->addDead($player);
			$this->check();
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		/** @var DisconnectedPlayerMob $entity */
		$entity = $event->getEntity();
		if($this->isPlayer($entity->getDisconnectedPlayer()->getName()) && !$this->isDead($entity->getDisconnectedPlayer()->getName())) {
			$this->addDead($entity->getDisconnectedPlayer()->getName());
			$this->check();
		}
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function isDead($player): bool {
		if($player instanceof GamePlayer) $player = $player->getLowerCaseName();
		return isset($this->dead[strtolower($player)]);
	}

	/**
	 * @param GamePlayer|string $player
	 */
	public function addDead($player): void {
		if($player instanceof GamePlayer) $player = $player->getLowerCaseName();
		if(!$this->isDead($player)) {
			$this->dead[strtolower($player)] = true;
		}
	}

	/**
	 * @return bool[]
	 */
	public function getDead(): array {
		return $this->dead;
	}

	/**
	 * @param GamePlayer|string $player
	 */
	public function removeFromDead($player): void {
		if($player instanceof GamePlayer) $player = $player->getLowerCaseName();
		if($this->isDead($player)) {
			unset($this->dead[strtolower($player)]);
		}
	}


	public function clearDead(): void {
		$this->dead = [];
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getAlive(): array {
		return array_filter($this->getPlayers(), function (GamePlayer $player): bool {
			return !$this->isDead($player);
		});
	}


}