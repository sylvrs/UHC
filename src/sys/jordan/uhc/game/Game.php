<?php

namespace sys\jordan\uhc\game;


use Exception;
use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\utils\BossBar;
use sys\jordan\uhc\border\Border;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStateChangeEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\game\eliminations\EliminationManager;
use sys\jordan\uhc\game\inventory\GameInventoriesManager;
use sys\jordan\uhc\game\member\MemberManager;
use sys\jordan\uhc\game\member\PlayerManager;
use sys\jordan\uhc\game\member\TeamManager;
use sys\jordan\uhc\game\respawn\RespawnManager;
use sys\jordan\uhc\game\scoreboard\ScoreboardManager;
use sys\jordan\uhc\game\task\RandomTeleportTask;
use sys\jordan\uhc\gamemodes\GamemodeManager;
use sys\jordan\uhc\player\DisconnectedPlayerManager;
use sys\jordan\uhc\scenario\EffectScenario;
use sys\jordan\uhc\scenario\ScenarioList;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\GameBaseTrait;
use sys\jordan\uhc\utils\TickEnum;
use sys\jordan\uhc\utils\UHCUtilities;

class Game {

	use GameBaseTrait;

	/** @var Level */
	private $level;
	/** @var Border */
	private $border;

	/** @var GameFeed */
	private $feed;
	/** @var GameHeartbeat */
	private $heartbeat;
	/** @var GameEventHandler */
	private $handler;

	/** @var GamePlayer */
	private $host;
	/** @var MemberManager */
	private $manager;
	/** @var GamePlayer[] */
	private $spectators = [];

	/** @var DisconnectedPlayerManager */
	private $disconnectedManager;
	/** @var EliminationManager */
	private $eliminationsManager;
	/** @var GameInventoriesManager */
	private $inventoriesManager;
	/** @var GamemodeManager */
	private $gamemodeManager;
	/** @var RespawnManager */
	private $respawnManager;
	/** @var ScoreboardManager */
	private $scoreboardManager;

	/** @var ScenarioList */
	private $list;
	/** @var GameSettings */
	private $settings;

	/** @var int */
	private $state = GameStatus::WAITING;

	/** @var int */
	private $countdown;
	/** @var int */
	private $time = 0;
	/** @var int */
	private $postgame = 60;
	/** @var bool */
	private $started = false;
	/** @var bool */
	private $hasFinishedShrinking = false;

	/** @var GameLogger */
	private $logger;

	/**
	 * Game constructor.
	 * @param GameBase $plugin
	 * @param Level $level
	 * @param GamePlayer $host
	 * @param int $size
	 * @param bool $createBorder
	 * @param bool $teams
	 * @throws Exception
	 */
	public function __construct(GameBase $plugin, Level $level, GamePlayer $host, int $size = Border::DEFAULT_SIZE, bool $createBorder = true, bool $teams = false) {
		$this->setPlugin($plugin);
		$this->level = $level;
		$this->border = new Border($this, $size, $createBorder);
		$this->countdown = GameValues::$COUNTDOWN_LENGTH;
		$this->setHost($host);
		$host->getScoreboard()->clearLines();
		$this->list = new ScenarioList($this);
		if($teams) {
			$this->manager = new TeamManager($this);
		} else {
			$this->manager = new PlayerManager($this);
		}
		$this->feed = new GameFeed($this);
		$this->heartbeat = new GameHeartbeat($this);
		$this->getHeartbeat()->schedule(TickEnum::SECOND);
		$this->createManagers();
		$this->handler = new GameEventHandler($this);
		$this->settings = new GameSettings;
		$this->logger = new GameLogger($this);
		if($level instanceof Level) {
			$this->setupLevel();
		}
	}

	public function createManagers(): void {
		$this->disconnectedManager = new DisconnectedPlayerManager($this);
		$this->eliminationsManager = new EliminationManager($this);
		$this->inventoriesManager = new GameInventoriesManager($this);
		$this->gamemodeManager = new GamemodeManager($this);
		$this->respawnManager = new RespawnManager($this);
		$this->scoreboardManager = new ScoreboardManager($this);
	}

	/**
	 * @return Level
	 */
	public function getLevel(): Level {
		return $this->level;
	}

	/**
	 * @return Border
	 */
	public function getBorder(): Border {
		return $this->border;
	}

	/**
	 * @return DisconnectedPlayerManager
	 */
	public function getDisconnectedManager(): DisconnectedPlayerManager {
		return $this->disconnectedManager;
	}

