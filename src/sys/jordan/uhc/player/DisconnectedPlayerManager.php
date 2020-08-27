<?php


namespace sys\jordan\uhc\player;


use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\game\GameValues;
use sys\jordan\uhc\GamePlayer;

class DisconnectedPlayerManager {

	use GameTrait;

	/** @var DisconnectedPlayer[] */
	private $disconnected = [];

	/**
	 * DisconnectedPlayerManager constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @return DisconnectedPlayer[]
	 */
	public function getDisconnectedPlayers(): array {
		return $this->disconnected;
	}

	/**
	 * @param DisconnectedPlayer $player
	 */
	public function addDisconnected(DisconnectedPlayer $player): void {
		$this->disconnected[strtolower($player->getName())] = $player;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function createDisconnected(GamePlayer $player): void {
		$this->addDisconnected(DisconnectedPlayer::create($player, GameValues::$DISCONNECTION_LENGTH));
	}

	/**
	 * @param string $name
	 * @return DisconnectedPlayer|null
	 */
	public function getDisconnected(string $name): ?DisconnectedPlayer {
		return $this->disconnected[strtolower($name)] ?? null;
	}


	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function isDisconnected($player): bool {
		return isset($this->disconnected[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
	}

	/**
	 * @param GamePlayer|string $player
	 */
	public function removeDisconnected($player): void {
		if($this->isDisconnected($player)) {
			unset($this->disconnected[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function join(GamePlayer $player): void {
		if($this->isDisconnected($player)) {
			$disconnected = $this->getDisconnected($player->getName());
			$disconnected->remove($player);
			$this->removeDisconnected($player);
		}
	}

	public function check(): void {
		foreach($this->getDisconnectedPlayers() as $disconnectedPlayer) {
			if($disconnectedPlayer->hasExpired()) {
				$disconnectedPlayer->kill();
				$this->removeDisconnected($disconnectedPlayer->getName());
			}
		}
	}

}