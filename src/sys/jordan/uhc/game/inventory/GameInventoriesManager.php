<?php


namespace sys\jordan\uhc\game\inventory;

use pocketmine\item\Item;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;

class GameInventoriesManager {

	use GameTrait;

	/** @var array */
	private $inventoryContents = [];

	/**
	 * GameInventoriesManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	public function clearInventoryContents(): void {
		$this->inventoryContents = [];
	}

	/**
	 * @param string $name
	 * @param array $armorContents
	 * @param Item[] $contents
	 */
	public function addInventoryContents(string $name, array $armorContents, array $contents): void {
		$this->inventoryContents[$name] = [
			"armorContents" => array_map(function (Item $item) { return $item->jsonSerialize(); }, $armorContents),
			"contents" => array_map(function (Item $item) { return $item->jsonSerialize(); }, $contents)
		];
	}

	/**
	 * @param string $name
	 * @return array
	 */
	public function getPlayerInventoryContents(string $name): array {
		if(!$this->hasInventoryContents($name)) {
			return [];
		}
		return $this->inventoryContents[$name];
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasInventoryContents(string $name): bool {
		return isset($this->inventoryContents[$name]);
	}

	/**
	 * @param string $name
	 */
	public function removeInventoryContents(string $name): void {
		if($this->hasInventoryContents($name)) {
			unset($this->inventoryContents[$name]);
		}
	}

}