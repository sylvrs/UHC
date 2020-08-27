<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\CustomForm;
use sys\jordan\core\form\elements\Slider;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class WorldSettingsForm extends CustomForm {

	/**
	 * WorldSettingsForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct("World Settings", function (GamePlayer $player) use($game): void {
			(new GameSettingsForm($game))->send($player);
		});
		$appleRate = new Slider("Apple Rate Percent", 1, 100, 1);
		$appleRate->setDefaultValue($game->getSettings()->getAppleRate());
		$appleRate->setCallable(function (GamePlayer $player, $data) use($game): void {
			$game->getSettings()->setAppleRate($data);
			$player->sendMessage(TextFormat::GREEN . "The apple rate is now $data%!");
		});
		$this->addElement($appleRate);
	}

}