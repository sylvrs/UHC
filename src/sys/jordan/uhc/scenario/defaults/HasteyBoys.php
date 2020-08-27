<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\scheduler\ClosureTask;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\scenario\Scenario;

class HasteyBoys extends Scenario {

	public function __construct() {
		parent::__construct("HasteyBoys", "All tools crafted will be enchanted with efficiency III");
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {}

	/**
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {
		$items = array_values($event->getOutputs());
		if(count($items) === 1) {
			$item = $items[0];
			if($item instanceof Pickaxe || $item instanceof Axe || $item instanceof Shovel) {
				$newItem = clone $item;
				$newItem->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 3));
				$newItem->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3));
				GameBase::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $currentTick) use($item, $newItem, $event): void {
					$index = 0;
					$inventory = $event->getPlayer()->getCursorInventory();
					$value = $inventory->getItem($index)->equalsExact($item);
					if(!$value) {
						foreach($event->getPlayer()->getInventory()->getContents(true) as $i => $inventoryItem) {
							if($inventoryItem->equalsExact($item)) {
								$value = true;
								$inventory = $event->getPlayer()->getInventory();
								$index = $i;
								break;
							}
						}
					}
					if($value) $inventory->setItem($index, $newItem);
				}));
			}
		}
	}
}