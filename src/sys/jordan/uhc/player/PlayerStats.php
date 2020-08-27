<?php


namespace sys\jordan\uhc\player;

use pocketmine\utils\Config;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\UHCUtilities;

class PlayerStats {

	/** @var Config */
	private $file;

	/** @var string */
	public const FOLDER_NAME = "players/";

	/** @var int */
	private $kills = 0;
	/** @var int */
	private $deaths = 0;
	/** @var int */
	private $wins = 0;
	/** @var int */
	private $losses = 0;
	/** @var int */
	private $timePlayed = 0.0;
	/** @var int */
	private $emeraldsMined = 0;
	/** @var int */
	private $diamondsMined = 0;
	/** @var int */
	private $goldMined = 0;
	/** @var int */
	private $lapisMined = 0;
	/** @var int */
	private $redstoneMined = 0;
	/** @var int */
	private $ironMined = 0;
	/** @var int */
	private $coalMined = 0;
	/** @var float */
	private $heartsHealed = 0.0;
	/** @var float */
	private $damageDealt = 0.0;
	/** @var float */
	private $damageTaken = 0.0;
	/** @var float */
	private $fallDamage = 0.0;
	/** @var int */
	private $goldenApples = 0;
	/** @var int */
	private $goldenHeads = 0;

	/**
	 * @param GamePlayer|string $player
	 */
	public function load($player): void {
		if($player instanceof GamePlayer) $player = $player->getName();
		if(!file_exists(GameBase::getInstance()->getDataFolder() . self::FOLDER_NAME)) mkdir(GameBase::getInstance()->getDataFolder() . self::FOLDER_NAME, 0777, true);
		$this->setFile(new Config(GameBase::getInstance()->getDataFolder() . self::FOLDER_NAME . $player . ".stats", Config::JSON));
		foreach($this->getFile()->getAll() as $key => $value) {
			if(isset($this->$key) && $key !== "file") {
				$this->$key = $value;
			}
		}
	}

	public function save(): void {
		if(!$this->file instanceof Config) {
			return;
		}
		$data = [];
		foreach($this as $key => $value) {
			if(is_int($value) || is_float($value)) {
				$data[$key] = $value;
			}
		}
		$this->getFile()->setAll($data);
		$this->getFile()->save();
	}

	/**
	 * @return Config
	 */
	public function getFile(): Config {
		return $this->file;
	}

	/**
	 * @param Config $file
	 */
	public function setFile(Config $file): void {
		$this->file = $file;
	}

	/**
	 * @return int
	 */
	public function getWins(): int {
		return $this->wins;
	}

	/**
	 * @param int $wins
	 */
	public function setWins(int $wins): void {
		$this->wins = $wins;
	}

	public function addWin(): void {
		$this->wins += 1;
	}

	/**
	 * @return int
	 */
	public function getLosses(): int {
		return $this->losses;
	}

	/**
	 * @param int $losses
	 */
	public function setLosses(int $losses): void {
		$this->losses = $losses;
	}

	public function addLoss(): void {
		$this->losses += 1;
	}

	/**
	 * @return int
	 */
	public function getTimePlayed(): int {
		return $this->timePlayed;
	}

