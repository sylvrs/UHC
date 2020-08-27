<?php


namespace sys\jordan\uhc\gamemodes\defaults;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\gamemodes\Gamemode;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\TickEnum;

class NoClean extends Gamemode {

	/** @var int */
	public const LENGTH = 15;

	/** @var array */
	private $tasks = [];

	/**
	 * NoClean constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct($game, "No Clean");
	}

	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		$this->clearTasks();
	}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {
		$this->clearTasks();
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
			if($damager instanceof GamePlayer && $damager->inGame() && $damager->getGame()->isAlive($damager)) {
				$this->createTask($damager);
			}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		if(!$event instanceof EntityDamageByEntityEvent) {
			return;
		}
		$victim = $event->getEntity();
		$damager = $event->getDamager();
		if($victim instanceof GamePlayer && $this->hasTask($victim)) {
			$event->setCancelled();
		}

		if($damager instanceof GamePlayer && $this->hasTask($damager) && !$event->isCancelled()) {
			$this->cancelTask($damager);
			$this->sendMessage($damager, "You have lost your invulnerability!");
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->isAlive($player)) {
			if($this->hasTask($player)) {
				$this->cancelTask($player);
			}
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function createTask(GamePlayer $player): void {
		$this->addTask($player, new class($this, $player) extends BaseTask {

			/** @var NoClean */
			private $gamemode;

			/** @var GamePlayer */
			private $player;

			/** @var int */
			private $time = NoClean::LENGTH;

			public function __construct(NoClean $gamemode, GamePlayer $player) {
				parent::__construct($gamemode->getGame()->getPlugin());
				$this->gamemode = $gamemode;
				$this->player = $player;
			}

			/**
			 * @return NoClean
			 */
			public function getGamemode(): NoClean {
				return $this->gamemode;
			}

			/**
			 * @return GamePlayer
			 */
			public function getPlayer(): GamePlayer {
				return $this->player;
			}

			/**
			 * Actions to execute when run
			 *
			 * @param int $currentTick
			 * @return void
			 */
			public function onRun(int $currentTick): void {
				$this->time--;
				$extradata = $this->getGamemode()->getGame()->getScoreboardManager()->getExtraData($this->getPlayer());
				$extradata->setData(0, TextFormat::WHITE . "No Clean: " . TextFormat::YELLOW . $this->time);
				if($this->time <= 0) {
					$this->getGamemode()->cancelTask($this->getPlayer());
					if(($player = $this->getPlayer()) instanceof GamePlayer && $player->isOnline()) {
						$this->getGamemode()->sendMessage($player, "Your invulnerability has worn off");
						$this->cancel();
					}
				}
			}

			public function cancel(): void {
				if($this->player !== null && $this->getPlayer()->isOnline() && $this->getPlayer()->isValid()) {
					$extradata = $this->getGamemode()->getGame()->getScoreboardManager()->getExtraData($this->getPlayer());
					$extradata->removeData(0);
					$this->getPlayer()->getScoreboard()->clearLines();
					$this->getGamemode()->getGame()->getScoreboardManager()->send($this->getPlayer());
				}
				parent::cancel();
			}
		});
	}

	/**
	 * @param GamePlayer $player
	 * @return BaseTask|null
	 */
	public function getTask(GamePlayer $player): ?BaseTask {
		return $this->tasks[$player->getLowerCaseName()] ?? null;
	}

	/**
	 * @param GamePlayer $player
	 * @param BaseTask $task
	 */
	public function addTask(GamePlayer $player, BaseTask $task): void {
		$this->cancelTask($player);
		$this->tasks[$player->getLowerCaseName()] = $task;
		$task->schedule(TickEnum::SECOND);
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function hasTask(GamePlayer $player): bool {
		return isset($this->tasks[$player->getLowerCaseName()]);
	}

	/**
	 * @param GamePlayer $player
	 */
	public function cancelTask(GamePlayer $player): void {
		if($this->hasTask($player)) {
			$task = $this->getTask($player);
			$task->cancel();
			unset($this->tasks[$player->getLowerCaseName()]);
		}
	}

	public function clearTasks(): void {
		foreach($this->tasks as $key => $task) {
			$task->cancel();
			unset($this->tasks[$key]);
		}
	}

	/**
	 * @param GamePlayer $player
	 * @param string $message
	 */
	public function sendMessage(GamePlayer $player, string $message): void {
		$player->sendMessage($this->asPrefix() . TextFormat::YELLOW . " $message");
	}

}