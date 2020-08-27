<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class RespawnCommand extends BaseUserCommand {

	/**
	 * RespawnCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "respawn", "Respawn players into the UHC", "/respawn <player>", [], "valiant.permission.respawn");
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
			$player = $sender->getServer()->getPlayer($args[0]);
			if($player instanceof GamePlayer) {
				if($player->inGame()) {
					if($player->getGame()->isHost($player)) {
						return TextFormat::RED . "The host can not respawn themselves!";
					} else if($player->getGame()->getManager()->isPlayer($player) && !$player->getGame()->getManager()->isDead($player)) {
						return TextFormat::RED . "You can not respawn a player that is alive!";
					}
				}
				$sender->getGame()->getRespawnManager()->respawn($player);
				$this->broadcastCommandMsg($sender, TextFormat::WHITE . "Respawned " . TextFormat::YELLOW . $player->getName());
				return TextFormat::GREEN . "Successfully respawned " . $player->getName() . "!";
			} else {
				if(!$sender->getGame()->getRespawnManager()->inQueue($args[0])) {
					$sender->getGame()->getRespawnManager()->addToQueue($args[0]);
					return TextFormat::GREEN . "Player '$args[0]' added to respawn queue!";
				}
				return TextFormat::RED . "Player '$args[0]' is already in the respawn queue!";
			}
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {
		$this->pushOverload("player", 0, false, AvailableCommandsPacket::ARG_TYPE_TARGET);
	}
}