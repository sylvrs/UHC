<?php

namespace sys\jordan\uhc\scenario;

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

class ScenarioList implements ScenarioInterface {

	use GameTrait;

	/** @var Scenario[] */
	private $scenarios = [];

	/**
	 * ScenarioList constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param Scenario $scenarioBase
	 */
	public function addScenario(Scenario $scenarioBase): void {
		if(!$this->isScenario($scenarioBase)) {
			$scenario = clone $scenarioBase;
			$this->scenarios[$scenario->getName()] = $scenario;
			$scenario->onEnable($this->getGame());
			$this->sort();
		}
	}

	/**
	 * @param Scenario $scenario
	 * @return bool
	 */
	public function isScenario(Scenario $scenario): bool {
		return isset($this->scenarios[$scenario->getName()]);
	}

	/**
	 * @param Scenario $scenarioBase
	 */
	public function removeScenario(Scenario $scenarioBase): void {
		if($this->isScenario($scenarioBase)) {
			$scenario = $this->scenarios[$scenarioBase->getName()];
			$scenario->onDisable($this->getGame());
			unset($this->scenarios[$scenarioBase->getName()]);
		}
	}

	/**
	 * @param string $name
	 * @return Scenario|null
	 */
	public function getScenario(string $name): ?Scenario {
		return $this->scenarios[$name] ?? null;
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array {
		return $this->scenarios;
	}

	/**
	 * Sorts the scenarios by priority
	 */
	public function sort() {
		uasort($this->scenarios, function (Scenario $firstScenario, Scenario $secondScenario) {
			return $firstScenario->getPriority() <=> $secondScenario->getPriority();
		});
	}

	/**
	 * @return Scenario[]
	 */
	public function getNameSortedArray(): array {
		$array = $this->getScenarios();
		uasort($array, function (Scenario $firstScenario, Scenario $secondScenario) {
			return $firstScenario->getName() <=> $secondScenario->getName();
		});
		return $array;
	}

	/**
	 * @param string $methodName
	 * @param Event $event
	 * @return Event
	 */
	private function call(string $methodName, Event $event) {
		if($this->getGame()->hasStarted()) {
			foreach($this->getScenarios() as $scenario) {
				$scenario->$methodName($event);
			}
		}
		return $event;
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function handleCommand(PlayerCommandPreprocessEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param FurnaceSmeltEvent $event
	 */
	public function handleSmelt(FurnaceSmeltEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleMove(PlayerMoveEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	public function handleToggleFlight(PlayerToggleFlightEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param GameStateChangeEvent $event
	 */
	public function handleGameStateChange(GameStateChangeEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		$this->call(__FUNCTION__, $event);
	}

	/**
	 * @return array
	 */
	public function serialize(): array {
		return array_values(array_map(function (Scenario $scenario): string {
			return $scenario->getName();
		}, $this->getNameSortedArray()));
	}
}