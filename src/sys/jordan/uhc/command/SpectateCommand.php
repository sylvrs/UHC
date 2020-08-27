<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;

class SpectateCommand extends BaseUserCommand {

	/**
	 * SpectateCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "spectate", "Spectate players in the UHC!", "/spectate", []);
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			if($sender->getGame()->isSpectator($sender) || $sender->getGame()->isHost($sender)) {
				if(isset($args[0])) {
					$player = $sender->getServer()->getPlayer($args[0]);
					if($player instanceof GamePlayer && $sender->getGame()->getManager()->isPlayer($player)) {
						if(!$sender->getGame()->getManager()->isDead($player)) {
							return $this->teleport($sender, $player);
						}
						return TextFormat::RED . "The player provided is not alive!";
					}
					return TextFormat::RED . "Player not found!";
				}
				$form = new SimpleForm("Spectate", "");
				$players = array_filter($sender->getGame()->getManager()->getOnlinePlayers(), function (GamePlayer $player) use($sender): bool {
					return !$sender->getGame()->getManager()->isDead($player);
				});
				foreach($players as $player) {
					$form->addElement(new Button($player->getName(), function(GamePlayer $sender) use($player): void {
						$sender->sendMessage($this->teleport($sender, $player));
					}));
				}
				$sender->sendForm($form);
				return true;
			}
			return TextFormat::RED . "You must be a spectator to use this command!";
		}
		return TextFormat::RED . "You must be in a UHC to use this command!";
	}

	/**
	 * @param GamePlayer $sender
	 * @param GamePlayer $to
	 * @return string
	 */
	public function teleport(GamePlayer $sender, GamePlayer $to): string {
		$sender->teleport($to);
		return TextFormat::GREEN . "Now spectating: " . TextFormat::YELLOW . $to->getName();
	}

	/**
	 * @return void
	 */
	public function setOverloads(): void {
		$this->pushOverload("player", 0, false, AvailableCommandsPacket::ARG_TYPE_TARGET);

	}
}