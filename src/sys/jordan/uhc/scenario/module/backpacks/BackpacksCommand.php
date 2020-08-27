<?php


namespace sys\jordan\uhc\scenario\module\backpacks;


use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\defaults\Backpacks;

class BackpacksCommand extends BaseUserCommand {

	/**
	 * BackpacksCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "backpacks", "Access an extra inventory to store your items in!", "/backpacks", ["bp"]);
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame() && $sender->getGame()->isAlive($sender)) {
			if($sender->inTeam()) {
				if($sender->getGame()->getState() > GameStatus::COUNTDOWN) {
					$scenario = $sender->getGame()->getScenarioList()->getScenario("Backpacks");
					if($scenario instanceof Backpacks) {
						$backpack = $scenario->getBackpack($sender->getTeam());
						$backpack->send($sender);
						return TextFormat::GREEN . "Opening backpack!";
					}
					return TextFormat::RED . "Unable to locate scenario in order to open backpack!";
				}
				return TextFormat::RED . "You can't open your backpack before the game starts!";
			}
			return TextFormat::RED . "You must be in a team to use this command!";
		}
		return TextFormat::RED . "Unable to open backpacks inventory! Make sure you are alive and in the game before running this command!";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {

	}
}