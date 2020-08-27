<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\form\GameCreationForm;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class CreateCommand extends BaseUserCommand {

	/**
	 * CreateCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "create", "Create games w/ this command", "/create", [], "valiant.permission.create");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		/** @var GameBase $plugin */
		$plugin = $this->getPlugin();
		if(!$sender->inGame()) {

			if($sender->getLevel() === $plugin->getServer()->getDefaultLevel()) {
				return TextFormat::RED . "You can't create a game in the server's default level!";
			}
			(new GameCreationForm($plugin))->send($sender);
			return true;
		}
		return TextFormat::RED . "You can't use this command while in a game!";
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {

	}
}