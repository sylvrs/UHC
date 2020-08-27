<?php


namespace sys\jordan\uhc\player;


class PlayerSettings {

	/** @var bool */
	private $coordinates = true;

	/**
	 * @return bool
	 */
	public function isCoordinatesEnabled(): bool {
		return $this->coordinates;
	}

	/**
	 * @param bool $coordinates
	 */
	public function setCoordinatesEnabled(bool $coordinates): void {
		$this->coordinates = $coordinates;
	}
}