<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GamePlayer;

class ConfigCommand extends BaseUserCommand {

	/**
	 * ConfigCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "config", "Shows config of the player's current UHC", "/config", []);
	}


	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			$messages = [
				TextFormat::WHITE . "----- " . TextFormat::YELLOW . "Configuration" . TextFormat::WHITE . " -----",
				TextFormat::WHITE . "Gamemode: " . TextFormat::YELLOW . ($sender->getGame()->isTeams() ? "TO" . $sender->getGame()->getManager()->getMaxPlayerCount() : "FFA"),
				TextFormat::WHITE . "Mobile Only: " . TextFormat::YELLOW . ($sender->getGame()->getSettings()->isMobileOnly() ? "true" : "false"),
				TextFormat::WHITE . "Apple Rate: " . TextFormat::YELLOW . $sender->getGame()->getSettings()->getAppleRate() . "%",
				TextFormat::WHITE . "Host: " . TextFormat::YELLOW . $sender->getGame()->getHost()->getName(),
				TextFormat::WHITE . "-----------------------"
			];
			foreach ($messages as $message) {
				$sender->sendMessage($message);
			}
			return true;
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {

	}
}