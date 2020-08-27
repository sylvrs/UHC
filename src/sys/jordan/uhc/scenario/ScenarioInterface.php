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
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStateChangeEvent;
use sys\jordan\uhc\event\GameStopEvent;

interface ScenarioInterface {

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void;

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void;

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function handleCommand(PlayerCommandPreprocessEvent $event): void;

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void;

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void;

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void;

	/**
	 * @param FurnaceSmeltEvent $event
	 */
	public function handleSmelt(FurnaceSmeltEvent $event): void;

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void;

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void;

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void;

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void;

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void;

	/**
	 * @param PlayerMoveEvent $event
	 * @return mixed
	 */
	public function handleMove(PlayerMoveEvent $event): void;

	/**
	 * @param PlayerToggleFlightEvent $event
	 * @return mixed
	 */
	public function handleToggleFlight(PlayerToggleFlightEvent $event): void;

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void;

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void;

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void;

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void;

	/**
	 * @param GameStateChangeEvent $event
	 */
	public function handleGameStateChange(GameStateChangeEvent $event): void;


	/**
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void;

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void;
}