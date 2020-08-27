<?php


namespace sys\jordan\uhc\player;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemIds;
use pocketmine\tile\Skull;
use sys\jordan\uhc\base\BaseListener;
use sys\jordan\uhc\event\GameWinEvent;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\UHCUtilities;

class PlayerStatListener extends BaseListener {

	/**
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getStats()->addTimePlayed($player->getSessionTime());
		}
		$player->getStats()->save();
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority MONITOR
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->getManager()->isPlayer($player)) {
			$cause = $player->getLastDamageCause();
			if($cause instanceof EntityDamageByEntityEvent) {
				$damager = $cause->getDamager();
				if($damager instanceof GamePlayer) {
					$damager->getStats()->addKill();
				}
			}
			$player->getStats()->addDeath();
			$player->getStats()->addLoss();
		}
	}

	/**
	 * @param GameWinEvent $event
	 * @priority MONITOR
	 */
	public function handleWin(GameWinEvent $event): void {
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->getManager()->isPlayer($player)) {
			$event->getPlayer()->getStats()->addWin();
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority MONITOR
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame() && $player->getGame()->getManager()->isPlayer($player) && !$player->getGame()->getManager()->isDead($player)) {
			if (!$event->isCancelled()) {
				if(UHCUtilities::isOre($event->getBlock()->getId())) {
					/** @var GamePlayer $player */
					$player = $event->getPlayer();
					switch($event->getBlock()->getId()) {
						case BlockIds::EMERALD_ORE:
							$player->getStats()->addEmeraldsMined();
							break;
						case BlockIds::DIAMOND_ORE:
							$player->getStats()->addDiamondsMined();
							break;
						case BlockIds::GOLD_ORE:
							$player->getStats()->addGoldMined();
							break;
						case BlockIds::LAPIS_ORE:
							$player->getStats()->addLapisMined();
							break;
						case BlockIds::REDSTONE_ORE:
						case BlockIds::LIT_REDSTONE_ORE:
							$player->getStats()->addRedstoneMined();
							break;
						case BlockIds::IRON_ORE:
							$player->getStats()->addIronMined();
							break;
						case BlockIds::COAL_ORE:
							$player->getStats()->addCoalMined();
					}
				}
			}
		}
	}

	/**
	 * @param EntityRegainHealthEvent $event
	 * @priority MONITOR
	 */
	public function handleRegainHealth(EntityRegainHealthEvent $event): void {
		$player = $event->getEntity();
		if($player instanceof GamePlayer && $player->inGame() && $player->getGame()->getManager()->isPlayer($player) && !$event->isCancelled()) {
			$amount = $event->getAmount();
			if($player->getHealth() + $amount >= $player->getMaxHealth()) {
				$amount = ($player->getHealth() + $amount) - $player->getMaxHealth();
			}
			$player->getStats()->addHeartsHealed($amount);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority MONITOR
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		if($player instanceof GamePlayer && !$event->isCancelled()) {
			if($player->inGame() && $player->getGame()->getManager()->isPlayer($player) ) {
				$damage = min($event->getFinalDamage(), $player->getHealth()) / 2;
				if($event instanceof EntityDamageByEntityEvent) {
					$damager = $event->getDamager();
					if($damager instanceof GamePlayer && $damager->inGame() && $damager->getGame()->getManager()->isPlayer($damager)) {
						$damager->getStats()->addDamageDealt($damage);
					}
				}
				if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
					$player->getStats()->addFallDamage($damage);
				}
				$player->getStats()->addDamageTaken($damage);
			}
		}
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 * @priority MONITOR
	 */
	public function handleConsume(PlayerItemConsumeEvent $event): void {
		if($event->getItem()->getId() === ItemIds::GOLDEN_APPLE && !$event->isCancelled()) {
			/** @var GamePlayer $player */
			$player = $event->getPlayer();
			if($player->inGame() && $player->getGame()->getManager()->isPlayer($player)) {
				if ($event->getItem()->getDamage() === Skull::TYPE_HUMAN) {
					$player->getStats()->addGoldenHead();
				} else {
					$player->getStats()->addGoldenApple();
				}
			}
		}

	}

}