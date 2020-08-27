<?php


namespace sys\jordan\uhc;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use sys\jordan\uhc\base\BaseListener;

class UHCBaseListener extends BaseListener {

	/**
	 * @param PlayerCreationEvent $event
	 * @priority HIGHEST
	 */
	public function handleCreation(PlayerCreationEvent $event): void {
		$event->setPlayerClass(GamePlayer::class);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		$event->setJoinMessage(null);
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		$event->setQuitMessage(null);
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		if($player instanceof GamePlayer) {
			if(!$player->inGame()) {
				$event->setCancelled();
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGHEST
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if(!$player->isOp() && (!$player->inGame() || $event->getBlock()->getLevel() === $this->getPlugin()->getServer()->getDefaultLevel())) {
			$event->setCancelled();
		}

	}

	/**
	 * @param BlockPlaceEvent $event
	 * @priority HIGHEST
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if(!$player->isOp() && (!$player->inGame() || $event->getBlock()->getLevel() === $this->getPlugin()->getServer()->getDefaultLevel())) {
			$event->setCancelled();
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function handleInteract(PlayerInteractEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if(!$player->isOp() && (!$player->inGame() || $event->getBlock()->getLevel() === $this->getPlugin()->getServer()->getDefaultLevel())) {
			$event->setCancelled();
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function handleExhaust(PlayerExhaustEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if(!$player->inGame()) {
			$event->setCancelled();
		}
	}

}