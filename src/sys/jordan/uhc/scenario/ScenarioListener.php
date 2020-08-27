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
use pocketmine\Player;
use sys\jordan\core\base\BaseListener;
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStateChangeEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;

class ScenarioListener extends BaseListener implements ScenarioInterface {

	/**
	 * ScenarioListener constructor.
	 * @param GameBase $plugin
	 */
	public function __construct(GameBase $plugin) {
		parent::__construct($plugin);
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleBreak($event);
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handlePlace($event);
		}
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function handleCommand(PlayerCommandPreprocessEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleCommand($event);
		}
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleConsume($event);
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getTransaction()->getSource();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleTransaction($event);
		}
	}

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleCraft($event);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		if($player instanceof GamePlayer) {
			if($player->inGame() && $player->getGame()->isAlive($player)) {
				$uhc = $player->getGame();
				$uhc->getScenarioList()->handleDamage($event);
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority LOW
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleDeath($event);
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		if($event->getEntity() instanceof Player) {
			return;
		}
		$level = $event->getEntity()->getLevelNonNull();
		/** @var GameBase $plugin */
		$plugin = $this->getPlugin();
		if($plugin->getGameManager()->hasGame($level)) {
			$plugin->getGameManager()->getGameByLevel($level)->getScenarioList()->handleEntityDeath($event);
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleInteract($event);
		}
	}

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleJump($event);
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 */
	public function handleMove(PlayerMoveEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleMove($event);
		}
	}

	/**
	 * @param PlayerToggleFlightEvent $event
	 */
	public function handleToggleFlight(PlayerToggleFlightEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleToggleFlight($event);
		}
	}

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		$event->getGame()->getScenarioList()->handleStart($event);
	}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {
		$event->getGame()->getScenarioList()->handleStop($event);
	}

	/**
	 * @param FurnaceSmeltEvent $event
	 */
	public function handleSmelt(FurnaceSmeltEvent $event): void {
		$level = $event->getFurnace()->getLevelNonNull();
		/** @var GameBase $plugin */
		$plugin = $this->getPlugin();
		if($plugin->getGameManager()->hasGame($level)) {
			$plugin->getGameManager()->getGameByLevel($level)->getScenarioList()->handleSmelt($event);
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleJoin($event);
		}
	}

	/**
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void {
		$level = $event->getEntity()->getLevelNonNull();
		/** @var GameBase $plugin */
		$plugin = $this->getPlugin();
		if($plugin->getGameManager()->hasGame($level)) {
			$plugin->getGameManager()->getGameByLevel($level)->getScenarioList()->handleEntitySpawn($event);
		}
	}

	/**
	 * @param GameStateChangeEvent $event
	 */
	public function handleGameStateChange(GameStateChangeEvent $event): void {
		$event->getGame()->getScenarioList()->handleGameStateChange($event);
	}

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void {
		$event->getGame()->getScenarioList()->handleGameRespawn($event);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			$uhc = $player->getGame();
			$uhc->getScenarioList()->handleQuit($event);
		}
	}
}