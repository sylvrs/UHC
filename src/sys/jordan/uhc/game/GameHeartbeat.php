<?php


namespace sys\jordan\uhc\game;


use sys\jordan\core\base\BaseTask;

class GameHeartbeat extends BaseTask {

	use GameTrait;

	/**
	 * GameHeartbeat constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct($game->getPlugin());
		$this->setGame($game);
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(int $currentTick) {
		$this->getGame()->update();
	}
}