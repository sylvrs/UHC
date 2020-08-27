<?php


namespace sys\jordan\uhc\player;


use Exception;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\GamePlayer;

class DisconnectedPlayer {

	use GameTrait;

	/** @var DisconnectedPlayerMob */
	private $disconnectedMob;

	/** @var string */
	private $name;

	/** @var float */
	private $health;

	/** @var Location */
	private $location;

	/** @var Item[] */
	private $armorContents;

	/** @var Item[] */
	private $contents;

	/** @var int */
	private $expirationTime;

	/**
	 * DisconnectedPlayer constructor.
	 * @param Game $game
	 * @param string $name
	 * @param float $health
	 * @param Location $location
	 * @param array $armorContents
	 * @param array $itemContents
	 * @param int $expirationTime
	 */
	public function __construct(Game $game, string $name, float $health, Location $location, array $armorContents, array $itemContents, int $expirationTime) {
		$this->setGame($game);
		$this->name = $name;
		$this->location = $location;
		$this->armorContents = $armorContents;
		$this->contents = $itemContents;
		$this->expirationTime = $expirationTime;
		$this->health = $health;
		if($this->getGame()->getState() > GameStatus::COUNTDOWN) {
			$this->setupDisconnectedMob();
		}
	}

	public function setupDisconnectedMob(): void {
		$this->disconnectedMob = DisconnectedPlayerMob::createEntity("disconnectedPlayer", $this->getLocation()->getLevel(), DisconnectedPlayerMob::createBaseNBT($this->getLocation()->asPosition()));
		$this->disconnectedMob->setDisconnectedPlayer($this);
		$this->disconnectedMob->setHealth($this->getHealth());
		$this->disconnectedMob->setNameTag(TextFormat::WHITE . $this->getName() . TextFormat::YELLOW . " (AFK)");
		$this->disconnectedMob->setNameTagVisible();
		$this->disconnectedMob->setNameTagAlwaysVisible();
		$this->disconnectedMob->getArmorInventory()->setContents($this->getArmorContents());
		$this->disconnectedMob->spawnToAll();
	}

	public function __destruct() {
		foreach($this as $key => $value) unset($this->$key);
	}

	/**
	 * @return Game
	 */
	public function getGame(): Game {
		return $this->game;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return float
	 */
	public function getHealth(): float {
		return $this->health;
	}

	/**
	 * @return Location
	 */
	public function getLocation(): Location {
		return $this->location;
	}

	/**
	 * @return Item[]
	 */
	public function getArmorContents(): array {
		return $this->armorContents;
	}

	/**
	 * @param Item[] $armorContents
	 */
	public function setArmorContents(array $armorContents): void {
		$this->armorContents = $armorContents;
	}

	/**
	 * @return Item[]
	 */
	public function getContents(): array {
		return $this->contents;
	}

	/**
	 * @param Item[] $contents
	 */
	public function setContents(array $contents): void {
		$this->contents = $contents;
	}

	/**
	 * @return int
	 */
	public function getExpirationTime(): int {
		return $this->expirationTime;
	}

	/**
	 * @return bool
	 */
	public function hasExpired(): bool {
		return microtime(true) >= $this->getExpirationTime();
	}


	/**
	 * @return DisconnectedPlayerMob
	 */
	public function getDisconnectedMob(): DisconnectedPlayerMob {
		return $this->disconnectedMob;
	}

	/**
	 * @return bool
	 */
	public function hasDisconnectedMob(): bool {
		return $this->disconnectedMob instanceof DisconnectedPlayerMob;
	}

	/**
	 * @param GamePlayer $player
	 * @param int $expirationLength
	 * @return static
	 */
	public static function create(GamePlayer $player, int $expirationLength): self {
		return new self($player->getGame(), $player->getName(), $player->getHealth(), $player->asLocation(), $player->getArmorInventory()->getContents(), $player->getInventory()->getContents(), microtime(true) + $expirationLength);
	}

	public function kill(): void {
		if($this->hasDisconnectedMob()) {
			try {
				$this->getDisconnectedMob()->kill();
			} catch(Exception $exception) {
				$this->getGame()->getLogger()->logException($exception);
			}
		} else {
			$this->getGame()->broadcast(TextFormat::YELLOW . "{$this->getName()} " . TextFormat::YELLOW . "(AFK) " . TextFormat::WHITE . "has died!");
			foreach(($this->getArmorContents() + $this->getContents()) as $item) {
				$this->getLocation()->getLevel()->dropItem($this->getLocation(), $item);
			}
			$this->getGame()->getManager()->addDead($this->getName());
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function remove(GamePlayer $player): void {
		if($this->hasDisconnectedMob()) {
			$player->setHealth($this->getDisconnectedMob()->getHealth());
			try {
				$player->teleport($this->getDisconnectedMob()->asLocation());
			} catch (AssumptionFailedError $exception) {
				// ignore for now
			}
			$this->setContents([]);
			$this->setArmorContents([]);
			if(!$this->getDisconnectedMob()->isClosed()) {
				$this->getDisconnectedMob()->flagForDespawn();
			} else {
				$this->getGame()->getLogger()->warning("Entity for {$player->getName()} was closed before it could be despawned.");
			}
		}
	}

}