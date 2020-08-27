<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\ModalForm;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class GameDeletionForm extends ModalForm {

	/**
	 * GameDeletionForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct("Game Deletion Confirmation", "Are you sure you want to delete this game?", "Confirm", "Cancel", function (GamePlayer $player, $data) use($game): void {
			if($data >= 1) {
				$game->stop();
				$game->getHeartbeat()->cancel();
				foreach($game->getAll() as $player) {
					$player->removeFromGame();
					$game->getManager()->removePlayer($player);
					if($player->inTeam()) {
						$player->setTeam(null);
					}
					$game->getPlugin()->sendDefaultScoreboard($player);
				}
				$game->getPlugin()->getGameManager()->removeGame($game);
				$player->sendMessage(TextFormat::GREEN . "You have deleted the game!");
			} else {
				(new GameSettingsForm($game))->send($player);
			}
		});
	}

}