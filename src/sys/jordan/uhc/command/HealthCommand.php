<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseCommand;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class HealthCommand extends BaseCommand {

	/**
	 * HealCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "health", "Check players' health w/ this command", "/health <player>", ["h"]);
	}

	/**
	 * @inheritDoc
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if(count($args) < 1) {
			throw new InvalidCommandSyntaxException;
		}
		$player = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if($player instanceof GamePlayer) {
			return TextFormat::YELLOW . $player->getDisplayName() . TextFormat::WHITE . ": " . $player->getHealthString();
		}
		return TextFormat::RED . "Unable to locate player '$args[0]'!";
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {
		$this->pushOverload("player", 0, false, AvailableCommandsPacket::ARG_TYPE_TARGET);
	}
}