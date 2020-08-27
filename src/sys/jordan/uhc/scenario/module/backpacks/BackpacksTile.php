<?php


namespace sys\jordan\uhc\scenario\module\backpacks;


use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\tile\Chest;

class BackpacksTile extends Chest {

	/**
	 * BackpacksTile constructor.
	 * @param Level $level
	 * @param CompoundTag $nbt
	 * @param TeamBackpack $backpack
	 */
	public function __construct(Level $level, CompoundTag $nbt, TeamBackpack $backpack) {
		parent::__construct($level, $nbt);
		$this->setName("Backpack");
		$this->inventory = new BackpacksInventory($backpack, $this);
	}

	public function spawnToAll() {}

	public function spawnTo(Player $player): bool {
		return true;
	}

	/**
	 * @return ChestInventory|DoubleChestInventory|BackpacksInventory
	 */
	public function getInventory() {
		return parent::getInventory();
	}

	public function saveNBT(): CompoundTag {
		return new CompoundTag();
	}

}