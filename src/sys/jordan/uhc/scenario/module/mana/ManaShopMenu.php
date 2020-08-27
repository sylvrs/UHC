<?php


namespace sys\jordan\uhc\scenario\module\mana;


use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use sys\jordan\core\form\elements\Button;
use sys\jordan\core\form\ModalForm;
use sys\jordan\core\form\SimpleForm;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\TickEnum;

class ManaShopMenu extends SimpleForm {

	/** @var ShopItem[] */
	private $shopItems = [];

	/**
	 * ManaShopMenu constructor.
	 * @param GamePlayer $observer
	 */
	public function __construct(GamePlayer $observer) {
		parent::__construct("Mana Shop", "");
		$this->setup();
		foreach($this->getShopItems() as $item) {
			$color = $observer->getXpLevel() >= $item->getCost() ? TextFormat::GREEN : TextFormat::RED;
			$this->addElement(new Button($item->getName() . " - " . $color . $item->getCost() . TextFormat::DARK_GRAY . " levels", function (GamePlayer $buyer) use($item): void {
				(new ModalForm("Are you sure you want to buy {$item->getName()} for {$item->getCost()} levels?", $item->getDescription(), "Purchase", "Decline", function (GamePlayer $player, $data) use ($item): void {
					if($data) {
						$item->call($player);
					} else {
						(new self($player))->send($player);
					}
				}))->send($buyer);
			}));
		}
	}

	public function setup(): void {
		$this->addShopItem("Gold Ingot", "Purchasing this item will grant you one gold ingot.", 3, function (GamePlayer $player): void {
			$player->getInventory()->addItem(ItemFactory::get(Item::GOLD_INGOT));
		});
		$this->addShopItem("Book", "Purchasing this item will give you one book.", 5, function (GamePlayer $player): void {
			$player->getInventory()->addItem(ItemFactory::get(Item::BOOK));
		});
		$this->addShopItem("Haste II", "Purchasing this item will give you haste II for 10 minutes.", 10, function (GamePlayer $player): void {
			$player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), TickEnum::MINUTE * 10, 1));
		});
		$this->addShopItem("Arrows", "Purchasing this item will give you 32 arrows.", 15, function (GamePlayer $player): void {
			$player->getInventory()->addItem(ItemFactory::get(Item::ARROW, 0, 32));
		});
		$this->addShopItem("Golden Apple", "Purchasing this item will give you one golden apple.", 20, function (GamePlayer $player): void {
			$player->getInventory()->addItem(ItemFactory::get(Item::GOLDEN_APPLE));
		});
		$this->addShopItem("Strength I", "Purchasing this item will give you strength I for 5 minutes.", 30, function (GamePlayer $player): void {
			$player->addEffect(new EffectInstance(Effect::getEffect(Effect::STRENGTH), TickEnum::MINUTE * 5));
		});
	}

	/**
	 * @return callable[]
	 */
	public function getShopItems(): array {
		return $this->shopItems;
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @param float $cost
	 * @param callable $callback
	 */
	public function addShopItem(string $name, string $description, float $cost, callable $callback): void {
		$this->shopItems[$name] = new ShopItem($name, $description, $cost, $callback);
	}



}