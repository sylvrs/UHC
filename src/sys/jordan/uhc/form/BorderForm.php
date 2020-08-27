<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class BorderForm extends SimpleForm {

	/**
	 * BorderForm constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "Border Settings", "");
		$button = new Button("Advance Border", function (GamePlayer $player, $data) use($game): void {
			if(!$game->hasStarted()) {
				(new GameSettingsForm($game))->send($player);
				return;
			}
			if(!$game->getBorder()->canShrink()) {
				$player->sendMessage(TextFormat::RED . "The border can't shrink any more than it has!");
				return;
			}
			$content = [
				"Please ensure this is what you want to do before continuing.",
				"Current border size: " . TextFormat::YELLOW . $game->getBorder()->getSize() . TextFormat::RESET,
				"Next border size: " . TextFormat::YELLOW . $game->getBorder()->getNextBorderSize()
			];
			(new ModalForm("Confirmation", join("\n\n", $content), "Confirm", "Deny", function (GamePlayer $player, $data) use($game): void {
				if($data) {
					$game->getBorder()->shrink();
				}
			}))->send($player);
		});
		$this->addElement($button);
	}

}