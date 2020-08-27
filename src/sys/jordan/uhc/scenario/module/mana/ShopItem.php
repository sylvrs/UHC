<?php


namespace sys\jordan\uhc\scenario\module\mana;


use pocketmine\utils\TextFormat;
use sys\jordan\uhc\GamePlayer;
use function max;

class ShopItem {

	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/** @var float */
	private $cost;

	/** @var callable */
	private $callback;

	/**
	 * ShopItem constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $cost
	 * @param callable $callback
	 */
	public function __construct(string $name, string $description, int $cost, callable $callback) {
		$this->name = $name;
		$this->description = $description;
		$this->cost = $cost;
		$this->callback = $callback;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getCost(): int {
		return $this->cost;
	}

	public function canAfford(GamePlayer $player): bool {
		return $player->getXpLevel() >= $this->getCost();
	}

	/**
	 * @param GamePlayer $player
	 */
	public function call(GamePlayer $player): void {
		if($this->canAfford($player)) {
			$level = max(0, $player->getXpLevel() - $this->getCost());
			$player->setXpLevel($level);
			($this->callback)($player);
			$player->sendMessage(TextFormat::GREEN . "Successfully purchased {$this->getName()}!");
		} else {
			$player->sendMessage(TextFormat::RED . "You can't afford this item!");
		}
	}

}