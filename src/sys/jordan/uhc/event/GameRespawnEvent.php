<?php


namespace sys\jordan\uhc\event;


use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;

class GameRespawnEvent extends GameEvent {

	/** @var GamePlayer */
	private $player;

	public function __construct(Game $game, GamePlayer $player) {
		parent::__construct($game);
		$this->player = $player;
	}

	/**
	 * @return GamePlayer
	 */
	public function getPlayer(): GamePlayer {
		return $this->player;
	}

}