<?php


namespace sys\jordan\uhc\game;

use pocketmine\block\Leaves;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\item\Shears;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;

class GameEventHandler {

	use GameTrait;

	/**
	 * GameEventHandler constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param PlayerChatEvent $event
	 */
	public function handleChat(PlayerChatEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		$game = $player->getGame();
		if($game->getSettings()->isGlobalMuteEnabled() && !$player->hasPermission(GamePermissions::GLOBAL_MUTE)) {
			$player->sendMessage(TextFormat::RED . "You can't speak while global-mute is enabled!");
			$event->setCancelled();
			return;
		}
		if($game->isTeams() && $player->inTeam()) {
			$event->setFormat($player->getTeam()->getFormat() . " " . $event->getFormat());
		}
		if($game->isHost($player)) {
			$event->setFormat(GameValues::$HOST_FORMAT . " {$event->getFormat()}");
		} else if($game->isSpectator($player)) {
			$event->setFormat(GameValues::$SPECTATOR_FORMAT . " {$event->getFormat()}");
		}
		if($game->isSpectator($player) && $game->getManager()->getCount() > 1 && $game->hasStarted() && !($game->isHost($player) || $player->hasPermission("valiant.permission.globalmute"))) {
			$event->setRecipients($game->getSpectators() + [$game->getHost()->getName() => $game->getHost()]);
		} else {
			$event->setRecipients($game->getAll());
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		$this->getGame()->join($player);
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if ($this->getGame()->getManager()->isPlayer($player) && $this->getGame()->getState() <= GameStatus::COUNTDOWN) {
			$event->setCancelled();
		}
		if (!$event->isCancelled() && !$player->isCreative()) {
			$block = $event->getBlock();
			if ($block instanceof Leaves && $block->canDropApples()) {
				$max = 100;
				if ($event->getItem() instanceof Shears) $max /= 1.5;
				if (mt_rand(0, $max) <= $this->getGame()->getSettings()->getAppleRate()) {
					$event->setDropsVariadic(Item::get(Item::APPLE));
				}
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getManager()->isPlayer($player) && $this->getGame()->getState() <= GameStatus::COUNTDOWN) {
			$event->setCancelled();
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function handleExhaust(PlayerExhaustEvent $event) {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->getManager()->isPlayer($player) && $this->getGame()->getState() <= GameStatus::COUNTDOWN) {
			$event->setCancelled();
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$victim = $event->getEntity();
		if($this->getGame()->getState() <= GameStatus::COUNTDOWN || $this->getGame()->getState() >= GameStatus::POSTGAME) {
			$event->setCancelled();
		} else {
			if($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				if($damager instanceof GamePlayer) {
					if(!$damager->inGame() || $this->getGame()->isGrace()) {
						$event->setCancelled();
					} else {
						$this->getGame()->getManager()->handleDamage($event);
					}
				}
			}
			$this->getGame()->getGamemodeManager()->handleDamage($event);
		}
		if(!$event->isCancelled() && $victim instanceof GamePlayer) {
			$victim->setScoreTag($victim->getHealthString());
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		$cause = $player->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if($damager instanceof GamePlayer) {
				$this->getGame()->getEliminationsManager()->addElimination($damager);
			}
		}
		$this->getGame()->getInventoriesManager()->addInventoryContents($player->getName(), $player->getArmorInventory()->getContents(), $player->getInventory()->getContents());
		$this->getGame()->summonLightning($player);
		$message = $event->getDeathMessage();
		$message->setText($message->getText());
		if($message instanceof TranslationContainer) {
			foreach($message->getParameters() as $key => $parameter) {
				$parameter = $parameter . (TextFormat::WHITE . "[" . TextFormat::WHITE . $this->getGame()->getEliminationsManager()->getEliminations($parameter) . TextFormat::WHITE . "]");
				$message->setParameter($key, TextFormat::YELLOW . $parameter . TextFormat::WHITE);
			}
		}
		$this->getGame()->broadcast($this->getGame()->getPlugin()->getServer()->getLanguage()->translate($message) . "!");
		$event->setDeathMessage(null);
		$this->getGame()->getManager()->handleDeath($event);
		$this->getGame()->getGamemodeManager()->handleDeath($event);
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		/** @var DisconnectedPlayerMob $entity */
		$entity = $event->getEntity();
		if($this->getGame()->getPlugin()->getServer()->getPlayer($entity->getDisconnectedPlayer()->getName()) instanceof GamePlayer) {
			$event->setDrops([]);
			return;
		}
		$cause = $entity->getLastDamageCause();
		$message = "";
		$name = TextFormat::YELLOW . $entity->getDisconnectedPlayer()->getName() . TextFormat::WHITE . "[" . TextFormat::WHITE . $entity->getDisconnectedPlayer()->getGame()->getEliminationsManager()->getEliminations($entity->getDisconnectedPlayer()->getName()) . TextFormat::WHITE . "]" . TextFormat::YELLOW . " (AFK)" . TextFormat::WHITE;
		if($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if($damager instanceof GamePlayer && $damager->inGame()) {
				if($this->getGame()->getManager()->isPlayer($damager)) {
					$damager->getGame()->getEliminationsManager()->addElimination($damager);
				}
				$message = $name . TextFormat::WHITE . " was killed by " . TextFormat::YELLOW . $damager->getName() . TextFormat::WHITE . "[" . TextFormat::WHITE . $damager->getGame()->getEliminationsManager()->getEliminations($damager) . TextFormat::WHITE . "]!";
			}
		} else {
			$message = $name . " has died!";
		}
		$this->getGame()->summonLightning($entity);
		$this->getGame()->getDisconnectedManager()->removeDisconnected($entity->getDisconnectedPlayer()->getName());
		$this->getGame()->broadcast($message);
		$this->getGame()->getManager()->handleEntityDeath($event);
		$this->getGame()->getGamemodeManager()->handleEntityDeath($event);
	}

	/**
	 * @param PlayerRespawnEvent $event
	 */
	public function handleRespawn(PlayerRespawnEvent $event) {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($this->getGame()->createSpectator($player)) {
			$event->setRespawnPosition(Position::fromObject($this->getGame()->getLevel()->getSpawnLocation()->add(0, 25), $this->getGame()->getLevel()));
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		$this->getGame()->leave($player);
		$this->getGame()->getGamemodeManager()->handleQuit($event);
	}

	/**
	 * @param EntityRegainHealthEvent $event
	 */
	public function handleRegeneration(EntityRegainHealthEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getEntity();
		if($event->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION) {
			$event->setCancelled();
		}
		if(!$event->isCancelled()) {
			$player->setScoreTag($player->getHealthString());
		}
	}

}