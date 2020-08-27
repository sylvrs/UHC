<?php


namespace sys\jordan\uhc\game\member;


use pocketmine\utils\TextFormat;
use sys\jordan\uhc\event\GameWinEvent;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\GamePlayer;

class PlayerManager extends MemberManager {

	/** @var GamePlayer[] */
	private $players = [];

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function addPlayer(GamePlayer $player): bool {
		if(!$this->isPlayer($player)) {
			$this->players[$player->getLowerCaseName()] = $player;
			return true;
		}
		return false;
	}

	/**
	 * @param string|GamePlayer $player
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
			$this->check();
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function updateInstance(GamePlayer $player): void {
		if($this->isPlayer($player)) {
			$this->players[$player->getLowerCaseName()] = $player;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPlayers(): array {
		return $this->players;
	}

	public function getMembers(): array {
		return $this->players;
	}

	/**
	 * @param GamePlayer $member
	 * @param bool $giveEffects
	 * @param bool $isRespawn
	 * @param callable|null $done
	 */
	public function scatter($member, bool $giveEffects = true, bool $isRespawn = false, callable $done = null): void {
		if($member === null || !$member->isValid() || !$member->isOnline()) {
			return;
		}
		parent::scatter($member, $giveEffects, $isRespawn, $done);
	}

	/**
	 * @return array
	 */
	public function getScoreboardData(): array {
		return [
			TextFormat::WHITE . "Players: " . TextFormat::YELLOW . $this->getCount()
		];
	}

	public function setup(): void {
		$this->calculateScatterPositions();
	}

	public function stop(): void {

	}

	public function check(): void {
		if($this->getGame()->hasStarted() && $this->getCount() === 1) {
			$winner = $this->getAlive()[array_key_first($this->getAlive())];
			(new GameWinEvent($winner))->call();
			$this->getGame()->broadcast(TextFormat::GREEN . "{$winner->getName()} won the game!");
			$winner->sendMessage(TextFormat::GREEN . "You have won the game!");
			$this->getGame()->broadcastTitle(TextFormat::GREEN . "{$winner->getName()} wins!");
			$this->getGame()->setState(GameStatus::POSTGAME);
		}
	}

	/**
	 * @return array
	 */
	public function getRemaining(): array {
		return $this->getAlive();
	}
}