<?php


namespace sys\jordan\uhc\scenario\defaults;


use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\CommandScenario;

class DeathSentence extends CommandScenario {

	/**
	 * DeathSentence constructor.
	 */
	public function __construct() {
		parent::__construct("Death Sentence", "Players are given 10 minutes to live. After their 10 minutes is over, the player dies. However, if a player mines a specific ore or kills a player, they will gain a certain amount of time to their lives.");
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {
		// TODO: Implement onEnable() method.
	}

	/**
	 * @param Game $game
	 */
	public function onDisable(Game $game): void {
		// TODO: Implement onDisable() method.
	}
}