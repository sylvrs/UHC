<?php
/**
 * File created by Matt(@yaboimattj)
 * Unauthorized access of this file will
 * result in legal punishment.
 */

namespace sys\jordan\uhc\scenario\defaults;

use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\level\sound\FizzSound;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class DoubleJump extends Scenario {

	/** @var float */
	const DOUBLE_JUMP_DISTANCE = 0.85;

	public function __construct() {
		parent::__construct("Double Jump", "Allows players to double jump. Fall damage is disabled.");
	}

	/**
	 * @param PlayerJumpEvent $event
	 */
	public function handleJump(PlayerJumpEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		/*
		 * Allows for double jumping by enabling flight.
		 * This will test for a second jump button press.
		 */
		$player->setAllowFlight(true);
	}

	/**
	 * @param PlayerToggleFlightEvent $event
	 */
	public function handleToggleFlight(PlayerToggleFlightEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if(!$player->isCreative() && $player->getAllowFlight()) {
			$event->setCancelled();
			$player->setAllowFlight(false);
			$this->jump($player);
		}
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @param Game $game
	 */
	public function onDisable(Game $game): void {}

	/**
	 * @param GamePlayer $player
	 */
	public function jump(GamePlayer $player): void {
		$delta = $player->getDirectionVector()->multiply(self::DOUBLE_JUMP_DISTANCE);
		$player->setMotion($player->getMotion()->add($delta->x, self::DOUBLE_JUMP_DISTANCE, $delta->z));
		$player->getLevel()->addSound(new FizzSound($player->getLocation()), [$player]);
	}
}