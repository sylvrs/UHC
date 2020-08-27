<?php


namespace sys\jordan\uhc\game\member;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\event\GameWinEvent;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use sys\jordan\uhc\utils\UHCUtilities;
use function array_filter;
use function array_search;
use function count;
use function is_array;
use function shuffle;

class TeamManager extends MemberManager {

	/** @var int */
	public static $TEAM_COUNT = 0;

	/** @var GamePlayer[] */
	private $solos = [];

	/** @var Team[] */
	private $teams = [];

	/** @var int */
	private $maxPlayerCount = 2;

	/** @var bool */
	private $teamCreationAllowed = true;

	/**
	 * @return int
	 */
	public function getMaxPlayerCount(): int {
		return $this->maxPlayerCount;
	}

	/**
	 * @param int $maxPlayerCount
	 */
	public function setMaxPlayerCount(int $maxPlayerCount): void {
		$this->maxPlayerCount = $maxPlayerCount;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function addSolo(GamePlayer $player): bool {
		if(!$this->isSolo($player)) {
			$this->solos[$player->getLowerCaseName()] = $player;
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function isSolo($player): bool {
		return isset($this->solos[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function removeSolo($player): bool {
		if($this->isSolo($player)) {
			unset($this->solos[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
			return true;
		}
		return false;
	}

	/**
	 * @return GamePlayer[]
	 */
	public function getSolos(): array {
		return $this->solos;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function addPlayer(GamePlayer $player): bool {
		if(!$this->isPlayer($player)) {
			if(!$this->getGame()->hasStarted()) {
				$this->solos[$player->getLowerCaseName()] = $player;
			} else {
				$this->createTeam([$player]);
			}
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function isPlayer($player): bool {
		foreach ($this->getTeams() as $team) {
			if($team->isPlayer($player)) {
				return true;
			}
		}
		return $this->isSolo($player);
	}

	/**
	 * @param GamePlayer|string $player
	 * @return bool
	 */
	public function removePlayer($player): bool {
		foreach ($this->getTeams() as $team) {
			if($team->isPlayer($player)) {
				$team->removePlayer($player);
				$this->check();
				return true;
			}
		}
		return $this->removeSolo($player);
	}

	/**
	 * @inheritDoc
	 */
	public function getPlayers(): array {
		$players = [];
		foreach($this->getTeams() as $team) {
			$players = $players + $team->getPlayers();
		}
		return ($players + $this->getSolos());
	}

	/**
	 * @param Team $team
	 * @return bool
	 */
	public function addTeam(Team $team): bool {
		if(!$this->isTeam($team)) {
			$this->teams[$team->getId()] = $team;
			return true;
		}
		return false;
	}

	/**
	 * @param Team $team
	 * @return bool
	 */
	public function isTeam(Team $team): bool {
		return isset($this->teams[$team->getId()]);
	}

	/**
	 * @param Team $team
	 * @return bool
	 */
	public function removeTeam(Team $team): bool {
		if($this->isTeam($team)) {
			unset($this->teams[$team->getId()]);
			self::$TEAM_COUNT--;
			return true;
		}
		return false;
	}

	/**
	 * @param GamePlayer[] $players
	 * @return Team
	 */
	public function createTeam(array $players): Team {
		$team = new Team(++self::$TEAM_COUNT);
		foreach($players as $player) {
			$team->addPlayer($player);
			if($this->isSolo($player)) {
				$this->removeSolo($player);
			}
		}
		$this->addTeam($team);
		return $team;
	}

	/**
	 * @return Team[]
	 */
	public function getTeams(): array {
		return $this->teams;
	}



	public function getScoreboardData(): array {
		return [
			TextFormat::WHITE . "Teams: " . TextFormat::YELLOW . $this->getCount(),
			TextFormat::WHITE . "Players: " . TextFormat::YELLOW . count($this->getAlive())
		];
	}

	public function createTeams(): void {
		foreach($this->getSolos() as $player) {
			$this->createTeam([$player]);
			$this->removeSolo($player);
		}
	}

	/**
	 * Legacy code that will stay in
	 * if we decide to add randomized teams back
	 *
	 * @deprecated Creates teams from solo players
	 *
	 */
	private function calculateTeams(): void {
		$this->getGame()->broadcast(TextFormat::YELLOW . "Creating teams...");
		if(!$this->getGame()->getSettings()->isMobileOnly()) {
			if(count($this->getSolos()) > 0) {
				$mobile = array_filter($this->getSolos(), function (GamePlayer $player) {
					return $player->isMobile();
				});
				shuffle($mobile);
				$nonmobile = array_filter($this->getSolos(), function (GamePlayer $player) {
					return !$player->isMobile();
				});
				shuffle($nonmobile);
				while(($players = UHCUtilities::getRandom($mobile, $this->getMaxPlayerCount() - (count($nonmobile) > 0 ? 1 : 0))) !== false) {
					if(!is_array($players)) $players = [$players];
					foreach($players as $player) {
						$key = array_search($player, $mobile, true);
						if($key !== false) {
							unset($mobile[$key]);
						}
					}
					if(count($players) < $this->getMaxPlayerCount()) {
						$player = UHCUtilities::getRandom($nonmobile, 1);
						if($player !== false) {
							$players[] = $player;
							$key = array_search($player, $nonmobile, true);
							if($key !== false) {
								unset($nonmobile[$key]);
							}
						}
					}
					foreach($players as $player) $this->removeSolo($player);
					$this->createTeam($players);
				}
				foreach($this->getSolos() as $player) {
					$this->createTeam([$player]);
					$this->removeSolo($player);
					$player->sendMessage(TextFormat::YELLOW . "You have been made into a team!");
				}
			}
		} else {
			while(($players = UHCUtilities::getRandom($this->getSolos(), $this->getMaxPlayerCount())) !== false) {
				if(!is_array($players)) $players = [$players];
				foreach($players as $player) {
					$this->removeSolo($player);
				}
				$this->createTeam($players);
			}
			foreach($this->getSolos() as $player) {
				$this->createTeam([$player]);
				$this->removeSolo($player);
			}
		}
	}

	public function disbandTeams(): void {
		foreach($this->getTeams() as $team) {
			$this->disbandTeam($team);
		}
	}

	/**
	 * @param Team $team
	 * @param bool $isAdmin
	 */
	public function disbandTeam(Team $team, bool $isAdmin = true): void {
		$team->broadcast(TextFormat::YELLOW . ($isAdmin ? "An admin has disbanded this team!" : "Your team has been disbanded!"));
		foreach($team->getPlayers() as $player) {
			$team->removePlayer($player);
			if(!$this->getGame()->hasStarted()) {
				$this->addSolo($player);
			}
		}
		$this->removeTeam($team);
	}

	public function setup(): void {
		$this->createTeams();
		$this->calculateScatterPositions();
	}

	public function stop(): void {

	}

	/**
	 * @inheritDoc
	 */
	public function updateInstance(GamePlayer $player): void {
		foreach($this->getTeams() as $team) {
			if($team->isPlayer($player)) {
				$team->updateInstance($player);
				return;
			}
		}
		if($this->isSolo($player)) {
			$this->solos[$player->getLowerCaseName()] = $player;
		}
	}

	/**
	 * @return Team[]
	 */
	public function getRemaining(): array {
		$teams = [];
		foreach($this->getAlive() as $player) {
			if($player->inTeam()) {
				$team = $player->getTeam();
				if(!isset($teams[$team->getId()])) {
					$teams[$player->getTeam()->getId()] = $team;
				}
			}
		}
		return $teams;
	}

	public function check(): void {
		if($this->getGame()->hasStarted() && count($this->getRemaining()) === 1) {
			$team = $this->getRemaining()[array_key_first($this->getRemaining())];
			foreach($team->getPlayers() as $player) {
				if($player instanceof GamePlayer) {
					(new GameWinEvent($player))->call();
				}
			}
			$this->getGame()->broadcast(TextFormat::GREEN . "Team {$team->getId()}" . TextFormat::GREEN . " won the game!");
			$team->broadcast(TextFormat::GREEN . "You have won the game!");
			$this->getGame()->broadcastTitle(TextFormat::GREEN . "Team {$team->getId()}" . TextFormat::GREEN . " has won the game!");
			$this->getGame()->setState(GameStatus::POSTGAME);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		parent::handleDamage($event);
		$player = $event->getEntity();
		if($player instanceof DisconnectedPlayerMob) {
			$player = $player->getDisconnectedPlayer()->getName();
		}
		if($event instanceof EntityDamageByEntityEvent) {
			/** @var GamePlayer $damager */
			$damager = $event->getDamager();
			if($damager->inTeam() && $damager->getTeam()->isPlayer($player)) {
				$event->setCancelled();
			}
		}
	}

	/**
	 * @return Team[]
	 */
	public function getMembers(): array {
		return $this->teams;
	}
}