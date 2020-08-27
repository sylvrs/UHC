<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use sys\jordan\uhc\event\GameStartEvent;
use sys\jordan\uhc\event\GameStopEvent;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\scenario\EffectScenario;


class CatEyes extends EffectScenario {


	/**
	 * CatEyes constructor.
	 */
	public function __construct() {
		parent::__construct("Cat Eyes", "All players receive night vision when the game begins!");
		$this->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), INT32_MAX, 1, false));
	}


	/**
	 * @param GameStartEvent $event
	 */
	public function handleStart(GameStartEvent $event): void {
		$this->giveEffects(...array_values($event->getGame()->getManager()->getPlayers()));
	}

	/**
	 * @param GameStopEvent $event
	 */
	public function handleStop(GameStopEvent $event): void {
		$this->removeEffects(...array_values($event->getGame()->getManager()->getPlayers()));
	}

	/**
	 * @inheritDoc
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @inheritDoc
	 */
	public function onDisable(Game $game): void {}
}