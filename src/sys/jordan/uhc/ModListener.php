<?php


namespace sys\jordan\uhc;


use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use ReflectionException;
use sys\jordan\core\base\BaseListener;
use sys\jordan\uhc\utils\UHCUtilities;

class ModListener extends BaseListener {

	/** @var string */
	public const PREFIX = TextFormat::RED . "[MOD-MSG]";

	/** @var array */
	private $ores;

	/**
	 * ModListener constructor.
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->ores = [BlockIds::GOLD_ORE, BlockIds::DIAMOND_ORE];
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function handleDamage(EntityDamageEvent $event): void {
		$player = $event->getEntity();
		$prefix = self::PREFIX;
		if($player instanceof GamePlayer && $player->inGame() && !$event->isCancelled() && $event->getFinalDamage() > 0) {
			try {
				$causes = UHCUtilities::getDamageNames();
			} catch(ReflectionException $exception) {
				$causes = [$event->getCause() => "CAUSE_UNKNOWN"];
			}
			$prefix .= TextFormat::AQUA . " [" . $causes[$event->getCause()] . "]";
			$damage = $event->getFinalDamage() / 2;
			foreach($player->getGame()->getAvailableModerators() as $moderator) {
				if($event instanceof EntityDamageByEntityEvent && ($damager = $event->getDamager()) instanceof GamePlayer) {
					$moderator->sendMessage($prefix . TextFormat::YELLOW . " {$player->getName()} took {$damage} HP of damage from {$damager->getName()} at ({$player->getFloorX()}, {$player->getFloorY()}, {$player->getFloorZ()})");
				} else {
					$moderator->sendMessage($prefix . TextFormat::YELLOW . " {$player->getName()} took {$damage} HP of damage at ({$player->getX()}, {$player->getY()}, {$player->getZ()})");

				}
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function handleBreak(BlockBreakEvent $event) {
		if(in_array($event->getBlock()->getId(), $this->ores)) {
			/** @var GamePlayer $player */
			$player = $event->getPlayer();
			if($player instanceof GamePlayer && $player->inGame() && !$event->isCancelled()) {
				/** @var Block $block */
				$block = $event->getBlock();
				if(!isset($player->recentBlocks[$block->asPosition()->__toString()])) {
					$surrounding = UHCUtilities::getSameSurrounding($block);
					foreach($surrounding as $current) $player->recentBlocks[$current->asPosition()->__toString()] = true;
					$count = count($surrounding);
					foreach($player->getGame()->getAvailableModerators() as $moderator) {
						$moderator->sendMessage(self::PREFIX . TextFormat::YELLOW . " {$player->getName()} mined {$count}x of {$block->getName()} at ({$block->getX()}, {$block->getY()}, {$block->getZ()})");
					}
				} else {
					unset($player->recentBlocks[$block->asPosition()->__toString()]);
				}
			}

		}
	}

}