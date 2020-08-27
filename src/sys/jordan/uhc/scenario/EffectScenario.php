<?php


namespace sys\jordan\uhc\scenario;


use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

abstract class EffectScenario extends Scenario {

	/** @var EffectInstance[] */
	private $effects = [];

	/**
	 * Scenario constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $priority
	 */
	public function __construct(string $name, string $description, int $priority = self::PRIORITY_MEDIUM) {
		parent::__construct($name, $description, $priority);
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getEffects(): array {
		return $this->effects;
	}

	/**
	 * @param EffectInstance $effect
	 */
	public function addEffect(EffectInstance $effect) {
		if(!isset($this->effects[$effect->getId()])) {
			$this->effects[$effect->getId()] = $effect;
		}
	}


	/**
	 * @param mixed ...$players
	 */
	public function giveEffects(...$players) {
		foreach($players as $player) {
			foreach($this->getEffects() as $effect) {
				$player->addEffect(new EffectInstance(Effect::getEffect($effect->getId()), $effect->getDuration(), $effect->getAmplifier(), $effect->isVisible()));
			}
		}
	}

	/**
	 * @param mixed ...$players
	 */
	public function removeEffects(...$players) {
		foreach($players as $player) {
			foreach($this->getEffects() as $effect) {
				if($player->hasEffect($effect->getId())) {
					$player->removeEffect($effect->getId());
				}
			}
		}
	}

}