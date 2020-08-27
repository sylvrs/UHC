<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\form\GameSettingsForm;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class GameCommand extends BaseUserCommand {

	/**
	 * GameCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "game", "Access game settings w/ this command", "/game", [], "valiant.permission.game");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			if($sender->getGame()->isHost($sender)) {
				(new GameSettingsForm($sender->getGame()))->send($sender);
				return true;
			}
			return TextFormat::RED . "You must be the host to use this command!";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {
		// TODO: Implement setOverloads() method.
	}
}