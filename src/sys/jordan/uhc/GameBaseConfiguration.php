<?php


namespace sys\jordan\uhc;


class GameBaseConfiguration {

	/** @var string */
	private $prefix;

	public function __construct(string $prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->prefix;
	}

}