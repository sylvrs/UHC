<?php


namespace sys\jordan\uhc\scenario;


use sys\jordan\uhc\GamePlayer;

abstract class CooldownScenario extends Scenario {

	/** @var int */
	const DEFAULT_COUNTDOWN = 3;

	/** @var int */
	private $cooldownLength;

	/** @var int[] */
	private $cooldowns = [];

	/**
	 * Scenario constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $cooldownLength
	 * @param int $priority
	 */
	public function __construct(string $name, string $description, int $cooldownLength = self::DEFAULT_COUNTDOWN, int $priority = self::PRIORITY_MEDIUM) {
		parent::__construct($name, $description, $priority);
		$this->cooldownLength = $cooldownLength;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function addCooldown(GamePlayer $player) {
		if(!$this->hasCooldown($player)) {
			$this->cooldowns[$player->getName()] = time() + $this->cooldownLength;
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function removeCooldown(GamePlayer $player): void {
		if($this->hasCooldown($player)) {
			unset($this->cooldowns[$player->getName()]);
		}
	}

	/**
	 * @param GamePlayer $player
	 * @return int
	 */
	public function getCooldown(GamePlayer $player): int {
		return $this->hasCooldown($player) ? $this->cooldowns[$player->getName()] : -1;
	}

	/**
	 * @param GamePlayer $player
	 * @return int
	 */
	public function getCooldownLength(GamePlayer $player): int {
		return $this->hasCooldown($player) ? $this->cooldowns[$player->getName()] - time() : 0;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function hasCooldown(GamePlayer $player): bool {
		return isset($this->cooldowns[$player->getName()]);
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function hasExpired(GamePlayer $player): bool {
		if($this->hasCooldown($player)) {
			return time() >= $this->getCooldown($player);
		}
		return true;
	}

}