<?php


namespace sys\jordan\uhc\gamemodes;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStateChangeEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\gamemodes\defaults\DeathPole;
use sys\jordan\uhc\gamemodes\defaults\NoClean;
use sys\jordan\uhc\gamemodes\defaults\NoFall;
use sys\jordan\uhc\scenario\ScenarioInterface;

class GamemodeManager implements ScenarioInterface {

	use GameTrait;

	/** @var Gamemode[] */
	private $gamemodes = [];

	/**
	 * GamemodeManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
		$this->registerGamemodes();
	}

	public function registerGamemodes(): void {
		$this->addGamemode(new DeathPole($this->getGame()));
		$this->addGamemode(new NoClean($this->getGame()));
		$this->addGamemode(new NoFall($this->getGame()));
	}

	/**
	 * @return Gamemode[]
	 */
	public function getGamemodes(): array {
		return $this->gamemodes;
	}

	/**
	 * @param Gamemode $gamemode
	 */
	public function addGamemode(Gamemode $gamemode): void {
		$this->gamemodes[$gamemode->getName()] = $gamemode;
	}

	/**
	 * @param string $name
	 * @param Event $event
	 */
	public function invoke(string $name, Event $event): void {
		foreach($this->getGamemodes() as $gamemode) {
			$gamemode->$name($event);
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		if($event->getGame() === $this->getGame()) {
			$this->invoke(__FUNCTION__, $event);
		}
	}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {
		if($event->getGame() === $this->getGame()) {
			$this->invoke(__FUNCTION__, $event);
		}
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function handleCommand(PlayerCommandPreprocessEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param FurnaceSmeltEvent $event
	 */
	public function handleSmelt(FurnaceSmeltEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerMoveEvent $event
	 * @return mixed
	 */
	public function handleMove(PlayerMoveEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerToggleFlightEvent $event
	 * @return mixed
	 */
	public function handleToggleFlight(PlayerToggleFlightEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param GameStateChangeEvent $event
	 */
	public function handleGameStateChange(GameStateChangeEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void {
		$this->invoke(__FUNCTION__, $event);
	}
}