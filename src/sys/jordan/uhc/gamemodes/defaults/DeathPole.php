<?php


namespace sys\jordan\uhc\gamemodes\defaults;


use pocketmine\block\BlockIds;
use pocketmine\block\Skull;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\tile\Skull as TileSkull;
use sys\jordan\core\level\LevelUtils;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\gamemodes\Gamemode;
use sys\jordan\uhc\scenario\defaults\Timebomb;

class DeathPole extends Gamemode {

	/**
	 * DeathPole constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		parent::__construct($game, "DeathPole");
	}

	/**
	 * @param Location $location
	 */
	public function spawnDeathPole(Location $location): void {
		if($this->getGame()->getScenarioList()->getScenario("Timebomb") instanceof Timebomb) return;
		if($location->getLevel() === $this->getGame()->getPlugin()->getServer()->getDefaultLevel()) {
			return; //TODO: actually fix this
		}
		$y = $location->getFloorY();
		while ($this->getGame()->getBorder()->isPassable($location->getLevel()->getBlockIdAt($location->getFloorX(), $y, $location->getFloorZ())) && $y > 0) {
			$y--;
		}
		$y = ($y <= 0) ? 1 : $y;
		$location->y = $y + 1;
		$location->getLevel()->setBlockIdAt($location->getFloorX(), $location->getFloorY(), $location->getFloorZ(), BlockIds::NETHER_BRICK_FENCE);
		$location->y += 1;
		$block = new Skull(TileSkull::TYPE_HUMAN);
		$block->setComponents($location->getFloorX(), $location->getFloorY(), $location->getFloorZ());
		$block->setLevel($location->getLevel());
		$block->place(Item::get(Item::SKULL_BLOCK, TileSkull::TYPE_HUMAN), $block, $block, 1, $block, null);
		$tile = $block->getLevel()->getTile($block);
		if ($tile instanceof TileSkull) {
			LevelUtils::setSkullRotation($tile, (LevelUtils::getSkullRotation($tile) + 8) % 16);
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$this->spawnDeathPole($event->getPlayer()->asLocation());
	}

	/**
	 * @param EntityDeathEvent $event
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		$this->spawnDeathPole($event->getEntity()->asLocation());
	}

}