<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class ScenariosCommand extends BaseUserCommand {

	/**
	 * ScenarioCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "scenarios", "Shows all active scenarios in a UHC", "/scenarios", ["sc"]);
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			$uhc = $sender->getGame();
			if(count($uhc->getScenarioList()->getScenarios()) <= 0) {
				return TextFormat::RED . "There are no active scenarios to date in this game!";
			} else {
				$sender->sendMessage(TextFormat::WHITE . "-----" . TextFormat::YELLOW . " Scenarios " . TextFormat::WHITE . "-----");
				foreach($uhc->getScenarioList()->getNameSortedArray() as $scenario) {
					$sender->sendMessage(TextFormat::YELLOW . $scenario->getName() . TextFormat::WHITE . " - " . $scenario->getDescription());
				}
				$sender->sendMessage(TextFormat::WHITE . "--------------------------");
			}
			return true;

		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {

	}
}