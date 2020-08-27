<?php


namespace sys\jordan\uhc\form;


use pocketmine\Player;
use pocketmine\utils\TextFormat;
use sys\jordan\core\form\CustomForm;
use sys\jordan\core\form\elements\Slider;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class TeamSettingsForm extends CustomForm {

	/**
	 * TeamSettingsForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "Team Settings", function (GamePlayer $player) use($game): void {
			(new GameSettingsForm($game))->send($player);
		});
		$teamSize = new Slider("Max Team Size", 2, 10, 1);
		$teamSize->setDefaultValue($game->getManager()->getMaxPlayerCount());
		$teamSize->setCallable(function (Player $player, $data) use($game): void {
			$game->getManager()->setMaxPlayerCount((int)$data);
			$player->sendMessage(TextFormat::GREEN . "The max team size has been set to {$data}!");
		});
		$this->addElement($teamSize);
	}

}