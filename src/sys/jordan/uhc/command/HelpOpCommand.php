<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;

class HelpOpCommand extends BaseUserCommand {

	public const PREFIX = TextFormat::RED . "[HELP-OP]";

	/**
	 * KillTopCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "helpop", "Sends a message to the host", "/helpop [message]");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			if($sender->getGame()->getManager()->isPlayer($sender)) {
				if(count($args) < 1) {
					throw new InvalidCommandSyntaxException;
				}
				$message = implode(" ", $args);
				$sender->getGame()->getHost()->sendMessage(self::PREFIX . TextFormat::YELLOW . " {$sender->getName()} > {$message}");
				return TextFormat::GREEN . "Message successfully sent to host!";
			}
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {
		$this->pushOverload("message", 0, false, AvailableCommandsPacket::ARG_TYPE_RAWTEXT);
	}
}