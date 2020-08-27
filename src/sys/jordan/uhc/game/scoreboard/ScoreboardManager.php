<?php


namespace sys\jordan\uhc\game\scoreboard;


use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\GamePlayer;

class ScoreboardManager {

	use GameTrait;

	/** @var ScoreboardExtraData[] */
	private $extraData = [];

	/**
	 * ScoreboardManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}
	/**
	 * @param GamePlayer $player
	 */
	public function send(GamePlayer $player): void {
		$player->getScoreboard()->setLineArray($this->getScoreboardData($player));
	}

	public function update(): void {
		foreach($this->getGame()->getAll() as $player) $this->send($player);
	}

	public function clear(): void {
		foreach($this->getGame()->getAll() as $player) $player->getScoreboard()->clearLines();
	}

	/**
	 * @param GamePlayer $player
	 * @return array
	 */
	public function getScoreboardData(GamePlayer $player): array {
		$line = TextFormat::WHITE . str_repeat("-", 18);
		$data = [
			1 => $line
		];
		$manager = $this->getGame()->getManager();
		switch($this->getGame()->getState()) {
			case GameStatus::WAITING:
				$data[] = TextFormat::WHITE . "Gamemode: " . TextFormat::YELLOW . ($this->getGame()->isTeams() ? "Team of " . $manager->getMaxPlayerCount() : "FFA");
				$data[] = "";
				$data[] = TextFormat::WHITE . "Scenarios: ";
				if(count($this->getGame()->getScenarioList()->getScenarios()) <= 0) {
					$data[] = TextFormat::WHITE . "- " . TextFormat::RED . "NONE";
				} else {
					foreach($this->getGame()->getScenarioList()->getNameSortedArray() as $scenario) {
						$data[] = TextFormat::WHITE . "- " . TextFormat::YELLOW . $scenario->getName();
					}
				}
				$data[] = "";
				foreach($manager->getScoreboardData() as $managerDatum) {
					$data[] = $managerDatum;
				}
				break;
			default:
				$data[] = TextFormat::WHITE . "Game Time: " . TextFormat::YELLOW . $this->getGame()->getFormattedTime() . str_repeat(" ", 4);
				if($this->getGame()->isTeams()) {
					$data[] = "";
				}
				foreach($manager->getScoreboardData() as $managerDatum) {
					$data[] = $managerDatum;
				}
				if($manager->isPlayer($player)) {
					$data[] = "";
					if($this->getGame()->isTeams() && $player->inTeam()) {
						$data[] = TextFormat::WHITE . "Team Eliminations: " . TextFormat::YELLOW . $player->getTeam()->getEliminations();
					}
					$data[] = TextFormat::WHITE . "Eliminations: " . TextFormat::YELLOW . $this->getGame()->getEliminationsManager()->getEliminations($player) . str_repeat(" ", 4);
					$data[] = "";
				}
				$data[] = TextFormat::WHITE . "Border: " . TextFormat::YELLOW . $this->getGame()->getBorder()->getSize();
		}
		if($this->hasExtraData($player)) {
			foreach($this->getExtraData($player)->getData() as $extraDatum) $data[] = $extraDatum;
		}
		$data[] = $line;
		$data[] = TextFormat::WHITE . "IP: " . TextFormat::YELLOW . "valiantnetwork.xyz";
		return $data;
	}

	/**
	 * @param GamePlayer $player
	 * @return ScoreboardExtraData
	 */
	public function getExtraData(GamePlayer $player): ScoreboardExtraData {
		return $this->extraData[$player->getLowerCaseName()] ?? ($this->extraData[$player->getLowerCaseName()] = new ScoreboardExtraData($player->getLowerCaseName()));
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function hasExtraData(GamePlayer $player): bool {
		return count($this->getExtraData($player)->getData()) > 0;
	}

}