<?php


namespace sys\jordan\uhc\form;


use pocketmine\utils\TextFormat;
use sys\jordan\core\form\CustomForm;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\elements\Label;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\GamePlayer;

class StatsForm extends SimpleForm {

	/**
	 * StatsForm constructor.
	 * @param GamePlayer $sender
	 * @param GamePlayer $statsPlayer
	 */
	public function __construct(GamePlayer $sender, GamePlayer $statsPlayer) {
		parent::__construct(TextFormat::WHITE  . ($sender === $statsPlayer ? "Your" : TextFormat::YELLOW . $statsPlayer->getName() . "'s") . TextFormat::WHITE . " Stats", "");
		$categories = [
			"Kills/Deaths" => [
				TextFormat::WHITE . "Kills: " . TextFormat::YELLOW . $statsPlayer->getStats()->getKills(),
				TextFormat::WHITE . "Deaths: " . TextFormat::YELLOW . $statsPlayer->getStats()->getDeaths(),
				TextFormat::WHITE . "KDR: " . TextFormat::YELLOW . $statsPlayer->getStats()->getKDR()
			],
			"Wins/Losses" => [
				TextFormat::WHITE . "Wins: " . TextFormat::YELLOW . $statsPlayer->getStats()->getWins(),
				TextFormat::WHITE . "Losses: " . TextFormat::YELLOW . $statsPlayer->getStats()->getLosses()
			],
			"Time Played" => [
				TextFormat::WHITE . "Time Played: " . TextFormat::YELLOW . $statsPlayer->getStats()->getFormattedTimePlayed()
			],
			"Blocks Broken" => [
				TextFormat::WHITE . "Emeralds Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getEmeraldsMined(),
				TextFormat::WHITE . "Diamonds Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getDiamondsMined(),
				TextFormat::WHITE . "Gold Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getGoldMined(),
				TextFormat::WHITE . "Lapis Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getLapisMined(),
				TextFormat::WHITE . "Redstone Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getRedstoneMined(),
				TextFormat::WHITE . "Iron Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getIronMined(),
				TextFormat::WHITE . "Coal Mined: " . TextFormat::YELLOW . $statsPlayer->getStats()->getCoalMined(),
			],
			"Damage/Healing" => [
				TextFormat::WHITE . "Hearts Healed: " . TextFormat::YELLOW . $statsPlayer->getStats()->getHeartsHealed(),
				TextFormat::WHITE . "Damage Dealt: " . TextFormat::YELLOW . $statsPlayer->getStats()->getDamageDealt(),
				TextFormat::WHITE . "Damage Taken: " . TextFormat::YELLOW . $statsPlayer->getStats()->getDamageTaken(),
				TextFormat::WHITE . "Fall Damage Taken: " . TextFormat::YELLOW . $statsPlayer->getStats()->getFallDamage()
			],
			"Consumables" => [
				TextFormat::WHITE . "Golden Apples Consumed: " . TextFormat::YELLOW . $statsPlayer->getStats()->getGoldenApples(),
				TextFormat::WHITE . "Golden Heads Consumed: " . TextFormat::YELLOW . $statsPlayer->getStats()->getGoldenHeads()
			]

		];
		foreach($categories as $name => $stats) {
			$this->addElement(new Button($name, function (GamePlayer $gamePlayer, $data) use($name, $stats): void {
				$gamePlayer->sendForm(new class($this, $name, $stats) extends CustomForm {
					public function __construct(StatsForm $form, string $name, array $stats) {
						parent::__construct($name, function (GamePlayer $player, $data) use($form): void {
							$player->sendForm($form);
						});
						foreach($stats as $stat) {
							$this->addElement(new Label($stat));
						}
					}
				});
			}));
		}
	}

}