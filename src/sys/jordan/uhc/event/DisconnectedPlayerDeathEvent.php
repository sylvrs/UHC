<?php


namespace sys\jordan\uhc\event;


use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use sys\jordan\uhc\player\DisconnectedPlayer;

class DisconnectedPlayerDeathEvent extends Event implements Cancellable {

	/** @var DisconnectedPlayer */
	private $disconnectedPlayer;

	/**
	 * DisconnectedPlayerDeathEvent constructor.
	 * @param DisconnectedPlayer $disconnectedPlayer
	 */
	public function __construct(DisconnectedPlayer $disconnectedPlayer) {
		$this->disconnectedPlayer = $disconnectedPlayer;
	}

	/**
	 * @return DisconnectedPlayer
	 */
	public function getDisconnectedPlayer(): DisconnectedPlayer {
		return $this->disconnectedPlayer;
	}

}