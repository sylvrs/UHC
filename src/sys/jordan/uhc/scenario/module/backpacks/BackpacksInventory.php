<?php


namespace sys\jordan\uhc\scenario\module\backpacks;


use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use sys\jordan\uhc\GameBase;
use function spl_object_hash;

class BackpacksInventory extends ChestInventory {

	/**
	 * Delay before showing the real block (default 5 ticks)
	 */
	public const DELAY = 5;

	/**
	 * Only sync every x amount of ticks
	 */
	public const SYNC_TICKS = 5;

	/**
	 * The block data (id, meta, level, x, y, z)
	 *
	 * @var array
	 */
	private $replacementBlock = [];

	/** @var TeamBackpack */
	private $backpack;

	/** @var int */
	private $lastSyncTick;

	/**
	 * BackpacksInventory constructor.
	 * @param TeamBackpack $backpack
	 * @param BackpacksTile $tile
	 */
	public function __construct(TeamBackpack $backpack, BackpacksTile $tile) {
		parent::__construct($tile);
		$this->backpack = $backpack;
		$this->holder = $tile;
		$this->setContents($backpack->getItems());
		$this->setReplacementBlock($tile->getBlock());
		$this->lastSyncTick = GameBase::getInstance()->getServer()->getTick();
	}

	/**
	 * @return BackpacksTile
	 */
	public function getHolder(): BackpacksTile {
		return $this->holder;
	}

	/**
	 * @return Block
	 */
	public function getReplacementBlock(): Block {
		$block = BlockFactory::get($this->replacementBlock[0], $this->replacementBlock[1]);
		$block->setLevel($this->replacementBlock[2]);
		$block->setComponents($this->replacementBlock[3], $this->replacementBlock[4], $this->replacementBlock[5]);
		return $block;
	}

	/**
	 * @param Block $block
	 */
	public function setReplacementBlock(Block $block): void {
		$this->replacementBlock = [$block->getId(), $block->getDamage(), $block->getLevelNonNull(), $block->x, $block->y, $block->z];
	}

	/**
	 * @param Player $player
	 */
	public function sendRealBlock(Player $player): void {
		$block = $this->getReplacementBlock();
		if($block instanceof Block) {
			// nasty hack to prevent errors when closing server
			if(GameBase::getInstance()->isEnabled()) {
				GameBase::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use($player, $block): void {
					$block->getLevelNonNull()->sendBlocks([$player], [$block]);
				}), self::DELAY);
			}

		}
	}

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who): void {
		if(isset($this->viewers[spl_object_hash($who)])) {
			parent::onClose($who);
			$this->getHolder()->close();
			$this->sendRealBlock($who);
			$this->backpack->removeInventory($this);
		}
	}

}