<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\Scenario;

class Diamondless extends Scenario {

	public function __construct() {
		parent::__construct("Diamondless", "Diamonds do not drop from ores & gold only has a 50% chance of dropping. Killing a player will drop 1 diamond & 4 gold ingots.", self::PRIORITY_HIGH);
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		if($event->getBlock()->getItemId() === ItemIds::DIAMOND_ORE || ($event->getBlock()->getItemId() === ItemIds::GOLD_ORE && mt_rand(1, 2) <= 1)) {
			$event->setDrops([]);
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$drops = $event->getDrops();
		$drops[] = Item::get(Item::DIAMOND);
		$drops[] = Item::get(Item::GOLD_INGOT, 0, 4);
		$event->setDrops($drops);
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