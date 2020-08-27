<?php


namespace sys\jordan\uhc;


use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use sys\jordan\core\discord\DiscordChannel;
use sys\jordan\uhc\command\CreateCommand;
use sys\jordan\uhc\command\GameCommand;
use sys\jordan\uhc\command\GlobalMuteCommand;
use sys\jordan\uhc\command\HealthCommand;
use sys\jordan\uhc\command\HelpOpCommand;
use sys\jordan\uhc\command\KillTopCommand;
use sys\jordan\uhc\command\RespawnCommand;
use sys\jordan\uhc\command\ConfigCommand;
use sys\jordan\uhc\command\ScenariosCommand;
use sys\jordan\uhc\command\SpectateCommand;
use sys\jordan\uhc\command\StatsCommand;
use sys\jordan\uhc\command\TeamCommand;
use sys\jordan\uhc\game\GameManager;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use sys\jordan\uhc\player\PlayerStatListener;
use sys\jordan\uhc\scenario\ScenarioManager;
use sys\jordan\uhc\utils\GoldenHead;

class GameBase extends PluginBase {

	public const NAME = "Valiant";

	public const SCOREBOARD_TITLE = TextFormat::RED . self::NAME . TextFormat::WHITE . " - " . self::GAMEMODE;

	/** @var string */
	public const GAMEMODE = TextFormat::WHITE . "UHC";

	/** @var self */
	private static $instance = null;

	/** @var GameManager */
	private $gameManager;

	/** @var ScenarioManager */
	private $scenarioManager;

	public function onLoad(): void {
		self::$instance = $this;
		$this->saveDefaultConfig();
	}

	public function onEnable(): void {
		$this->gameManager = new GameManager($this);
		$this->scenarioManager = new ScenarioManager($this);
		$this->registerCommands();
		$this->registerListeners();
		$this->registerEntity();
		$this->registerItems();
		$this->getLogger()->info(TextFormat::GREEN . "{$this->getDescription()->getFullName()} has been enabled!");
	}

	public function onDisable(): void {
		$this->getLogger()->info(TextFormat::RED . "{$this->getDescription()->getFullName()} has been disabled!");
	}

	public function registerCommands(): void {
		$this->getServer()->getCommandMap()->registerAll("uhc", [
			new CreateCommand($this),
			new GameCommand($this),
			new GlobalMuteCommand($this),
			new HealthCommand($this),
			new HelpOpCommand($this),
			new KillTopCommand($this),
			new RespawnCommand($this),
			new ConfigCommand($this),
			new ScenariosCommand($this),
			new StatsCommand($this),
			new TeamCommand($this),
			new SpectateCommand($this)
		]);
	}

	public function registerListeners(): void {
		new ModListener($this);
		new PlayerStatListener($this);
		new UHCBaseListener($this);
	}

	public function registerEntity(): void {
		Entity::registerEntity(DisconnectedPlayerMob::class, true, ["disconnectedPlayer"]);
	}

	public function registerItems(): void {
		ItemFactory::registerItem(new GoldenHead, true);
		new CraftingAddons($this);
	}


	/**
	 * @return GameBase
	 */
	public static function getInstance(): GameBase {
		return self::$instance;
	}

	/**
	 * @return DiscordChannel[]
	 */
	public function getChannels(): array {
		return [
			new DiscordChannel("https://discordapp.com/api/webhooks/731567611371847730/dHFQaHfH0fBUTB2AMXQZPC1VXK1UCYpP7XWfgs3swNtAVk2bwnu2eHRYH6lOLuq6WpU2")
		];
	}

	/**
	 * @return GameManager
	 */
	public function getGameManager(): GameManager {
		return $this->gameManager;
	}

	/**
	 * @return ScenarioManager
	 */
	public function getScenarioManager(): ScenarioManager {
		return $this->scenarioManager;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function sendDefaultScoreboard(GamePlayer $player): void {
		$player->getScoreboard()->setLineArray([
			1 => TextFormat::WHITE. str_repeat("-", 16),
			2 => TextFormat::WHITE . "Name: " . TextFormat::YELLOW . $player->getName(),
			3 => TextFormat::WHITE . "Rank: " . TextFormat::YELLOW . $player->getRank()->getName(),
			4 => TextFormat::WHITE. str_repeat("-", 16)
		]);
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getLobbyPlayers(): array {
		return array_filter($this->getServer()->getLoggedInPlayers(), function (GamePlayer $player): bool {
			return !$player->inGame();
		});
	}

}