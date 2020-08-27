<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\CommandScenario;
use sys\jordan\uhc\scenario\module\mana\ManaCommand;

class Mana extends CommandScenario {

	/** @var int */
	public const KILL_ADDITION_AMOUNT = 4;
	/** @var float */
	public const DAMAGE_SUBTRACT_AMOUNT = 0.5;

	public function __construct() {
		parent::__construct("Mana", "Players can use their levels in order to purchase items from a shop. Each time a player gets a kill, they gain 4 levels. Each time a player takes damage, they lose half a level.");
		$this->setCommand(new ManaCommand);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		if($event->isCancelled()) return;
		$player = $event->getEntity();
		if($player instanceof GamePlayer) {
			$level = max(0, $player->getXpLevel() - self::DAMAGE_SUBTRACT_AMOUNT);
			$player->setXpLevel($level);
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$cause = $event->getPlayer()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if($damager instanceof GamePlayer) {
				$damager->addXpLevels(self::KILL_ADDITION_AMOUNT);
				$this->sendMessage($damager, TextFormat::GREEN . "You have received " . self::KILL_ADDITION_AMOUNT . " for killing {$event->getPlayer()->getName()}!");
			}
		}
	}

}