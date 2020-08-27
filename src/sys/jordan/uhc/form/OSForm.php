<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\CustomForm;
use sys\jordan\core\form\elements\Toggle;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class OSForm extends CustomForm {

	/**
	 * OSForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "OS Settings", function (GamePlayer $player) use($game): void {
			(new GameSettingsForm($game))->send($player);
		});
		$toggle = new Toggle(TextFormat::WHITE . "Mobile-Only", $game->getSettings()->isMobileOnly());
		$toggle->setCallable(function (GamePlayer $player, $data) use($game): void {
			$game->getSettings()->setMobileOnly($data);
			$game->getManager()->checkPlayers();
			$player->sendMessage(TextFormat::YELLOW . "Mobile-only set to: " . strtoupper(var_export($data, true)));
		});
		$this->addElement($toggle);
	}

}