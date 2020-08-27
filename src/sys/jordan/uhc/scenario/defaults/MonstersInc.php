<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\Door;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\CooldownScenario;
use function abs;
use function array_filter;
use function array_rand;
use function mt_rand;

class MonstersInc extends CooldownScenario {

	/** @var Door[] */
	private $doors = [];

	public function __construct() {
		parent::__construct("Monsters Inc.", "Upon clicking a door, you will be teleported to another door if there are 2 or more, otherwise, you'll be teleported to a random location on the map");
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {
		$this->doors = [];
	}

	/**
	 * @param Game $game
	 */
	public function onDisable(Game $game): void {
		$this->doors = [];
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		$block = $event->getBlock();
		if($block instanceof Door && !$this->exists($block)) {
			$this->add($block);
			$event->getPlayer()->sendMessage($this->asPrefix() . TextFormat::YELLOW . " You have created a new door!");
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		$block = $event->getBlock();
		if($block instanceof Door && $this->exists($block)) {
			$this->remove($block);
			$event->getPlayer()->sendMessage($this->asPrefix() . TextFormat::YELLOW . " You have destroyed an existing door!");
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
		$block = $event->getBlock();
		if($block instanceof Door && $this->exists($block)) {
			/** @var GamePlayer $player */
			$player = $event->getPlayer();
			if($this->hasExpired($player)) {
				$this->teleport($player, $block);
				$this->addCooldown($player);
			} else {
				$this->sendMessage($player, TextFormat::RED . "You must wait " . TextFormat::YELLOW . $this->getCooldownLength($player) . "s" . TextFormat::RED . " before using a door again!");
			}
		}
	}

	/**
	 * @param Door $door
	 * @return bool
	 */
	public function exists(Door $door): bool {
		return isset($this->doors[$door->asPosition()->__toString()]);
	}

	/**
	 * @param Door $door
	 */
	public function add(Door $door): void {
		$position = $door->asPosition();
		$this->doors[$position->__toString()] = $position;
	}

	/**
	 * @param Door $door
	 */
	public function remove(Door $door): void {
		if($this->exists($door)) {
			unset($this->doors[$door->asPosition()->__toString()]);
		}
	}

	/**
	 * @param GamePlayer $player
	 * @param Door $clicked
	 * @return Position
	 */
	public function pick(GamePlayer $player, Door $clicked): ?Position {
		$game = $player->getGame();
		$filtered = array_filter($this->doors, function (Position $position) use($game, $clicked): bool {
			$size = $game->getBorder()->getSize();
			 return (abs($position->getX()) < $size) && (abs($position->getZ()) < $size) && ($position->__toString() !== $clicked->asPosition()->__toString());
		});
		if(count($filtered) < 2) {
			return null;
		}
		return $filtered[array_rand($filtered, 1)] ?? null;
	}

	/**
	 * @param GamePlayer $player
	 * @param Door $clicked
	 */
	public function teleport(GamePlayer $player, Door $clicked): void {
		$position = $this->pick($player, $clicked);
		$random = false;
		if($position === null) {
			$size = (int) floor($player->getGame()->getBorder()->getSize() * 0.95);
			$position = new Position(mt_rand(-$size, $size), 128, mt_rand(-$size, $size), $player->getLevelNonNull());
			$random = true;
		}
		$position->y = $position->getLevelNonNull()->getHighestBlockAt($position->getFloorX(), $position->getFloorZ());
		if($position->y < 1) $position->y = 128;
		$position->y += 1;
		$player->teleport($position);
		$this->sendMessage($player, TextFormat::YELLOW . " You have been teleported to a random " . ($random ? "location" : "door") . " on the map!");
		$player->getLevel()->addSound(new EndermanTeleportSound($player->asPosition()), [$player]);
	}
}