<?php


namespace sys\jordan\uhc\game;


use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use sys\jordan\core\base\BaseListener;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use sys\jordan\uhc\utils\TickEnum;

class GameListener extends BaseListener {

	/**
	 * @return GameBase
	 */
	public function getPlugin(): GameBase {
		return parent::getPlugin();
	}

	/**
	 * @param ChunkLoadEvent $event
	 */
	public function handleChunkLoad(ChunkLoadEvent $event): void {
		foreach($event->getChunk()->getEntities() as $entity) {
			if($entity instanceof FallingBlock && !$entity->isClosed()) {
				$entity->close();
			} else if($entity instanceof ItemEntity && $entity->ticksLived > (TickEnum::SECOND * 30) && !$entity->isClosed()) {
				$entity->close();
			}
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 */
	public function handleChat(PlayerChatEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		/** @var GameBase $plugin */
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleChat($event);
			$logger = $player->getGame()->getLogger();
		} else {
			$event->setRecipients($this->getPlugin()->getLobbyPlayers());
			$logger = $this->getPlugin()->getLogger();
		}
		if(!$event->isCancelled()) {
			$logger->info($event->getFormat());
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function handleJoin(PlayerJoinEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		/** @var GameBase $plugin */
		$plugin = $this->getPlugin();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleJoin($event);
		} else {
			$player->reset();
			if(($game = $plugin->getGameManager()->getDefaultGame()) instanceof Game) {
				$game->join($player);
			} else {
				$player->teleport($plugin->getServer()->getDefaultLevel()->getSafeSpawn());
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function handleQuit(PlayerQuitEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleQuit($event);
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleDeath($event);
		}
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		$entity = $event->getEntity();
		if($entity instanceof DisconnectedPlayerMob) {
			$player = $entity->getDisconnectedPlayer();
			if($player instanceof DisconnectedPlayer) {
				$game = $player->getGame();
				$game->getHandler()->handleEntityDeath($event);
			}
		}
	}

	/**
	 * @param PlayerRespawnEvent $event
	 */
	public function handleRespawn(PlayerRespawnEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleRespawn($event);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		if(($player instanceof GamePlayer && $player->inGame())) {
			$player->getGame()->getHandler()->handleDamage($event);
		} else if($player instanceof DisconnectedPlayerMob) {
			if($player->getDisconnectedPlayer() instanceof DisconnectedPlayer) {
				$player->getDisconnectedPlayer()->getGame()->getHandler()->handleDamage($event);
			}
		}
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function handleExhaust(PlayerExhaustEvent $event) {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleExhaust($event);
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handleBreak($event);
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function handlePlace(BlockPlaceEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		if($player->inGame()) {
			$player->getGame()->getHandler()->handlePlace($event);
		}
	}

	/**
	 * @param EntityRegainHealthEvent $event
	 */
	public function handleRegeneration(EntityRegainHealthEvent $event) {
		$player = $event->getEntity();
		if($player instanceof GamePlayer && $player->inGame()) {
			$player->getGame()->getHandler()->handleRegeneration($event);
		}
	}

}