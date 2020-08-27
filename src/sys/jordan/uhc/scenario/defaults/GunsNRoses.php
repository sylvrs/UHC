<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\Scenario;
use function in_array;
use function mt_rand;

class GunsNRoses extends Scenario {

	/** @var int */
	public const BOW_DROP_PERCENTAGE = 2;

	public function __construct() {
		parent::__construct("Guns N Roses", "When you break a poppy you get 1 arrow. When you break a rose bush you get 4 arrows. You have a 2% chance of getting a bow from both of them.");
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
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		if(in_array($event->getBlock()->getId(), [Block::POPPY, Block::DOUBLE_PLANT])) {
			$block = $event->getBlock();
			if($block->getId() === Block::DOUBLE_PLANT) {
				/*
				 * 4 is the dmg meta for a rose bush.
				 * Why are there no constants for this yet?? (@dylan)
				 */
				if($block->getDamage() !== 4) {
					return;
				}
				$count = 4;
			} else {
				$count = 1;
			}
			$drops = [ItemFactory::get(Item::ARROW, 0, $count)];
			if(mt_rand(0, 100) <= self::BOW_DROP_PERCENTAGE) $drops[] = ItemFactory::get(Item::BOW);
			$event->setDrops($drops);
		}
	}
}