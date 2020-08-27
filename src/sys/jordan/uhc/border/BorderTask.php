<?php


namespace sys\jordan\uhc\border;


use sys\jordan\core\base\BaseTask;
use sys\jordan\uhc\GameBase;

class BorderTask extends BaseTask {

	/** @var Border */
	private $border;

	/** @var int */
	private $iteration = 0;

	/**
	 * BorderTask constructor.
	 * @param Border $border
	 */
	public function __construct(Border $border) {
		parent::__construct(GameBase::getInstance());
		$this->border = $border;
		$this->schedule(1);
	}

	/**
	 * @return Border
	 */
	public function getBorder(): Border {
		return $this->border;
	}

	/**
	 * @return int
	 */
	public function getIteration(): int {
		return $this->iteration;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(int $currentTick) {
		$size = $this->getBorder()->getSize();
		switch($this->getIteration()) {
			case 0:
				$this->getBorder()->createWall(-$size, $size, $size, $size);
				break;
			case 1:
				$this->getBorder()->createWall(-$size, $size, -$size, -$size);
				break;
			case 2:
				$this->getBorder()->createWall(-$size, -$size, -$size, $size);
				break;
			case 3:
				$this->getBorder()->createWall($size, $size, -$size, $size);
				$this->cancel();
				$this->destroy();
				return;
		}
		$this->iteration++;
	}

	public function destroy(): void {
		foreach($this as $key => $value) unset($this->$key);
	}
}