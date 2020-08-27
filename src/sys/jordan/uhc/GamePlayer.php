<?php


namespace sys\jordan\uhc;

use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\SourceInterface;
use pocketmine\utils\TextFormat;
use sys\jordan\core\CorePlayer;
use sys\jordan\core\utils\Scoreboard;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\GameStatus;
use sys\jordan\uhc\game\GameTrait;
use sys\jordan\uhc\game\team\invite\Invite;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\player\PlayerSettings;
use sys\jordan\uhc\player\PlayerStats;
use function microtime;

class GamePlayer extends CorePlayer {

	use GameTrait;

	/** @var int */
	public const MOVEMENT_CHECK_PERIOD = 2;

	/** @var int */
	private $lastCheck = -1;

	/** @var Invite[] */
	private $invites = [];

	/** @var Team */
	private $team = null;

	/** @var PlayerSettings */
	private $settings;

	/** @var PlayerStats */
	private $stats;

	/** @var Scoreboard */
	private $scoreboard;

	/** @var array */
	public $recentBlocks = [];

	/**
	 * GamePlayer constructor.
	 * @param SourceInterface $interface
	 * @param string $ip
	 * @param int $port
	 */
	public function __construct(SourceInterface $interface, string $ip, int $port) {
		parent::__construct($interface, $ip, $port);
		$this->scoreboard = new Scoreboard($this);
		$this->settings = new PlayerSettings;
		$this->stats = new PlayerStats;
	}

	/**
	 * @param LoginPacket $packet
	 * @return bool
	 */
	public function handleLogin(LoginPacket $packet): bool {
		$status = parent::handleLogin($packet);
		if($status) {
			if(($game = GameBase::getInstance()->getGameManager()->getGame($this)) instanceof Game) {
				if($game->getSettings()->isMobileOnly() && !$this->isMobile() && !$game->isHost($this) && !$game->isSpectator($this)) {
					$this->kick(TextFormat::RED . "You can't switch to a non-mobile device while playing a mobile-only game!", false, null);
					return true;
				}
				$this->setGame($game);
				$game->updateInstance($this);
			}
		}
		return $status;
	}

	public function doFirstSpawn(): void {
		parent::doFirstSpawn();
		$this->getStats()->load($this);
		$this->showCoordinates();
		$this->setImmediateRespawn();
		$this->setNameTag(TextFormat::WHITE . "{$this->getDisplayName()} " . TextFormat::YELLOW . "[{$this->getOSString()}]");
		$this->getScoreboard()->send(GameBase::SCOREBOARD_TITLE, Scoreboard::SLOT_SIDEBAR, Scoreboard::SORT_ASCENDING);
		if(!$this->inGame()) {
			GameBase::getInstance()->sendDefaultScoreboard($this);
		} else {
			$this->getGame()->getScoreboardManager()->send($this);
		}
	}

	public function processMostRecentMovements(): void {
		$now = microtime(true);
		if($now - $this->lastCheck > self::MOVEMENT_CHECK_PERIOD) {
			$this->lastCheck = $now;
			if($this->inGame() && $this->getGame()->getState() > GameStatus::COUNTDOWN) {
				if(!$this->getGame()->getBorder()->inBorder($this)) {
					$this->getGame()->getBorder()->teleport($this);
				}
			}
		}
		parent::processMostRecentMovements();
	}

	/**
	 * @return bool
	 */
	public function inGame(): bool {
		return $this->game instanceof Game;
	}

	public function removeFromGame(): void {
		$this->game = null;
	}

	/**
	 * @param GamePlayer $player
	 * @return bool
	 */
	public function hasInvite(GamePlayer $player): bool {
		return isset($this->invites[$player->getName()]);
	}

	/**
	 * @param GamePlayer $player
	 * @return Invite|null
	 */
	public function getInvite(GamePlayer $player): ?Invite {
		return $this->hasInvite($player) ? $this->invites[$player->getName()] : null;
	}

	/**
	 * @param GamePlayer $player
	 */
	public function addInvite(GamePlayer $player): void {
		if(!$this->hasInvite($player)) {
			$this->invites[$player->getName()] = new Invite($player, $this);
		}
	}

	/**
	 * @param GamePlayer $player
	 */
	public function removeInvite(GamePlayer $player): void {
		if($this->hasInvite($player)) {
			$invite = $this->getInvite($player);
			$invite->getTask()->cancel();
			unset($this->invites[$player->getName()]);
		}
	}

	/**
	 * @return Team|null
	 */
	public function getTeam(): ?Team {
		return $this->team;
	}

	/**
	 * @return bool
	 */
	public function inTeam(): bool {
		return $this->getTeam() instanceof Team;
	}

	/**
	 * @param Team|null $team
	 */
	public function setTeam(?Team $team = null): void {
		$this->team = $team;
	}

	/**
	 * @return PlayerSettings
	 */
	public function getSettings(): PlayerSettings {
		return $this->settings;
	}

	/**
	 * @return PlayerStats
	 */
	public function getStats(): PlayerStats {
		return $this->stats;
	}

	/**
	 * @param bool $value
	 */
	public function showCoordinates(bool $value = true): void {
		$pk = new GameRulesChangedPacket;
		$pk->gameRules = ["showCoordinates" => [1, $value]];
		$this->directDataPacket($pk);
		$this->getSettings()->setCoordinatesEnabled($value);
	}

	/**
	 * @param bool $value
	 */
	public function setImmediateRespawn(bool $value = true): void {
		$pk = new GameRulesChangedPacket;
		$pk->gameRules = ["immediaterespawn" => [1, $value]];
		$this->dataPacket($pk);
	}

	public function giveCountdownEffects(): void {
		$this->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20 * 3, 1));
		$this->setImmobile();
	}

	public function reset(): void {
		if (!$this->isSurvival()) $this->setGamemode(GamePlayer::SURVIVAL);
		$this->setRegeneration($this->inGame() ? !$this->getGame()->hasStarted() : true);
		$this->setHealth($this->getMaxHealth());
		$this->setFood($this->getMaxFood());
		$this->setSaturation(Attribute::getAttribute(Attribute::SATURATION)->getMaxValue());
		$this->setExhaustion(0);
		$this->setXpAndProgress(0, 0);
		$this->removeAllEffects();
		$this->setImmobile(false);
		$this->getInventory()->clearAll();
		$this->getArmorInventory()->clearAll();
		$this->getCursorInventory()->clearAll();
	}

}