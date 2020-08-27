<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\form\StatsForm;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;

class StatsCommand extends BaseUserCommand {

	/**
	 * ScenarioCommand constructor.
	 * @param GameBase $main
	 */
	public function __construct(GameBase $main) {
		parent::__construct($main, "stats", "Shows stats for the sender or another player", "/stats [player]");
	}

	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		$player = null;
		if(count($args) > 0) {
			$player = $this->getPlugin()->getServer()->getPlayer($args[0]);
		}
		if($player === null) $player = $sender;
		(new StatsForm($sender, $player))->send($sender);
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {

	}
}