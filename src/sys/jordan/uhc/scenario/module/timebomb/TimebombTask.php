<?php


namespace sys\jordan\uhc\scenario\module\timebomb;


use pocketmine\level\Explosion;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\scenario\defaults\Timebomb;

class TimebombTask extends BaseTask {

	/** @var Game */
	private $game;

	/** @var string */
	private $name;

//	/** @var FloatingTextParticle */
//	private $particle;

	/** @var Position */
	private $position;

	/** @var int */
	private $countdown = Timebomb::EXPLOSION_DELAY;

	/**
	 * TimebombTask constructor.
	 * @param Game $game
	 * @param string $name
	 * @param Position $position
	 */
	public function __construct(Game $game, string $name, Position $position) {
		parent::__construct(GameBase::getInstance());
		$this->game = $game;
		$this->name = $name;
		$this->position = $position;
		//$this->particle = new FloatingTextParticle($position->asVector3(), TextFormat::YELLOW . $this->countdown, TextFormat::YELLOW . $this->name . TextFormat::WHITE . " corpse will explode in: ");
		//$this->updateTag();
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 * @return void
	 * @noinspection PhpStatementHasEmptyBodyInspection (REMOVE when Timebomb's text is centered)
	 */
	public function onRun(int $currentTick): void {
		if(--$this->countdown <= 0) {
			$this->explode();
			//$this->removeTag();
			$this->cancel();
		} else {
			//$this->updateTag();
		}
	}

	public function explode(): void {
		$explosion = new Explosion($this->position, Timebomb::EXPLOSION_SIZE);
		$explosion->explodeA();
		$explosion->explodeB();
	}

//	public function updateTag(): void {
//		$this->particle->setText(TextFormat::YELLOW . $this->countdown);
//		$this->sendTag();
//	}
//
//	public function removeTag(): void {
//		$this->particle->setInvisible();
//		$this->sendTag();
//	}
//
//	public function sendTag(): void {
//		$this->position->getLevelNonNull()->addParticle($this->particle);
//	}

}