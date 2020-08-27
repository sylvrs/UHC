<?php


namespace sys\jordan\uhc\scenario\module\mana;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;

class ManaCommand extends BaseUserCommand {

	/**
	 * ManaCommand constructor.
	 */
	public function __construct() {
		parent::__construct(GameBase::getInstance(), "mana", "Access the mana shop with this command", "/mana", []);
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if(!$sender->inGame()) {
			return TextFormat::RED . "You must be in a game to use this command!";
		}
		if(!$sender->getGame()->isAlive($sender)) {
			return TextFormat::RED . "You must be alive to use this command!";
		}
		if(!($sender->getGame()->getState() > GameStatus::COUNTDOWN)) {
			return TextFormat::RED . "You can't use this command before the game starts!";
		}
		$sender->sendForm(new ManaShopMenu($sender));
		return TextFormat::GREEN . "Opening mana shop...";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {

	}
}