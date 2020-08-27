<?php


namespace sys\jordan\uhc\game\team\invite;


use sys\jordan\uhc\GamePlayer;

class Invite {

	/** @var GamePlayer */
	private $from;

	/** @var GamePlayer */
	private $to;

	/** @var InviteTask */
	private $task;

	/**
	 * Invite constructor.
	 * @param GamePlayer $from
	 * @param GamePlayer $to
	 */
	public function __construct(GamePlayer $from, GamePlayer $to) {
		$this->from = $from;
		$this->to = $to;

		$this->task = new InviteTask($this);
	}

	/**
	 * @return GamePlayer
	 */
	public function getFrom(): GamePlayer {
		return $this->from;
	}

	/**
	 * @return GamePlayer
	 */
	public function getTo(): GamePlayer {
		return $this->to;
	}

	/**
	 * @return InviteTask
	 */
	public function getTask(): InviteTask {
		return $this->task;
	}

}