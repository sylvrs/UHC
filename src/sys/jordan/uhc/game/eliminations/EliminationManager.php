<?php


namespace sys\jordan\uhc\game\eliminations;


use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\GamePlayer;

class EliminationManager {

	use GameTrait;

	/** @var int[] */
	private $eliminations = [];

	/**
	 * EliminationManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param GamePlayer|string $player
	 * @return int
	 */
	public function getEliminations($player): int {
		if($player instanceof GamePlayer) $player = $player->getName();
		if(!isset($this->eliminations[$player])) $this->eliminations[$player] = 0;
		return $this->eliminations[$player];
	}

	/**
	 * @param GamePlayer|string $player
	 */
	public function addElimination($player): void {
		if($player instanceof GamePlayer) $player = $player->getName();
		if(!isset($this->eliminations[$player])) $this->eliminations[$player] = 0;
		$this->eliminations[$player] += 1;
	}

	/**
	 * @param int $count
	 * @return array
	 */
	public function getTopEliminations(int $count = 5): array {
		$filtered = array_filter($this->eliminations, function($eliminations) {return $eliminations > 0;});
		$output = [];
		if(count($filtered) > 0) {
			arsort($filtered);
			reset($filtered);
			for($i = 0; $i < ($count > count($filtered) ? count($filtered) : $count); $i++) {
				$output[key($filtered)] = current($filtered);
				next($filtered);
			}
		}
		return $output;
	}
}