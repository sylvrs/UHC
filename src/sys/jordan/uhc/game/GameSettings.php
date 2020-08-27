<?php


namespace sys\jordan\uhc\game;


class GameSettings {

	/** @var bool */
	private $mobileOnly = false;

	/** @var bool */
	private $netherEnabled = false;

	/** @var int */
	private $appleRate = 10;

	/** @var bool */
	private $globalMute = false;

	/**
	 * @return bool
	 */
	public function isMobileOnly(): bool {
		return $this->mobileOnly;
	}

	/**
	 * @param bool $mobileOnly
	 */
	public function setMobileOnly(bool $mobileOnly): void {
		$this->mobileOnly = $mobileOnly;
	}

	/**
	 * @return bool
	 */
	public function isNetherEnabled(): bool {
		return $this->netherEnabled;
	}

	/**
	 * @param bool $netherEnabled
	 */
	public function setNetherEnabled(bool $netherEnabled = true): void {
		$this->netherEnabled = $netherEnabled;
	}

	/**
	 * @return int
	 */
	public function getAppleRate(): int {
		return $this->appleRate;
	}

	/**
	 * @param int $appleRate
	 */
	public function setAppleRate(int $appleRate): void {
		$this->appleRate = $appleRate;
	}

	/**
	 * @return bool
	 */
	public function isGlobalMuteEnabled(): bool {
		return $this->globalMute;
	}

	/**
	 * @param bool $value
	 */
	public function setGlobalMute(bool $value = true): void {
		$this->globalMute = $value;
	}

	/**
	 * @return array
	 */
	public function serialize(): array {
		return [
			"appleRate" => $this->getAppleRate(),
			"globalMute" => $this->isGlobalMuteEnabled(),
			"mobileOnly" => $this->isMobileOnly()
		];
	}
}