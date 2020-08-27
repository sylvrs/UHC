<?php


namespace sys\jordan\uhc\form;


use pocketmine\Server;
use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class GameSettingsForm extends SimpleForm {

	/**
	 * GameSettingsForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "Game Settings", "");
		$this->addElement(new Button(!$game->hasStarted() ? "Start" : "Stop", function(GamePlayer $player) use($game): void {
			(new ModalForm("Confirmation", "This will " . (!$game->hasStarted() ? "start" : "stop") . " the game. Are you sure you want to continue?", "Confirm", "Deny", function (GamePlayer $player, $data) use($game): void {
				if($data) {
					if($game->hasStarted()) {
						$game->stop();
						$player->sendMessage(TextFormat::GREEN . "Game stopped successfully!");
					} else {
						$game->begin();
						$player->sendMessage(TextFormat::GREEN . "Game started successfully!");
					}
				}
			}))->send($player);
		}));
		$this->addElement(new Button("Border Settings", function (GamePlayer $player) use($game): void {
			(new BorderForm($game))->send($player);
		}));
		$this->addElement(new Button("OS Settings", function(GamePlayer $player) use($game): void {
			(new OSForm($game))->send($player);
		}));
		$this->addElement(new Button("Player Settings", function(GamePlayer $player) use($game): void {
			(new PlayerForm($game))->send($player);
		}));
		$this->addElement(new Button("Scenario Settings", function(GamePlayer $player) use($game): void {
			(new ScenarioListForm($game))->send($player);
		}));
		if($game->isTeams()) {
			$this->addElement(new Button("Team Settings", function(GamePlayer $player) use($game): void {
				(new TeamSettingsForm($game))->send($player);
			}));
		}
		$this->addElement(new Button("Post", function (GamePlayer $player) use($game): void {
			(new ModalForm("Confirmation", "This will post the game to Discord. Are you sure you want to continue?", "Confirm", "Deny", function (GamePlayer $player, $data) use($game): void {
				if($data) {
					$player->sendMessage(TextFormat::YELLOW . "Posting...");
					/**
					 * Note:
					 * Do not try to pass in the $player object itself
					 * It'll result in a crash, instead use the primitive string value of their name & search the server on callback
					 */
					$name = $player->getName();
					$game->getFeed()->post(function (Server $server) use($name) {
						if(($callbackPlayer = $server->getPlayer($name)) instanceof GamePlayer) {
							$callbackPlayer->sendMessage(TextFormat::GREEN . "Game posted successfully!"); //TODO: check if the game *actually* posted successfully & not just says it did
						}
					});
				}
			}))->send($player);
		}));
		$this->addElement(new Button("World Settings", function(GamePlayer $player) use($game): void {
			(new WorldSettingsForm($game))->send($player);
		}));
		$this->addElement(new Button("Delete", function (GamePlayer $player) use($game): void {
			(new GameDeletionForm($game))->send($player);
		}));
	}

}