<?php


namespace sys\jordan\uhc;


use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\tile\Skull;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\utils\GameBaseTrait;

class CraftingAddons {

	use GameBaseTrait;

	/**
	 * CraftingAddons constructor.
	 * @param GameBase $plugin
	 */
	public function __construct(GameBase $plugin) {
		$this->setPlugin($plugin);
		$this->addDefaultRecipes();
	}

	public function addDefaultRecipes(): void {
		$this->getPlugin()->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
			["ggg", "ghg", "ggg"],
			["g" => Item::get(ItemIds::GOLD_INGOT, 0, 1), "h" => Item::get(ItemIds::SKULL, Skull::TYPE_HUMAN, 1)],
			[Item::get(ItemIds::GOLDEN_APPLE, Skull::TYPE_HUMAN)->setCustomName(TextFormat::GOLD . "Golden Head")]
		));
		$this->getPlugin()->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
			["f", "s", "s"],
			["f" => Item::get(ItemIds::FLINT, 0, 1), "s" => Item::get(ItemIds::STICK, 0, 1)],
			[Item::get(ItemIds::ARROW, 0, 3)]));
		$this->getPlugin()->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
			["s ", " s", "s "],
			["s" => Item::get(ItemIds::STICK, 0, 1)],
			[Item::get(ItemIds::BOW, 0, 1)]));
		$this->getPlugin()->getServer()->getCraftingManager()->buildCraftingDataCache();
	}

}