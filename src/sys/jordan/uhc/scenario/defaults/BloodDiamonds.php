<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemIds;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\Scenario;

class BloodDiamonds extends Scenario {

	public function __construct() {
		parent::__construct("Blood Diamonds", "Every time a player mines a diamond, the player takes half a heart of damage.");
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		$player = $event->getPlayer();
		if($event->getBlock()->getItemId() === ItemIds::DIAMOND_ORE) {
			$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, 1));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {}
}