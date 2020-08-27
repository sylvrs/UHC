<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class GlobalMuteCommand extends BaseUserCommand {

	/**
	 * ScenarioCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "globalmute", "Enables or disables global mute", "/globalmute [true|false]", [], "valiant.permission.globalmute");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			if(count($args) < 1) {
				throw new InvalidCommandSyntaxException;
			}
			$game = $sender->getGame();
			switch(strtolower($args[0])) {
				case "enable":
				case "true":
				case "on":
				default:
					if(!$game->getSettings()->isGlobalMuteEnabled()) {
						$game->getSettings()->setGlobalMute(true);
						$game->broadcast(TextFormat::YELLOW . "Global mute has been enabled by an admin!");
						return TextFormat::GREEN . "Global mute has been enabled!";
					}
					return TextFormat::RED . "Global mute is already enabled!";
				case "disable":
				case "false":
				case "off":
					if($game->getSettings()->isGlobalMuteEnabled()) {
						$game->getSettings()->setGlobalMute(false);
						$game->broadcast(TextFormat::YELLOW . "Global mute has been disabled by an admin!");
						return TextFormat::GREEN . "Global mute has been disabled!";
					}
					return TextFormat::RED . "Global mute is already disabled!";
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