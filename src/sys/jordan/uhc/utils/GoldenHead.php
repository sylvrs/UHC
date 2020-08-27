<?php
/**
 * File created by Matt(@yaboimattj)
 * Unauthorized access of this file will
 * result in legal punishment.
 */

namespace sys\jordan\uhc\utils;


use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\GoldenApple;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\tile\Skull;
use pocketmine\utils\TextFormat;

class GoldenHead extends GoldenApple {

	public function __construct(int $meta = 0) {
		parent::__construct($meta);
	}

	public function getAdditionalEffects() : array{
		if($this->getDamage() === Skull::TYPE_HUMAN) {
			return array(
				new EffectInstance(Effect::getEffect(Effect::REGENERATION), 20 * 9, 1),
				new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 2400)
			);
		}
		return parent::getAdditionalEffects();
	}

	/**
	 * @return Item
	 */
	public static function create(): Item {
		return (ItemFactory::get(Item::GOLDEN_APPLE, Skull::TYPE_HUMAN))->setCustomName(TextFormat::GOLD . "Golden Head");
	}

	public function getVanillaName(): string {
		return $this->meta === Skull::TYPE_HUMAN ? "Golden Head" : parent::getVanillaName();
	}

}