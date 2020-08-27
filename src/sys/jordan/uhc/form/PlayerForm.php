<?php


namespace sys\jordan\uhc\form;


use pocketmine\Player;
use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class PlayerForm extends SimpleForm {

	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "Player Settings", "");
		$players = array_filter($game->getManager()->getPlayers(), function (GamePlayer $player) use($game): bool {
			return !$game->getManager()->isDead($player);
		});
		$form = $this;
		asort($players);
		foreach($players as $player) {
			$this->addElement(new Button($player->getName(), function (GamePlayer $host) use($form, $game, $player): void {
				$host->sendForm(new class($form, $game, $player) extends SimpleForm {
					public function __construct(PlayerForm $form, Game $game, GamePlayer $player) {
						parent::__construct($player->getName(), "", function (Player $player, $data) use($form, $game): void {
							if($data === null) {
								$form->send($player);
							}
						});
						$this->addElement(new Button("Remove", function (Player $sender) use($game, $player): void {
							$sender->sendForm(new ModalForm("Confirmation", "Are you sure you want to remove {$player->getName()}?", "Yes", "No", function(Player $sender, $data) use ($game, $player): void {
								if($data) {
									$sender->sendMessage(TextFormat::GREEN . "You have successfully removed {$player->getName()}!");
									$game->getManager()->removePlayer($player);
									if($player->isOnline()) {
										$player->reset();
										$player->removeFromGame();
										$player->teleport($game->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
										$player->sendMessage(TextFormat::RED . "You have been removed from the game!");
									}
								}
							}));
						}));
						if($player->isOnline()) {
							$this->addElement(new Button("Spectate", function (Player $sender, &$data) use($player): void {
								$sender->teleport($player);
								$sender->sendMessage(TextFormat::GREEN . "You are now spectating {$player->getName()}!");
								$data = false;
							}));
						}

					}

				});
			}));
		}
	}

}