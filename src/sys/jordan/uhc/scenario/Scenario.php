<?php


namespace sys\jordan\uhc\scenario;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntitySpawnEvent;
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
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStateChangeEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\GamePlayer;

abstract class Scenario implements ScenarioInterface {

	use GameTrait;

	/** @var int */
	public const PRIORITY_LOW = 1;
	/** @var int */
	public const PRIORITY_MEDIUM = 2;
	/** @var int */
	public const PRIORITY_HIGH = 3;

	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/** @var int */
	private $priority;

	/**
	 * Scenario constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $priority
	 */
	public function __construct(string $name, string $description, int $priority = self::PRIORITY_MEDIUM) {
		$this->name = $name;
		$this->description = $description;
		$this->priority = $priority;
	}

	/**
	 * @return string
	 */
	public function asPrefix(): string {
		return TextFormat::RED . "[{$this->getName()}]";
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * @param GamePlayer $player
	 * @param string $message
	 */
	public function sendMessage(GamePlayer $player, string $message): void {
		$player->sendMessage($this->asPrefix() . " " . TextFormat::RESET . $message);
	}

	/**
	 * @param Game $game
	 */
	abstract public function onEnable(Game $game): void;

	/**
	 * @param Game $game
	 */
	abstract public function onDisable(Game $game): void;

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function handleCommand(PlayerCommandPreprocessEvent $event): void {}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void {}

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void {}

	/**
	 * @param FurnaceSmeltEvent $event
	 */
	public function handleSmelt(FurnaceSmeltEvent $event): void {}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {}

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void {}

	/**
	 * @param PlayerMoveEvent $event
	 */
	public function handleMove(PlayerMoveEvent $event): void {}

	/**
	 * @param PlayerToggleFlightEvent $event
	 */
	public function handleToggleFlight(PlayerToggleFlightEvent $event): void {}

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {}

	/**
	 * @param GameStateChangeEvent $event
	 */
	public function handleGameStateChange(GameStateChangeEvent $event): void {}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {}

	/**
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void {}

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void {}

}