	/**
	 * @return string
	 */
	public function getFormattedTimePlayed(): string {
		$seconds = $this->getTimePlayed();
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds / 60) % 60);
		$seconds = $seconds % 60;
		return ($hours < 10 ? "0" : "") . $hours . ":" . ($minutes < 10 ? "0" : "") . $minutes . ($seconds < 10 ? "0" : "") . ":" . $seconds;
	}

	/**
	 * @param int $timePlayed
	 */
	public function setTimePlayed(int $timePlayed): void {
		$this->timePlayed = $timePlayed;
	}

	/**
	 * @param int $timePlayed
	 */
	public function addTimePlayed(int $timePlayed): void {
		$this->timePlayed += $timePlayed;
	}

	/**
	 * @return int
	 */
	public function getKills(): int {
		return $this->kills;
	}

	/**
	 * @param int $kills
	 */
	public function setKills(int $kills): void {
		$this->kills = $kills;
	}

	public function addKill(): void {
		$this->kills += 1;
	}

	/**
	 * @return int
	 */
	public function getDeaths(): int {
		return $this->deaths;
	}

	/**
	 * @param int $deaths
	 */
	public function setDeaths(int $deaths): void {
		$this->deaths = $deaths;
	}

	public function addDeath(): void {
		$this->deaths += 1;
	}

	/**
	 * @return float
	 */
	public function getKDR(): float {
		return $this->getKills() / max(1, $this->getDeaths());
	}

	/**
	 * @return int
	 */
	public function getEmeraldsMined(): int {
		return $this->emeraldsMined;
	}

	/**
	 * @param int $emeraldsMined
	 */
	public function setEmeraldsMined(int $emeraldsMined): void {
		$this->emeraldsMined = $emeraldsMined;
	}

	public function addEmeraldsMined(): void {
		$this->emeraldsMined += 1;
	}

	/**
	 * @return int
	 */
	public function getDiamondsMined(): int {
		return $this->diamondsMined;
	}

	/**
	 * @param int $diamondsMined
	 */
	public function setDiamondsMined(int $diamondsMined): void {
		$this->diamondsMined = $diamondsMined;
	}

	public function addDiamondsMined(): void {
		$this->diamondsMined += 1;
	}

	/**
	 * @return int
	 */
	public function getGoldMined(): int {
		return $this->goldMined;
	}

	/**
	 * @param int $goldMined
	 */
	public function setGoldMined(int $goldMined): void {
		$this->goldMined = $goldMined;
	}

	public function addGoldMined(): void {
		$this->goldMined += 1;
	}

	/**
	 * @return int
	 */
	public function getLapisMined(): int {
		return $this->lapisMined;
	}

	/**
	 * @param int $lapisMined
	 */
	public function setLapisMined(int $lapisMined): void {
		$this->lapisMined = $lapisMined;
	}

	public function addLapisMined(): void {
		$this->lapisMined += 1;
	}

	/**
	 * @return int
	 */
	public function getRedstoneMined(): int {
		return $this->redstoneMined;
	}

	/**
	 * @param int $redstoneMined
	 */
	public function setRedstoneMined(int $redstoneMined): void {
		$this->redstoneMined = $redstoneMined;
	}

	public function addRedstoneMined(): void {
		$this->redstoneMined += 1;
	}

	/**
	 * @return int
	 */
	public function getIronMined(): int {
		return $this->ironMined;
	}

	/**
	 * @param int $ironMined
	 */
	public function setIronMined(int $ironMined): void {
		$this->ironMined = $ironMined;
	}

	public function addIronMined(): void {
		$this->ironMined += 1;
	}

	/**
	 * @return int
	 */
	public function getCoalMined(): int {
		return $this->coalMined;
	}

	/**
	 * @param int $coalMined
	 */
	public function setCoalMined(int $coalMined): void {
		$this->coalMined = $coalMined;
	}

	public function addCoalMined(): void {
		$this->coalMined += 1;
	}

	/**
	 * @return float
	 */
	public function getHeartsHealed(): float {
		return $this->heartsHealed;
	}

	/**
	 * @param float $heartsHealed
	 */
	public function setHeartsHealed(float $heartsHealed): void {
		$this->heartsHealed = UHCUtilities::roundIfNeeded($heartsHealed);
	}

	/**
	 * @param float $heartsHealed
	 */
	public function addHeartsHealed(float $heartsHealed): void {
		$this->setHeartsHealed($this->getHeartsHealed() + $heartsHealed);
	}

	/**
	 * @return float
	 */
	public function getDamageDealt(): float {
		return $this->damageDealt;
	}

	/**
	 * @param float $damageDealt
	 */
	public function setDamageDealt(float $damageDealt): void {
		$this->damageDealt = UHCUtilities::roundIfNeeded($damageDealt);
	}

	/**
	 * @param float $damageDealt
	 */
	public function addDamageDealt(float $damageDealt): void {
		$this->setDamageDealt($this->getDamageDealt() + $damageDealt);
	}

	/**
	 * @return float
	 */
	public function getDamageTaken(): float {
		return $this->damageTaken;
	}

	/**
	 * @param float $damageTaken
	 */
	public function setDamageTaken(float $damageTaken): void {
		$this->damageTaken = UHCUtilities::roundIfNeeded($damageTaken);
	}

	/**
	 * @param float $damageTaken
	 */
	public function addDamageTaken(float $damageTaken): void {
		$this->setDamageTaken($this->getDamageTaken() + $damageTaken);
	}

	/**
	 * @return float
	 */
	public function getFallDamage(): float {
		return $this->fallDamage;
	}

	/**
	 * @param float $fallDamage
	 */
	public function setFallDamage(float $fallDamage): void {
		$this->fallDamage = UHCUtilities::roundIfNeeded($fallDamage);
	}

	/**
	 * @param float $fallDamage
	 */
	public function addFallDamage(float $fallDamage): void {
		$this->fallDamage += $fallDamage;
	}

	/**
	 * @return int
	 */
	public function getGoldenApples(): int {
		return $this->goldenApples;
	}

	/**
	 * @param int $goldenApples
	 */
	public function setGoldenApples(int $goldenApples): void {
		$this->goldenApples = $goldenApples;
	}

	public function addGoldenApple(): void {
		$this->goldenApples += 1;
	}

	/**
	 * @return int
	 */
	public function getGoldenHeads(): int {
		return $this->goldenHeads;
	}

	/**
	 * @param int $goldenHeads
	 */
	public function setGoldenHeads(int $goldenHeads): void {
		$this->goldenHeads = $goldenHeads;
	}

	public function addGoldenHead(): void {
		$this->goldenHeads += 1;
	}

}