	/**
	 * @return EliminationManager
	 */
	public function getEliminationsManager(): EliminationManager {
		return $this->eliminationsManager;
	}

	/**
	 * @return GameInventoriesManager
	 */
	public function getInventoriesManager(): GameInventoriesManager {
		return $this->inventoriesManager;
	}

	/**
	 * @return GamemodeManager
	 */
	public function getGamemodeManager(): GamemodeManager {
		return $this->gamemodeManager;
	}

	/**
	 * @return RespawnManager
	 */
	public function getRespawnManager(): RespawnManager {
		return $this->respawnManager;
	}

	/**
	 * @return ScoreboardManager
	 */
	public function getScoreboardManager(): ScoreboardManager {
		return $this->scoreboardManager;
	}

	/**
	 * @return ScenarioList
	 */
	public function getScenarioList(): ScenarioList {
		return $this->list;
	}

	/**
	 * @return GameFeed
	 */
	public function getFeed(): GameFeed {
		return $this->feed;
	}

	/**
	 * @return GameHeartbeat
	 */
	public function getHeartbeat(): GameHeartbeat {
		return $this->heartbeat;
	}

	/**
	 * @return GameEventHandler
	 */
	public function getHandler(): GameEventHandler {
		return $this->handler;
	}

	/**
	 * @return GameSettings
	 */
	public function getSettings(): GameSettings {
		return $this->settings;
	}

	/**
	 * @return GameLogger
	 */
	public function getLogger(): GameLogger {
		return $this->logger;
	}

	/**
	 * @return Item[]
	 */
	public function getStartingItems(): array {
		return [
			Item::get(Item::STEAK, 0, 64),
			Item::get(Item::LEATHER, 0, 32)
		];
	}

	/**
	 * @return MemberManager
	 */
	public function getManager(): MemberManager {
		return $this->manager;
	}

