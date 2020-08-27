<?php


namespace sys\jordan\uhc\game\task;


use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;

class CalculatePositionsTask extends BaseTask {

	use GameTrait;

	/** @var GamePlayer[]|Team[] */
	private $members;

	public function __construct(Game $game) {
		parent::__construct($game->getPlugin());
		$this->setGame($game);
		$this->members = $game->getManager()->getMembers();
		$game->broadcast(TextFormat::YELLOW . "Calculating positions...");
		$this->reschedule();
	}

	/**
	 * @return GameBase
	 */
	public function getPlugin(): GameBase {
		return parent::getPlugin();
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick) {
		if(count($this->members) > 0) {
			$key = array_rand($this->members);
			$member = $this->members[$key];
			$this->getGame()->getManager()->calculate($member, function (Position $position) use($key, $member): void {
				$this->getGame()->getManager()->addScatterPosition($member, $position);
				$this->reschedule();
				unset($this->members[$key]);
			});
		} else {
			$this->cancel();
			$this->getGame()->broadcast(TextFormat::GREEN . "All positions calculated. Starting countdown...");
			$this->getGame()->setState(GameStatus::COUNTDOWN);
		}
	}

	public function reschedule(): void {
		$this->cancel();
		$this->schedule(-1);
	}
}