<?php


namespace sys\jordan\uhc\utils;


use sys\jordan\uhc\GameBase;

trait GameBaseTrait {

	/** @var GameBase */
	private $plugin;

	/**
	 * @return GameBase
	 */
	public function getPlugin(): GameBase {
		return $this->plugin;
	}

	/**
	 * @param GameBase $plugin
	 */
	public function setPlugin(GameBase $plugin): void {
		$this->plugin = $plugin;
	}

}