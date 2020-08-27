<?php


namespace sys\jordan\uhc\scenario;


use sys\jordan\core\base\BaseCommand;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GameBase;
use function strtolower;

abstract class CommandScenario extends Scenario {

	/** @var BaseCommand */
	private $command;

	/**
	 * CommandScenario constructor.
	 * @param string $name
	 * @param string $description
	 * @param int $priority
	 */
	public function __construct(string $name, string $description, int $priority = self::PRIORITY_MEDIUM) {
		parent::__construct($name, $description, $priority);
	}

	/**
	 * @return BaseCommand
	 */
	public function getCommand(): BaseCommand {
		return $this->command;
	}

	/**
	 * @param BaseCommand $command
	 */
	public function setCommand(BaseCommand $command): void {
		$this->command = $command;
	}

	public function register(): void {
		if($this->command !== null) {
			GameBase::getInstance()->getServer()->getCommandMap()->register(strtolower(GameBase::NAME), $this->getCommand());
			$this->refreshCommands();
		}
	}

	public function unregister(): void {
		if($this->command !== null) {
			GameBase::getInstance()->getServer()->getCommandMap()->unregister($this->getCommand());
			$this->refreshCommands();
		}
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {
		$this->register();
	}

	public function onDisable(Game $game): void {
		$this->unregister();
	}

	/**
	 * Update the player's command list
	 */
	public function refreshCommands(): void {
		foreach(GameBase::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$player->sendCommandData();
		}
	}

}