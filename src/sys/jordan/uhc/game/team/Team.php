<?php


namespace sys\jordan\uhc\game\team;


use pocketmine\math\Vector3;
use sys\jordan\core\chat\ChatChannel;
use sys\jordan\uhc\game\member\TeamManager;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\UHCUtilities;

class Team {

	/** @var string */
	private $format = "";

	/** @var int */
	private $id;
	/** @var GamePlayer[] */
	private $players = [];

	/** @var ChatChannel */
	private $chatChannel;

	/**
	 * Team constructor.
	 * @param int $id
	 */
	public function __construct(int $id) {
		$this->id = $id;
		$this->format = UHCUtilities::getRandomColor() . "[Team $id]";
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return "Team #{$this->getId()}";
	}

	/**
	 * @return string
	 */
	public function getFormat(): string {
		return $this->format;
	}

	/**
	 * @param string $format
	 */
	public function setFormat(string $format): void {
		$this->format = $format;
	}

	/**
	 * @return ChatChannel
	 */
	public function getChatChannel(): ChatChannel {
		return $this->chatChannel;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function addPlayer(GamePlayer $player): bool {
		if(!$this->isPlayer($player)) {
			$this->players[$player->getLowerCaseName()] = $player;
			$player->setTeam($this);
			return true;
		}
		return false;
	}

	/**
	 * @param string $playerName
	 * @return bool
	 */
	public function addPlayerByName(string $playerName): bool {
		if(!$this->isPlayer($playerName)) {
			$this->players[strtolower($playerName)] = true;
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function isPlayer($player): bool {
		return isset($this->players[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function removePlayer($player): bool {
		if($this->isPlayer($player)) {
			unset($this->players[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
			if($player instanceof GamePlayer) {
				$player->setTeam(null);
			}
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function updateInstance(GamePlayer $player): void {
		$this->players[$player->getLowerCaseName()] = $player;
		$player->setTeam($this);
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getPlayers(): array {
		return $this->players;
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getOnlinePlayers(): array {
		return array_filter($this->getPlayers(), function (GamePlayer $player): bool {
			return $player->isOnline();
		});
	}

	/**
	 * @param string $message
	 */
	public function broadcast(string $message): void {
		foreach($this->getPlayers() as $player) {
			$player->sendMessage($message);
		}
	}

	/**
	 * @param Vector3 $position
	 * @param float|null $yaw
	 * @param float|null $pitch
	 */
	public function teleport(Vector3 $position, float $yaw = null, float $pitch = null): void {
		foreach($this->getOnlinePlayers() as $player) {
			$player->teleport($position);
		}
	}

	public function reset(): void {
		foreach($this->getOnlinePlayers() as $player) {
			$player->reset();
		}
	}

	public function giveCountdownEffects(): void {
		foreach($this->getOnlinePlayers() as $player) {
			$player->giveCountdownEffects();
		}
	}

	/**
	 * @return int
	 */
	public function getEliminations(): int {
		return array_reduce($this->getPlayers(), function (int $accumulation, GamePlayer $player): int {
			return $accumulation + $player->getGame()->getEliminationsManager()->getEliminations($player);
		}, 0);
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function isAllowed(GamePlayer $player): bool {
		/** @var TeamManager $manager */
		$manager = $player->getGame()->getManager();
		return $player->isMobile() || (!$player->isMobile() && count(array_filter($this->getPlayers(), function (GamePlayer $teammate): bool {
			return !$teammate->isMobile();
		})) < ceil($manager->getMaxPlayerCount() / 2));
	}

}