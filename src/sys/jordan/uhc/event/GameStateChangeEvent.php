<?php


namespace sys\jordan\uhc\event;


use sys\jordan\uhc\game\Game;

class GameStateChangeEvent extends GameEvent {

	/** @var int */
	private $before;

	/** @var int */
	private $after;

	/**
	 * GameStateChangeEvent constructor.
	 * @param Game $game
	 * @param int $before
	 * @param int $after
	 */
	public function __construct(Game $game, int $before, int $after) {
		parent::__construct($game);
		$this->before = $before;
		$this->after = $after;
	}

	/**
	 * @return int
	 */
	public function getBefore(): int {
		return $this->before;
	}

	/**
	 * @return int
	 */
	public function getAfter(): int {
		return $this->after;
	}
}