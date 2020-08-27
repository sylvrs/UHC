<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class KillTopCommand extends BaseUserCommand {

	/**
	 * KillTopCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "kt", "Show top kills for the UHC", "/kt");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			$top = $sender->getGame()->getEliminationsManager()->getTopEliminations();
			if(count($top) > 0) {
				$sender->sendMessage(TextFormat::WHITE . "-----" . TextFormat::RED . " Top Kills " . TextFormat::WHITE . "-----");
				foreach($top as $name => $killCount) {
					$sender->sendMessage(TextFormat::YELLOW . $name . TextFormat::WHITE . " - " . $killCount);
				}
				$sender->sendMessage(TextFormat::WHITE . "---------------------");
				return true;
			} else {
				return TextFormat::YELLOW . "No one has killed yet! Change that?";
			}
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {

	}
}