<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\entity\EntityDamageEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class Fireless extends Scenario {

	public function __construct() {
		parent::__construct("Fireless", "All types of fire damage are nullified");
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getEntity();
		$fireDamages = [EntityDamageEvent::CAUSE_FIRE, EntityDamageEvent::CAUSE_FIRE_TICK, EntityDamageEvent::CAUSE_LAVA];
		if(in_array($event->getCause(), $fireDamages)) {
			$event->setCancelled();
			$player->extinguish();
		}
	}
}