<?php


namespace sys\jordan\uhc\game\respawn;


use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\EffectScenario;
use function array_filter;
use function array_key_first;
use function count;

class RespawnManager {

	use GameTrait;

	/** @var array */
	private $queue = [];

	/**
	 * RespawnManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @return array
	 */
	public function getQueue(): array {
		return $this->queue;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function inQueue(string $name): bool {
		return isset($this->queue[strtolower($name)]);
	}

	/**
	 * @param string $name
	 */
	public function addToQueue(string $name): void {
		if(!$this->inQueue($name)) {
			$this->queue[strtolower($name)] = true;
		}
	}

	/**
	 * @param string $name
	 */
	public function removeFromQueue(string $name): void {
		if($this->inQueue($name)) {
			unset($this->queue[strtolower($name)]);
		}
	}

	public function resetQueue(): void {
		$this->queue = [];
	}

	/**
	 * @param GamePlayer $player
	 */
	public function respawn(GamePlayer $player): void {
		if(!$this->getGame()->getManager()->isPlayer($player)) {
			$this->getGame()->getManager()->addPlayer($player);
		}
		$this->getGame()->removeSpectator($player);
		$this->getGame()->getManager()->removeFromDead($player);
		$this->removeFromQueue($player->getName());
		$player->setGame($this->getGame());
		$player->reset();
		$needsTeleport = true;
		if($player->getGame()->isTeams() && $player->inTeam()) {
			$teammates = array_filter($player->getTeam()->getPlayers(), function (GamePlayer $teammate) use($player): bool {
				return ($player !== $teammate) && !$this->getGame()->getManager()->isDead($teammate);
			});
			if(count($teammates) > 0) {
				$player->teleport($teammates[array_key_first($teammates)]);
				$needsTeleport = false;
			}
		}
		if($needsTeleport) $this->getGame()->getManager()->calculate($player, function (Position $position) use($player): void {
			$player->teleport($position);
		});
		foreach($this->getGame()->getScenarioList()->getScenarios() as $scenario) {
			if($scenario instanceof EffectScenario) {
				$scenario->giveEffects($player);
			}
		}
		$pastCountdown = $this->getGame()->getState() > GameStatus::COUNTDOWN;
		$player->setImmobile(!$pastCountdown);
		if($player->getGame()->getInventoriesManager()->hasInventoryContents($player->getName())) {
			$inventoryContents = $player->getGame()->getInventoriesManager()->getPlayerInventoryContents($player->getName());
			$player->getArmorInventory()->setContents(array_map(function (array $data): Item {
				return Item::jsonDeserialize($data);
			}, $inventoryContents["armorContents"]));
			$player->getInventory()->setContents(array_map(function (array $data): Item {
				return Item::jsonDeserialize($data);
			}, $inventoryContents["contents"]));
			$player->sendMessage(TextFormat::GREEN . "You have received your last known items!");
		} else {
			if($pastCountdown) {
				$player->getInventory()->setContents($this->getGame()->getStartingItems());
				$player->sendMessage(TextFormat::GREEN . "You have been given the starting items!");
			}
		}
		(new GameRespawnEvent($this->getGame(), $player))->call();
		$player->sendMessage(TextFormat::GREEN . "You have been respawned. Good luck!");
	}

}