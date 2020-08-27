<?php


namespace sys\jordan\uhc\scenario\module\backpacks;


use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use sys\jordan\uhc\GamePlayer;
use function spl_object_hash;

class TeamBackpack {

	/** @var Item[] */
	private $items = [];

	/** @var BackpacksInventory[] */
	private $inventories = [];

	/**
	 * @return BackpacksInventory[]
	 */
	public function getInventories(): array {
		return $this->inventories;
	}

	/**
	 * @param BackpacksInventory $inventory
	 */
	public function addInventory(BackpacksInventory $inventory): void {
		if(!isset($this->inventories[spl_object_hash($inventory)])) {
			$this->inventories[spl_object_hash($inventory)] = $inventory;
		}
	}

	/**
	 * @param BackpacksInventory $inventory
	 */
	public function removeInventory(BackpacksInventory $inventory): void {
		if(isset($this->inventories[spl_object_hash($inventory)])) {
			unset($this->inventories[spl_object_hash($inventory)]);
		}
	}

	/**
	 * @return Item[]
	 */
	public function getItems(): array {
		return $this->items;
	}

	/**
	 * @param InventoryTransaction $transaction
	 */
	public function sync(InventoryTransaction $transaction): void {
		foreach($transaction->getInventories() as $inventory) {
			if($inventory instanceof BackpacksInventory) {
				$this->items = $inventory->getContents(true);
				foreach($this->getInventories() as $viewer) {
					if($viewer !== $inventory) $viewer->setContents($this->items);
				}
			}
		}
		foreach($this->getInventories() as $inventory) $inventory->sendContents($inventory->getViewers());
	}

	/**
	 * @param GamePlayer $player
	 */
	public function send(GamePlayer $player): void {
		$tile = Tile::createTile("BackpacksTile", $player->getLevelNonNull(), Chest::createNBT($player->subtract(0, 4)), $this);
		if($tile instanceof BackpacksTile) {
			$block = (BlockFactory::get(Block::CHEST))->setComponents($tile->getX(), $tile->getY(), $tile->getZ());
			$block->setLevel($tile->getLevelNonNull());
			$tile->getInventory()->setReplacementBlock($tile->getLevelNonNull()->getBlock($tile));
			$block->getLevelNonNull()->sendBlocks([$player], [$block]);
			$tile->spawnTo($player);
			$player->addWindow($tile->getInventory());
			$this->addInventory($tile->getInventory());
		}
	}

}