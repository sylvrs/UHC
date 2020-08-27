<?php


namespace sys\jordan\uhc\event;


use pocketmine\event\Event;
use sys\jordan\uhc\game\Game;

class GameEvent extends Event {

	/** @var Game */
	private $game;

	public function __construct(Game $game) {
		$this->game = $game;
	}

	/**
	 * @return Game
	 */
	public function getGame(): Game {
		return $this->game;
	}

}