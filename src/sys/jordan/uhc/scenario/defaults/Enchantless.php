<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\entity\object\ExperienceOrb;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class Enchantless extends Scenario {

	/** @var int */
	public const XP_LEVELS = 5;

	/**
	 * Enchantless constructor.
	 */
	public function __construct() {
		parent::__construct("Enchantless", "The only way to obtain XP is by killing a player");
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
	 * @param EntitySpawnEvent $event
	 */
	public function handleEntitySpawn(EntitySpawnEvent $event): void {
		if(($orb = $event->getEntity()) instanceof ExperienceOrb) {
			$orb->flagForDespawn();
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$cause = $event->getPlayer()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$attacker = $cause->getDamager();
			if($attacker instanceof GamePlayer && $attacker->inGame()) {
				$attacker->addXpLevels(self::XP_LEVELS);
				$attacker->sendMessage($this->asPrefix() . TextFormat::GREEN . " You have been given " . self::XP_LEVELS . " for killing {$event->getPlayer()->getName()}!");
			}
		}
	}
}