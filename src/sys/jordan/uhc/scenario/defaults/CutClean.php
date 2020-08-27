<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\Scenario;

class CutClean extends Scenario {

	/**
	 * CutClean constructor.
	 */
	public function __construct() {
		parent::__construct("CutClean", "Iron and gold ore smelt automatically after mining.");
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		$block = $event->getBlock();
		$drops = $event->getDrops();
		$xp = $event->getXpDropAmount();
		switch($block->getItemId()) {
			case BlockIds::IRON_ORE:
				$drops = [Item::get(ItemIds::IRON_INGOT)];
				$xp = mt_rand(1, 3);
				break;
			case BlockIds::GOLD_ORE:
				$drops = [Item::get(ItemIds::GOLD_INGOT)];
				$xp = mt_rand(2, 4);
		}
		$event->setDrops($drops);
		$event->setXpDropAmount($xp);
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {

	}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {

	}
}