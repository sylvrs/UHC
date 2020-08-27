<?php


namespace sys\jordan\uhc\border;


use Exception;
use pocketmine\block\BlockIds;
use pocketmine\entity\Living;
use pocketmine\level\Location;
use pocketmine\level\utils\SubChunkIteratorManager;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use function array_filter;
use function array_key_first;
use function mt_rand;

class Border {

	/** @var int */
	public const DEFAULT_SIZE = 1500;
	/** @var int */
	public const RANDOM_TELEPORT_THRESHOLD = 250;

	/** @var string */
	public const PREFIX = TextFormat::RED . "[Border]";

	/** @var int */
	public const WALL_HEIGHT = 3;

	/** @var Game */
	private $game;

	/** @var int */
	private $size;

	/** @var SubChunkIteratorManager */
	private $iterator;

	/** @var int */
	private $borderIndex = 0;

	/** @var int[] */
	private $borders;

	/** @var float */
	private $teleportDistance = 3.5;

	/** @var int[] */
	private $passableMaterials = [
		BlockIds::AIR,
		BlockIds::DOUBLE_PLANT,
		BlockIds::LEAVES,
		BlockIds::LEAVES2,
		BlockIds::LILY_PAD,
		BlockIds::LOG,
		BlockIds::LOG2,
		BlockIds::RED_FLOWER,
		BlockIds::SNOW_LAYER,
		BlockIds::TALL_GRASS,
		BlockIds::VINES,
		BlockIds::YELLOW_FLOWER
	];

	/** @var int[] */
	private $broadcastTimes = [60, 30, 10, 5];

	/** @var bool */
	private $canShrink = true;
	/** @var int */
	private $nextSize = -1;
	/** @var int */
	private $nextTime = -1;