	/**
	 * @return bool
	 */
	public function isTeams(): bool {
		return $this->manager instanceof TeamManager;
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getSpectators(): array {
		return $this->spectators;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function addSpectator(GamePlayer $player): void {
		$this->spectators[$player->getLowerCaseName()] = $player;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function isSpectator(GamePlayer $player): bool {
		return isset($this->spectators[$player->getLowerCaseName()]);
	}

	/**
	 * @param GamePlayer $player
	 */
	public function removeSpectator(GamePlayer $player): void {
		if($this->isSpectator($player)) {
			unset($this->spectators[$player->getLowerCaseName()]);
		}
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function createSpectator(GamePlayer $player): bool {
		$this->addSpectator($player);
		$this->setSpectating($player);
		$player->sendMessage(TextFormat::GREEN . "You have been added to the game as a spectator!");
		return true;
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getAll(): array {
		return array_filter($this->getManager()->getOnlinePlayers() + $this->getSpectators() + [$this->getHost()->getName() => $this->getHost()], function (GamePlayer $player): bool {
			return $player->isOnline();
		});
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getAvailableModerators(): array {
		return array_filter($this->getAll(), function (GamePlayer $player): bool {
			return $player->hasPermission("valiant.permission.mod") && (!$this->getManager()->isPlayer($player) || ($this->getManager()->isPlayer($player) && $this->getManager()->isDead($player))) && $player->getModeratorSettings()->hasModMessagesEnabled();
		});
	}

	/**
	 * @param GamePlayer $player
	 */
	public function setSpectating(GamePlayer $player): void {
		if(!$player->isOnline()) {
			return;
		}
		$player->reset();
		$player->setGamemode(GamePlayer::SPECTATOR);
		$player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), INT32_MAX, 0, false));
		if($this->hasStarted()) {
			$location = $this->getLevel()->getSafeSpawn();
			$player->teleport(Position::fromObject($location->add(0, 25), $location->getLevel()));
		}
	}

	/**
	 * @return GamePlayer
	 */
	public function getHost(): GamePlayer {
		return $this->host;
	}

	/**
	 * @param GamePlayer $host
	 */
	public function setHost(GamePlayer $host): void {
		$this->host = $host;
		$host->setGame($this);
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function isHost(GamePlayer $player): bool {
		return $this->getHost()->getName() === $player->getName();
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function isAlive(GamePlayer $player): bool {
		return $this->getManager()->isPlayer($player) && !$this->getManager()->isDead($player);
	}

	/**
	 * @return int
	 */
	public function getState(): int {
		return $this->state;
	}

	/**
	 * @param int $state
	 */
	public function setState(int $state): void {
		(new GameStateChangeEvent($this, $this->state, $state))->call();
		$this->state = $state;
	}

	/**
	 * @return bool
	 */
	public function hasStarted(): bool {
		return $this->getState() > GameStatus::WAITING;
	}

	/**
	 * @return int
	 */
	public function getTime(): int {
		return $this->time;
	}

	public function isGrace(): bool {
		return $this->getTime() <= GameValues::$GRACE_LENGTH;
	}

	/**
	 * @return string
	 */
	public function getFormattedTime(): string {
		return gmdate(($this->getTime() >= 3600 ? "H:" : "") . "i:s", $this->getTime());
	}

	/**
	 * @param GamePlayer $player
	 */
	public function updateInstance(GamePlayer $player): void {
		$this->getManager()->updateInstance($player);
		if($this->isHost($player)) {
			$this->setHost($player);
		}
	}

	/**
	 * Main logic handled here
	 */
	public function update(): void {
		switch($this->getState()) {
			case GameStatus::WAITING:
				$this->broadcastTip(TextFormat::YELLOW . "Waiting for players...");
				break;
			case GameStatus::SETUP:
				if(!$this->started) {
					$this->setup();
				}
				break;
			case GameStatus::COUNTDOWN:
				switch($this->countdown) {
					case GameValues::$COUNTDOWN_LENGTH - 5:
						$this->broadcast(TextFormat::YELLOW . "Teleporting players in " . ($this->countdown - GameValues::$TELEPORT_TIME) . "...");
						break;
					case GameValues::$TELEPORT_TIME:
						$this->broadcast(TextFormat::YELLOW . "Teleporting players...");
						$this->startScatter();
						break;
					case 0:
						$this->start();
						break;
					case in_array($this->countdown, [45, 30, 15, 10]) || $this->countdown <= 5:
						$this->broadcast(TextFormat::YELLOW . "The game will commence in {$this->countdown} " . UHCUtilities::pluralize("second", $this->countdown) . "!");
						break;
				}
				$this->countdown--;
				break;
			case GameStatus::PLAYING:
				$this->time++;
				$this->check();
				break;
			case GameStatus::POSTGAME:
				$this->broadcastTip(TextFormat::YELLOW . "Stopping game in {$this->postgame}...");
				if($this->postgame <= 0) {
					$this->stop();
					return;
				}
				$this->postgame--;
				break;
		}
		$this->getScoreboardManager()->update();
	}

	/**
	 * @param GamePlayer $player
	 */
	public function join(GamePlayer $player): void {
		if(!$this->hasStarted() && !$this->getManager()->isPlayer($player) && !$this->isHost($player) && (!$this->getSettings()->isMobileOnly() || ($this->getSettings()->isMobileOnly() && $player->isMobile()))) {
			$player->reset();
			$player->setJoinTime(time());
			$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSpawnLocation());
			$this->getManager()->addPlayer($player);
			$player->setRegeneration();
			$player->sendMessage(TextFormat::GREEN . "You have been added to the game as a player!");
		} else {
			if($this->getRespawnManager()->inQueue($player->getName())) {
				$this->getRespawnManager()->respawn($player);
			}
			if($this->getManager()->isPlayer($player)) {
				if(!$this->getManager()->isDead($player)) {
					$this->getDisconnectedManager()->join($player);
					$this->getManager()->updateInstance($player);
					foreach($this->getScenarioList()->getScenarios() as $scenario) {
						if($scenario instanceof EffectScenario) {
							$scenario->giveEffects($player);
						}
					}
					if($this->getState() === GameStatus::COUNTDOWN) $player->setImmobile();
					$player->setRegeneration();
				} else {
					if(!$this->createSpectator($player)) {
						return;
					}
				}
			} else {
				if(!$this->isHost($player) && !$this->createSpectator($player)) {
					return;
				}
			}
		}
		$player->setGame($this);
		$player->getScoreboard()->clearLines();
		$this->getScoreboardManager()->send($player);
	}

	/**
	 * @param GamePlayer $player
	 */
	public function leave(GamePlayer $player): void {
		if($this->getManager()->isPlayer($player)) {
			if(!$this->hasStarted()) {
				if(!$this->getPlugin()->getGameManager()->isDefaultGame($this)) {
					$this->getManager()->removePlayer($player);
				}
			} else {
				if(!$this->getManager()->isDead($player)) {
					$this->getDisconnectedManager()->createDisconnected($player);
				}
			}
		} else if($this->isSpectator($player)) {
			$this->removeSpectator($player);
		}
		$player->getScoreboard()->clearLines();
		$player->setRegeneration(true);
		$this->getPlugin()->sendDefaultScoreboard($player);
	}

	public function setupLevel(): void {
		$this->resetSpawn();
		$this->resetTime();
		foreach($this->getLevel()->getEntities() as $entity) {
			if(!$entity instanceof GamePlayer) {
				$entity->kill();
			}
		}
	}

	public function setup(): void {
		$this->started = true;
		$this->getSettings()->setGlobalMute();
		$this->getManager()->removeNullifiedPlayers();
		$this->getManager()->setup();
		if($this->getHost()->isOnline()) {
			$this->setSpectating($this->getHost());
		}
		$this->getScoreboardManager()->clear();
		$this->getInventoriesManager()->clearInventoryContents();
		foreach($this->getSpectators() as $spectator) {
			try {
				$this->setSpectating($spectator);
			} catch (Exception $exception) {}
		}
		$this->broadcast(TextFormat::RED . "WARNING: Do not log out until after you have been teleported!");
		$this->broadcast(TextFormat::GREEN . "Global mute has been enabled!");
	}

	public function setupHost(): void {

	}

	public function begin(): void {
		$this->setState(GameStatus::SETUP);
		$this->broadcast(TextFormat::GREEN . "The game has been started by the host!");
	}

	public function start(): void {
		$this->setState(GameStatus::PLAYING);
		foreach($this->getManager()->getOnlinePlayers() as $player) {
			$player->reset();
			$player->setScoreTag($player->getHealthString());
			$player->getInventory()->setContents($this->getStartingItems());
		}
		(new GameStartEvent($this))->call();
		$this->setupHost();
		$this->broadcast(TextFormat::GREEN . "The game has begun! Good luck!");
		$this->broadcast(TextFormat::YELLOW . "Final heal will occur in 10 minutes!");
	}

	public function stop(): void {
		(new GameStopEvent($this))->call();
		$this->broadcast(TextFormat::YELLOW . "The game has been stopped!");
		$this->setState(GameStatus::WAITING);
		$this->countdown = GameValues::$COUNTDOWN_LENGTH;
		$this->time = 0;
		$this->postgame = 15;
		$this->started = false;
		$this->getManager()->removeNullifiedPlayers();
		foreach($this->getSpectators() as $key => $spectator) {
			if($spectator === null || ($spectator instanceof GamePlayer && !$spectator->isOnline())) {
				unset($this->spectators[$key]);
				continue;
			}
			if($this->getManager()->isPlayer($spectator) && $this->getManager()->isDead($spectator)) {
				$this->removeSpectator($spectator);
			}
		}
		$this->getManager()->clearDead();
		$this->getInventoriesManager()->clearInventoryContents();
		$this->getRespawnManager()->resetQueue();
		foreach($this->getAll() as $player) {
			$player->reset();
			$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSpawnLocation());
			$player->getScoreboard()->clearLines();
			$player->setScoreTag("");
			if($player->getBossBar()->isSent()) {
				$player->getBossBar()->remove();
			}
		}
	}

	/**
	 * Start the player scatter
	 */
	public function startScatter(): void {
		new RandomTeleportTask($this);
	}


	public function check(): void {
		switch($this->getTime()) {
			case GameValues::$GLOBAL_MUTE_LENGTH:
				$this->getSettings()->setGlobalMute(false);
				$this->broadcast(TextFormat::GREEN . "Global mute has been disabled!");
				break;
			case GameValues::$FINAL_HEAL:
				$this->healAll();
				$this->broadcast(TextFormat::GREEN . "Final heal has been executed!");
				break;
			case GameValues::$GRACE_LENGTH:
				$this->broadcast(TextFormat::GREEN . "Grace has ended! PvP is now enabled!");
				break;
		}
		$this->getDisconnectedManager()->check();
		$this->getBorder()->check();
		$this->checkBossBar();
	}

	/**
	 * Resets spawn to (0, 0)
	 */
	public function resetSpawn(): void {
		$this->getLevel()->setSpawnLocation(new Vector3(0, $this->getLevel()->getHighestBlockAt(0, 0), 0));
	}

	/**
	 * @param Living $living
	 */
	public function summonLightning(Living $living): void {
		$pk = new AddActorPacket;
		$pk->type = "minecraft:lightning_bolt";
		$pk->entityRuntimeId = Entity::$entityCount++;
		$pk->metadata = [];
		$pk->position = $living->asVector3();
		$pk->yaw = $living->getYaw();
		$pk->pitch = $living->getPitch();

		$soundPk = new PlaySoundPacket;
		$soundPk->x = $living->getX();
		$soundPk->y = $living->getY();
		$soundPk->z = $living->getZ();
		$soundPk->soundName = "ambient.weather.thunder";
		$soundPk->volume = 1;
		$soundPk->pitch = 1;

		$this->getPlugin()->getServer()->broadcastPacket($living->getLevel()->getPlayers(), $pk);
	}

	/**
	 * @param bool $stopTime
	 */
	public function resetTime(bool $stopTime = true): void {
		$this->getLevel()->setTime(Level::TIME_NOON);
		if ($stopTime) {
			$this->getLevel()->stopTime();
		} else {
			$this->getLevel()->startTime();
		}
	}

	public function healAll(): void {
		foreach ($this->getManager()->getOnlinePlayers() as $player) {
			$player->setFood($player->getMaxFood());
			$player->setSaturation(Attribute::getAttribute(Attribute::SATURATION)->getMaxValue());
			$player->heal(new EntityRegainHealthEvent($player, abs($player->getMaxHealth() - $player->getHealth()), EntityRegainHealthEvent::CAUSE_CUSTOM));
		}
	}

	/**
	 * @param $message
	 */
	public function broadcast($message): void {
		foreach($this->getAll() as $player) {
			$player->sendMessage($message);
		}
		$this->getLogger()->info($message);
	}

	/**
	 * @param string $message
	 */
	public function broadcastTip(string $message): void {
		foreach($this->getAll() as $player) {
			$player->sendTip($message);
		}
	}

	/**
	 * @param string $title
	 * @param string $subtitle
	 * @param int $fadeIn
	 * @param int $stay
	 * @param int $fadeOut
	 */
	public function broadcastTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) {
		foreach($this->getAll() as $player) {
			$player->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
		}
	}

	/**
	 * @param string $message
	 * @param float $progress
	 */
	public function broadcastBossBar(string $message, float $progress = 0.0): void {
		foreach($this->getAll() as $player) {
			$player->getBossBar()->setTitle($message);
			$player->getBossBar()->setProgress($progress);
			if(!$player->getBossBar()->isSent()) {
				$player->getBossBar()->send();
			}
		}
	}

	public function checkBossBar(): void {
		switch($this->getTime()) {
			case $this->getTime() < GameValues::$FINAL_HEAL:
				$length = GameValues::$FINAL_HEAL - $this->time;
				$this->broadcastBossBar(TextFormat::WHITE . "Final heal will occur in: " . TextFormat::YELLOW . gmdate("i:s", $length), BossBar::map($length, 0, GameValues::$FINAL_HEAL));
				break;
			case $this->getTime() < GameValues::$GRACE_LENGTH:
				$max = (GameValues::$GRACE_LENGTH - GameValues::$FINAL_HEAL);
				$length = GameValues::$GRACE_LENGTH - $this->time;
				$this->broadcastBossBar(TextFormat::WHITE . "Grace will end in: " . TextFormat::YELLOW . gmdate("i:s", $length), BossBar::map($length, 0, $max));
				break;
			default:
				if($this->getBorder()->canShrink()) {
					$borderTime = $this->getBorder()->getNextBorderTime();
					$borderSize = $this->getBorder()->getNextBorderSize();
					$length = $borderTime - $this->time;
					$lastBorder = $this->getBorder()->getBorderTime($this->getBorder()->getBorderIndex() - 1);
					if($lastBorder === -1) {
						$lastBorder = GameValues::$GRACE_LENGTH;
					}
					$max = ($borderTime - $lastBorder);
					$this->broadcastBossBar(TextFormat::WHITE . "Border shrink to " . TextFormat::YELLOW . $borderSize . TextFormat::WHITE . " in: " . TextFormat::YELLOW . gmdate("i:s", $length), BossBar::map($length, 0, $max));
				} else if(!$this->hasFinishedShrinking) {
					$this->hasFinishedShrinking = true; //TODO: Remove this flag & find another way to manage this
					$this->removeBossBars();
				}
		}
	}

	public function removeBossBars(): void {
		foreach($this->getAll() as $player) {
			if($player->getBossBar()->isSent()) {
				$player->getBossBar()->remove();
			}
		}
	}

	public function __destruct() {
		foreach($this as $key => $value) unset($this->$key);
	}
}