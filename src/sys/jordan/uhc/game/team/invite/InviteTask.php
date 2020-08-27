<?php


namespace sys\jordan\uhc\game\team\invite;


use Exception;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\utils\TickEnum;

class InviteTask extends BaseTask {

	/** @var Invite */
	private $invite;

	/**
	 * InviteTask constructor.
	 * @param Invite $invite
	 */
	public function __construct(Invite $invite) {
		parent::__construct(GameBase::getInstance());
		$this->invite = $invite;
		$this->schedule(-1, TickEnum::MINUTE);
	}

	/**
	 * @return Invite
	 */
	public function getInvite(): Invite {
		return $this->invite;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(int $currentTick) {
		$this->getInvite()->getFrom()->sendMessage(TextFormat::RED . "The invite to {$this->getInvite()->getTo()->getName()} has expired!");
		$this->getInvite()->getTo()->sendMessage(TextFormat::YELLOW . "The invite from {$this->getInvite()->getFrom()->getName()} has expired!");
		try {
			$this->getInvite()->getTo()->removeInvite($this->getInvite()->getFrom());
		} catch(Exception $exception) {}
		$this->cancel();
	}

	public function __destruct() {
		foreach($this as $key => $value) unset($this->$key);
	}
}