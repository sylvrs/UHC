<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\CustomForm;
use sys\jordan\core\form\elements\Dropdown;
use sys\jordan\core\form\elements\Toggle;
use sys\jordan\uhc\border\Border;
use sys\jordan\uhc\border\BorderValues;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\GameBase;
use function intval;

class GameCreationForm extends CustomForm {

	/**
	 * GameCreationForm constructor.
	 * @param GameBase $plugin
	 */
	public function __construct(GameBase $plugin) {
		$size = Border::DEFAULT_SIZE;
		$createBorder = true;
		$teams = false;
		parent::__construct("Create Game", function(GamePlayer $player) use($plugin, &$size, &$createBorder, &$teams): void {
			$success = $plugin->getGameManager()->createGame($player->getLevel(), $player, $size, $createBorder, $teams);
			if($success) {
				$player->sendForm(new GameSettingsForm($player->getGame()));
				/** @var GamePlayer[] $players */
				$players = $plugin->getServer()->getOnlinePlayers();
				$defaultGame = $plugin->getGameManager()->getDefaultGame();
				foreach($players as $onlinePlayer) {
					if($onlinePlayer !== $player && !$onlinePlayer->inGame() && ($player->getGame() === $defaultGame)) {
						$defaultGame->join($onlinePlayer);
					}
				}
				$player->sendMessage(TextFormat::GREEN . "You have successfully created a game in level '{$player->getLevel()->getName()}/{$player->getLevel()->getFolderName()}'!");
			} else {
				$player->sendMessage(TextFormat::RED . "There is already a game in level '{$player->getLevel()->getName()}/{$player->getLevel()->getFolderName()}'!");
			}
		});

		$borderToggle = new Toggle("Create border", true);
		$borderToggle->setCallable(function (GamePlayer $player, $data) use(&$createBorder): void {
			$createBorder = is_bool($data) ? $data : true;
		});
		$this->addElement($borderToggle);

		$borderSize = new Dropdown("Border size");
		foreach(BorderValues::getSizes() as $defaultSize) $borderSize->addOption($defaultSize, $defaultSize === Border::DEFAULT_SIZE);
		$borderSize->setCallable(function (GamePlayer $player, $data) use(&$size): void {
			$size = BorderValues::getSizes()[intval($data)] ?? Border::DEFAULT_SIZE;
		});
		$this->addElement($borderSize);

		$teamToggle = new Toggle("Teams", false);
		$teamToggle->setCallable(function (GamePlayer $player, $data) use(&$teams): void {
			$teams = is_bool($data) ? $data : true;
		});
		$this->addElement($teamToggle);
	}
}