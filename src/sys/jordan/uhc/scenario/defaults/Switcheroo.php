<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class Switcheroo extends Scenario {

	public function __construct() {
		parent::__construct("Switcheroo", "When you shoot someone, you trade places with them");
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
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		if($event instanceof EntityDamageByChildEntityEvent) {
			$victim = $event->getEntity();
			$damager = $event->getDamager();
			if($victim instanceof GamePlayer && $damager instanceof GamePlayer && $event->getChild() instanceof Arrow) {
				$victimLocation = $victim->asLocation();
				$damagerLocation = $damager->asLocation();
				$victim->teleport($damagerLocation);
				$this->sendMessage($victim, TextFormat::YELLOW . "You were shot by {$damager->getName()} and have switched places with them!");
				$damager->teleport($victimLocation);
				$this->sendMessage($damager, TextFormat::YELLOW . "You shot {$victim->getName()} and have switched places with them!");

			}
		}
	}
}