<?php


namespace sys\jordan\uhc\game;


use DateTime;
use DateTimeZone;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use sys\jordan\core\discord\DiscordChannel;
use sys\jordan\core\discord\DiscordEmbed;
use sys\jordan\core\discord\DiscordMessage;
use sys\jordan\core\discord\embed\Field;
use sys\jordan\uhc\game\member\TeamManager;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\scenario\Scenario;
use sys\jordan\uhc\utils\UHCUtilities;

class GameFeed {

	use GameTrait;

	/** @var int */
	public const TYPE_NA = 0;
	/** @var int */
	public const TYPE_EU = 1;

	/** @var string */
	public const MOBILE_ROLE = "707070655568543756";
	/** @var string */
	public const CONTROLLER_ROLE = "708067980285706411";
	/** @var string */
	public const DESKTOP_ROLE = "707347599778119763";

	/**
	 * GameFeed constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param int $type
	 * @return DiscordMessage
	 */
	public function craftMessage(int $type = self::TYPE_NA): DiscordMessage {
		$message = new DiscordMessage;
		$mentions = DiscordMessage::createMention(self::MOBILE_ROLE) . " ";
		$mentions .= DiscordMessage::createMention(self::CONTROLLER_ROLE) . " ";
		if(!$this->getGame()->getSettings()->isMobileOnly()) {
			$mentions .= DiscordMessage::createMention(self::DESKTOP_ROLE);
		}
		$message->addContent($mentions);
		$embed = new DiscordEmbed("Game Update", DiscordEmbed::TYPE_RICH);
		$embed->setColor(hexdec("80a6e8"));
		if($this->getGame()->isTeams()) {
			/** @var TeamManager $manager */
			$manager = $this->getGame()->getManager();
			$gameType = "Team of " . $manager->getMaxPlayerCount();
		} else {
			$gameType = "Free-for-all";
		}
		$embed->addField(new Field("Game Type", $gameType));
		try {
			$dateTime = UHCUtilities::roundTime(new DateTime());
			$dateTime->setTimeZone(new DateTimeZone($type === self::TYPE_NA ? "America/New_York" : "UTC"));

			$embed->addField(new Field("Opens", $dateTime->format("h:i A T")));
			$embed->addField(new Field("Starts", UHCUtilities::addTime($dateTime, 10)->format("h:i A T")));
		} catch (\Exception $exception) {}

		$scenarios = array_map(function (Scenario $scenario): string {
			return "• {$scenario->getName()} - {$scenario->getDescription()}";
		}, $this->getGame()->getScenarioList()->getNameSortedArray());
		$embed->addField(new Field("Scenarios", count($scenarios) > 0 ? implode("\n", $scenarios) : "None"));
		$embed->addField(new Field("Host", $this->getGame()->getHost()->getName()));
		$embed->addField(new Field("Nether", UHCUtilities::boolConvert($this->getGame()->getSettings()->isNetherEnabled())));
		$embed->addField(new Field("IP", "valiantnetwork.xyz")); //TODO: change when done

		$message->addEmbed($embed);
		return $message;
	}

	/**
	 * Note: We're not using the async parameter, so that hosts don't get sent multiple messages for one posting to multiple channels
	 *
	 * @param callable|null $callback
	 */
	public function post(?callable $callback = null): void {
		$channels = GameBase::getInstance()->getChannels();
		$data = array_map(function (int $type, DiscordChannel $channel): array {
			return [
				"url" => $channel->getWebhookUrl(),
				"data" => json_encode($this->craftMessage($type),JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
			];
		}, array_keys($channels), $channels);
		$this->getGame()->getPlugin()->getServer()->getAsyncPool()->submitTask(new class($data, $callback) extends AsyncTask {
			/** @var array */
			private $channels;
			/** @var callable|null */
			private $callback;
			/**
			 * @param array $channels
			 * @param callable|null $callback
			 */
			public function __construct(array $channels, ?callable $callback = null) {
				$this->channels = $channels;
				$this->callback = $callback;
			}
			public function onRun() {
				foreach($this->channels as $channel) {
					Internet::postURL($channel["url"], $channel["data"], 10, ["Content-Type: application/json"]);
				}
			}
			/**
			 * @param Server $server
			 */
			public function onCompletion(Server $server) {
				if($this->callback !== null) {
					($this->callback)($server);
				}
			}
		});
	}

}