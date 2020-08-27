<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\scheduler\ClosureTask;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class Timber extends Scenario {

	public function __construct() {
		parent::__construct("Timber", "Mining a log from a tree will mine the entire tree");
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($event->getBlock() instanceof Wood || $event->getBlock() instanceof Wood2) {
			$this->breakBlock($player, $event->getBlock());
		}
	}

	/**
	 * @param GamePlayer $player
	 * @param Block $block
	 */
	public function breakBlock(GamePlayer $player, Block $block): void {
		$item = null;
		foreach($block->getAllSides() as $side) {
			if($side->getId() === $block->getId()) {
				$side->getLevel()->useBreakOn($side, $item, null, true);
				GameBase::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use($player, $side): void {
					$this->breakBlock($player, $side);
				}), 1);
			} else {
				foreach($side->getAllSides() as $adjacentSide) {
					if($adjacentSide->getId() === $block->getId()) {
						$adjacentSide->getLevel()->useBreakOn($adjacentSide, $item, null, true);
						GameBase::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use($player, $adjacentSide): void {
							$this->breakBlock($player, $adjacentSide);
						}), 1);
					}
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {
		// TODO: Implement onEnable() method.
	}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {
		// TODO: Implement onDisable() method.
	}
}