<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\Scenario;

class DoubleOres extends Scenario {

	public function __construct() {
		parent::__construct("Double Ores", "All ores mined are multiplied by 2", self::PRIORITY_HIGH);
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		if(in_array($event->getBlock()->getId(), [BlockIds::COAL_ORE, BlockIds::IRON_ORE, BlockIds::LAPIS_ORE, BlockIds::GOLD_ORE, BlockIds::REDSTONE_ORE, BlockIds::DIAMOND_ORE])) {
			$drops = $event->getDrops();
			foreach($drops as $drop) {
				$drop->setCount($drop->getCount() * 2);
			}
			$event->setDrops($drops);
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