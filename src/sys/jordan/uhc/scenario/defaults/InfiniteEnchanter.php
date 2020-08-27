<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\entity\Attribute;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use sys\jordan\uhc\event\GameRespawnEvent;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;

class InfiniteEnchanter extends Scenario {

	public function __construct() {
		parent::__construct("Infinite Enchanter", "All players start with 128 bookshelves, infinite XP levels, 64 anvils, and 64 enchantment tables");
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
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		foreach($event->getGame()->getManager()->getOnlinePlayers() as $player) {
			$this->setup($player);
		}
	}

	/**
	 * @param GameRespawnEvent $event
	 */
	public function handleGameRespawn(GameRespawnEvent $event): void {
		if($event->getGame()->getState() > GameStatus::COUNTDOWN) {
			$this->setup($event->getPlayer());
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function setup(GamePlayer $player): void {
		$player->getInventory()->addItem(ItemFactory::get(Item::BOOKSHELF, 0, 128), ItemFactory::get(Item::ANVIL, 0, 64), ItemFactory::get(Item::ENCHANTMENT_TABLE, 0, 64));
		$player->setXpLevel(Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL)->getMaxValue());
	}
}