<?php


namespace sys\jordan\uhc\gamemodes\defaults;


use pocketmine\event\entity\EntityDamageEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\gamemodes\Gamemode;

class NoFall extends Gamemode {

	/**
	 * NoFall constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct($game,"No Fall");
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$event->setCancelled();
		}
	}

}