	/**
	 * Border constructor.
	 * @param Game $game
	 * @param int $size
	 * @param bool $createBorder
	 * @throws Exception
	 */
	public function __construct(Game $game, int $size, bool $createBorder = true) {
		$this->game = $game;
		$this->size = $size;
		$this->borders = BorderValues::get($this->getSize());
		$this->iterator = new SubChunkIteratorManager($game->getLevel(), false);
		if($createBorder) $this->create();
		$this->update();
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function isPassable(int $id): bool {
		return in_array($id, $this->passableMaterials);
	}

	/**
	 * @return Game
	 */
	public function getGame(): Game {
		return $this->game;
	}

	/**
	 * @return int
	 */
	public function getBorderIndex(): int {
		return $this->borderIndex;
	}

	/**
	 * The statements in this method are used to update our flags
	 * & properties
	 */
	private function update(): void {
		$this->canShrink = $this->canShrink(false);
		$this->nextSize = $this->getNextBorderSize(false);
		$this->nextTime = $this->getNextBorderTime(false);
	}

	/**
	 * @param int $borderIndex
	 * @return int
	 */
	public function getBorderTime(int $borderIndex): int {
		$keys = array_keys($this->borders);
		if(isset($keys[$borderIndex])) {
			return $keys[$borderIndex];
		}
		return -1;
	}

	/**
	 * @param bool $fromCache
	 * @return int
	 */
	public function getNextBorderTime(bool $fromCache = true): int {
		if($fromCache) return $this->nextTime;
		if($this->canShrink()) {
			return $this->getBorderTime($this->getBorderIndex());
		}
		return -1;
	}

	/**
	 * @param bool $fromCache
	 * @return int
	 */
	public function getNextBorderSize(bool $fromCache = true): int {
		if($fromCache) return $this->nextSize;
		$key = $this->getNextBorderTime();
		if($key !== -1) {
			return $this->getBorders()[$key] ?? -1;
		}
		return -1;
	}

	/**
	 * @return int
	 */
	public function getSize(): int {
		return $this->size;
	}

	/**
	 * @return SubChunkIteratorManager
	 */
	public function getIterator(): SubChunkIteratorManager {
		return $this->iterator;
	}

	/**
	 * @return float
	 */
	public function getTeleportDistance(): float {
		return $this->teleportDistance;
	}

	/**
	 * @return int[]
	 */
	public function getBorders(): array {
		return $this->borders;
	}

	/**
	 * @param bool $fromCache
	 * @return bool
	 */
	public function canShrink(bool $fromCache = true): bool {
		if($fromCache) return $this->canShrink;
		return $this->borderIndex <= (count($this->borders) - 1);
	}

	/**
	 * Used to shrink the border & create the bedrock border for it
	 */
	public function shrink(): void {
		$this->size = $this->borders[array_keys($this->borders)[$this->borderIndex]];
		$this->create();
		$this->borderIndex++;
		$this->update();
	}

	/**
	 * Calls made when the border shrinks
	 */
	public function executeShrink(): void {
		$this->shrink();
		$this->getGame()->broadcast(Border::PREFIX . TextFormat::WHITE . " The border has been shrank to " . TextFormat::YELLOW . $this->getSize() . TextFormat::WHITE . "x" . TextFormat::YELLOW . $this->getSize() . TextFormat::WHITE . "!");
		$this->teleportPlayers();
	}

	/**
	 * @param Living $living
	 * @return bool
	 */
	public function inBorder(Living $living): bool {
		if(($living instanceof GamePlayer && !$living->isOnline()) || !$living->isValid()) {
			return true;
		}
		list($x, $z) = [abs($living->getFloorX()), abs($living->getFloorZ())];
		return $living->getLevel()->getFolderName() === $this->getGame()->getLevel()->getFolderName() && ($x <= $this->getSize() && $z <= $this->getSize());
	}

	/**
	 * @param Living $living
	 */
	public function teleport(Living $living): void {
		if(($living instanceof GamePlayer && !$living->isOnline()) || !$living->isValid()) {
			return;
		}
		$outsideX = ($living->getFloorX() < 0 ? $living->getFloorX() <= -$this->getSize() : $living->getFloorX() >= $this->getSize());
		$outsideZ = ($living->getFloorZ() < 0 ? $living->getFloorZ() <= -$this->getSize() : $living->getFloorZ() >= $this->getSize());
		$teleportDistance = $this->getTeleportDistance() > $this->getSize() ? $this->getSize() / 2 : $this->getTeleportDistance();
		$position = $living->asPosition();
		$position->x = $outsideX ? (($living->getFloorX() <=> 0) * ($this->getSize() - $teleportDistance)) : $position->x;
		$position->z = $outsideZ ? (($living->getFloorZ() <=> 0) * ($this->getSize() - $teleportDistance)) : $position->z;

		$position->y = $this->getGame()->getLevel()->getHighestBlockAt($position->getX(), $position->getZ()) + 1;
		if($position->y <= 1) $position->y = 128;
		$living->teleport(Location::fromObject($position, $this->getGame()->getLevel(), $living->getYaw(), $living->getPitch()));
		if($living instanceof GamePlayer) {
			$living->sendMessage(TextFormat::YELLOW . "You have been teleported inside the border!");
		}
	}

	public function teleportPlayers(): void {
		if($this->getSize() <= self::RANDOM_TELEPORT_THRESHOLD) {
			$size = $this->getSize() * 0.98;
			foreach($this->getGame()->getManager()->getMembers() as $member) {
				if($member instanceof Team) {
					$outside = array_filter($member->getOnlinePlayers(), function (GamePlayer $player): bool { return !$this->inBorder($player); });
					if(count($outside) === count($member->getOnlinePlayers())) {
						$this->randomizedTeleport($member, $size);
					} else {
						foreach($outside as $player) $this->randomizedTeleport($player, $size);
					}
				}
				$this->randomizedTeleport($member, $size);
			}
			$disconnectedPlayers =  array_filter($this->getGame()->getDisconnectedManager()->getDisconnectedPlayers(), function (DisconnectedPlayer $player): bool { return $player->hasDisconnectedMob() && !$this->inBorder($player->getDisconnectedMob()); });
			foreach($disconnectedPlayers as $disconnectedPlayer) $this->randomizedTeleport($disconnectedPlayer->getDisconnectedMob(), $size);
		} else {
			$players = array_filter($this->getGame()->getManager()->getOnlinePlayers(), function (GamePlayer $player): bool { return !$this->inBorder($player); });
			foreach($players as $player) $this->teleport($player);

			$disconnectedPlayers =  array_filter($this->getGame()->getDisconnectedManager()->getDisconnectedPlayers(), function (DisconnectedPlayer $player): bool { return $player->hasDisconnectedMob() && !$this->inBorder($player->getDisconnectedMob()); });
			foreach($disconnectedPlayers as $disconnectedPlayer) $this->teleport($disconnectedPlayer->getDisconnectedMob());
		}
	}

	/**
	 * @param Team|GamePlayer|DisconnectedPlayerMob $member
	 * @param int $size
	 */
	public function randomizedTeleport($member, int $size): void {
		if($member === null || ($member instanceof GamePlayer && !$member->isOnline()) || ($member instanceof Living && !$member->isValid())) {
			return;
		}
		$randomizedX = mt_rand(-$size, $size);
		$randomizedZ = mt_rand(-$size, $size);
		$y = $this->getGame()->getLevel()->getHighestBlockAt($randomizedX, $randomizedZ);
		$member->teleport(new Vector3($randomizedX, $y + 1, $randomizedZ));
	}

	public function create(): void {
		new BorderTask($this);
	}

	/**
	 * @param int $x1
	 * @param int $x2
	 * @param int $z1
	 * @param int $z2
	 */
	public function createLayer(int $x1, int $x2, int $z1, int $z2): void {
		$minX = min($x1, $x2);
		$maxX = max($x1, $x2);

		$minZ = min($z1, $z2);
		$maxZ = max($z1, $z2);
		$level = $this->getGame()->getLevel();
		$this->getIterator()->currentChunk = $level->getChunk($minX >> 4, $minZ >> 4, true);
		for($x = $minX; $x <= $maxX; $x++) {
			for($z = $minZ; $z <= $maxZ; $z++) {
				$subX = $x & 0x0f;
				$subZ = $z & 0x0f;
				$y = $this->getIterator()->currentChunk->getHighestBlockAt($subX, $subZ);
				$this->getIterator()->moveTo($x, $y, $z);
				while($this->isPassable($this->getIterator()->currentChunk->getBlockId($subX, $y, $subZ)) && $y > 1) {
					$y -= 1;
				}
				$this->getIterator()->currentChunk->setBlockId($subX, $y + 1, $subZ, BlockIds::BEDROCK);
			}
		}
	}

	/**
	 * @param int $x1
	 * @param int $x2
	 * @param int $z1
	 * @param int $z2
	 */
	public function createWall(int $x1, int $x2, int $z1, int $z2): void {
		for($y = 0; $y < self::WALL_HEIGHT; $y++) {
			GameBase::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use($x1, $x2, $z1, $z2): void {
				$this->createLayer($x1, $x2, $z1, $z2);
			}), self::WALL_HEIGHT * 2);
		}
	}

	public function check(): void {
		if($this->canShrink()) {
			$borderTime = $this->getNextBorderTime();
			$next = $borderTime - $this->getGame()->getTime();
			$broadcastMatches = array_filter($this->broadcastTimes, function (int $broadcastTime) use($next): bool { return $next === $broadcastTime; });
			if(count($broadcastMatches) > 0) {
				$broadcastTime = $broadcastMatches[array_key_first($broadcastMatches)];
				$this->getGame()->broadcast(self::PREFIX . TextFormat::WHITE . " The border will shrink in " . TextFormat::YELLOW . $broadcastTime . TextFormat::WHITE . " seconds!");
			}
			if ($this->getGame()->getTime() === $borderTime) {
				$this->executeShrink();
			}
		}
	}

}