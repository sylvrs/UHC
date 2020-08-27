<?php


namespace sys\jordan\uhc\form;


use pocketmine\Player;
use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class ScenarioListForm extends SimpleForm {

	/**
	 * ScenarioForm constructor.
	 * @param Game $game
	*/
	public function __construct(Game $game) {
		parent::__construct(TextFormat::YELLOW . "Scenario Settings", "");
		foreach($game->getPlugin()->getScenarioManager()->getScenarios() as $scenario) {
			$name = ($scenario->getName() . " " . ($game->getScenarioList()->isScenario($scenario) ? TextFormat::GREEN . "[Enabled]" : TextFormat::RED . "[Disabled]"));
			$this->addElement(new Button($name, function(Player $player) use($game, $name, $scenario): void {
				(new ModalForm($name, $scenario->getDescription(), "Enable", "Disable", function(Player $player, $data) use($game, $scenario) {
					if($data) {
						$game->getScenarioList()->addScenario($scenario);
					} else {
						$game->getScenarioList()->removeScenario($scenario);
					}
					if(!$game->hasStarted()) {
						$game->getScoreboardManager()->clear();
					}
					$player->sendMessage(TextFormat::GREEN . "{$scenario->getName()} has been " . ($data ? "enabled" : "disabled") . "!");
					(new ScenarioListForm($game))->send($player);
				}))->send($player);
			}));
		}
		$this->setCallable(function (GamePlayer $player, $data) use($game): void {
			if($data === null) {
				(new GameSettingsForm($game))->send($player);
			}
		});
	}

}