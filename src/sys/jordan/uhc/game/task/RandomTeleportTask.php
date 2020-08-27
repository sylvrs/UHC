<?php


namespace sys\jordan\uhc\game\task;


use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\member\MemberManager;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GamePlayer;
use function array_rand;

class RandomTeleportTask extends BaseTask {

	/** @var int */
	public const TELEPORT_DELAY = 3;

	/** @var MemberManager $manager */
	private $manager;

	/** @var GamePlayer[]|Team[] */
	private $members;

	/**
	 * TeleportTask constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct($game->getPlugin());
		$this->manager = $game->getManager();
		$this->members = $game->getManager()->getMembers();
		$this->reschedule();
	}

	/**
	 * @return MemberManager
	 */
	public function getManager(): MemberManager {
		return $this->manager;
	}


	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick): void {
		if(count($this->members) > 0) {
			$key = array_rand($this->members);
			$member = $this->members[$key];
			$this->getManager()->scatter($member, true, false, function () use($key): void {
				$this->reschedule();
				unset($this->members[$key]);
			});
		} else {
			$this->cancel();
			$this->getManager()->getGame()->broadcast(TextFormat::GREEN . "All players have been scattered!");
		}
	}

	public function reschedule(): void {
		$this->cancel();
		$this->schedule(-1, self::TELEPORT_DELAY);
	}

}