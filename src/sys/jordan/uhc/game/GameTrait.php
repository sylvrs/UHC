<?php


namespace sys\jordan\uhc\game;


trait GameTrait {

	/** @var Game|null */
	private $game = null;

	/**
	 * @return Game
	 */
	public function getGame(): ?Game {
		return $this->game;
	}

	/**
	 * @param Game $game
	 */
	public function setGame(Game $game): void {
		$this->game = $game;
	}
}