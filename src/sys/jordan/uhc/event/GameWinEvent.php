<?php


namespace sys\jordan\uhc\event;


use pocketmine\event\Event;
use sys\jordan\uhc\GamePlayer;

class GameWinEvent extends Event {

	/** @var GamePlayer */
	private $player;

	/**
	 * GameWinEvent constructor.
	 * @param GamePlayer $player
	 */
	public function __construct(GamePlayer $player) {
		$this->player = $player;
	}

	/**
	 * @return GamePlayer
	 */
	public function getPlayer(): GamePlayer {
		return $this->player;
	}

}