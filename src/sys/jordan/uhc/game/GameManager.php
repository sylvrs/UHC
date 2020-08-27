<?php


namespace sys\jordan\uhc\game;

use Exception;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use sys\jordan\uhc\border\BorderValues;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\GameBaseTrait;

class GameManager {

	use GameBaseTrait;

	/** @var Game */
	private $defaultGame = null;

	/** @var Game[] */
	private $games = [];

	/** @var array */
	private $savedGames = [];

	/**
	 * GameManager constructor.
	 * @param GameBase $plugin
	 */
	public function __construct(GameBase $plugin) {
		$this->setPlugin($plugin);
		new GameListener($plugin);
		BorderValues::load($plugin);
		GameValues::load($plugin);
	}

	public function load(): void {
		$file = new Config($this->getPlugin()->getDataFolder() . "games.json");
		foreach($file->getAll() as $data) {
			$this->savedGames[] = $data;
		}
	}

	/**
	 * @return Game|null
	 */
	public function getDefaultGame(): ?Game {
		return $this->defaultGame;
	}

	/**
	 * @param Game $defaultGame
	 */
	public function setDefaultGame(Game $defaultGame): void {
		$this->defaultGame = $defaultGame;
	}

	/**
	 * @param Game $compare
	 * @return bool
	 */
	public function isDefaultGame(Game $compare): bool {
		return $this->defaultGame === $compare;
	}

	/**
	 * @return Game[]
	 */
	public function getGames(): array {
		return $this->games;
	}

	/**
	 * @param Level $level
	 * @param GamePlayer $host
	 * @param int $size
	 * @param bool $createBorder
	 * @param bool $teams
	 * @return bool
	 * @throws Exception
	 */
	public function createGame(Level $level, GamePlayer $host, int $size, bool $createBorder, bool $teams): bool {
		if(!$this->hasGame($level)) {
			$this->addGame(new Game($this->getPlugin(), $level, $host, $size, $createBorder, $teams), true);
			return true;
		}
		return false;
	}

	/**
	 * @param Game $game
	 * @param bool $default
	 */
	public function addGame(Game $game, bool $default = false): void {
		$this->games[$game->getLevel()->getFolderName()] = $game;
		if($default) $this->setDefaultGame($game);
	}

	/**
	 * @param Game $game
	 */
	public function removeGame(Game $game): void {
		unset($this->games[$game->getLevel()->getFolderName()]);
	}

	/**
	 * @param Level $level
	 * @return bool
	 */
	public function hasGame(Level $level): bool {
		return isset($this->games[$level->getFolderName()]);
	}

	/**
	 * @param Level $level
	 * @return Game|null
	 */
	public function getGameByLevel(Level $level): ?Game {
		return $this->hasGame($level) ? $this->games[$level->getFolderName()] : null;
	}

	/**
	 * @param GamePlayer $player
	 * @return Game|null
	 */
	public function getGame(GamePlayer $player): ?Game {
		foreach($this->getGames() as $game) {
			if($game->getManager()->isPlayer($player) || $game->isHost($player)) {
				return $game;
			}
		}
		return null;
	}

}