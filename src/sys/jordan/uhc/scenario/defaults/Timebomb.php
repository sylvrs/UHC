<?php


namespace sys\jordan\uhc\scenario\defaults;


use Exception;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Position;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest as TileChest;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\player\DisconnectedPlayerMob;
use sys\jordan\uhc\scenario\module\timebomb\TimebombTask;
use sys\jordan\uhc\scenario\Scenario;
use sys\jordan\uhc\utils\GoldenHead;
use sys\jordan\uhc\utils\TickEnum;
use sys\jordan\uhc\utils\UHCUtilities;
use function ceil;
use function max;
use function min;

class Timebomb extends Scenario {

	/** @var int */
	public const EXPLOSION_DELAY = 30;
	/** @var float */
	public const EXPLOSION_SIZE = 5.0;

	/**
	 * Timebomb constructor.
	 */
	public function __construct() {
		parent::__construct("Timebomb", "Upon a player's death, a chest will spawn with the player's items along with a golden head");
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {}

	/**
	 * @param Game $game
	 */
	public function onDisable(Game $game): void {}

	/**
	 * @param EntityDeathEvent $event
	 * @throws Exception
	 */
	public function handleEntityDeath(EntityDeathEvent $event): void {
		$entity = $event->getEntity();
		if($entity instanceof DisconnectedPlayerMob && $entity->hasDisconnectedPlayer()) {
			$this->spawn($entity);
			$event->setDrops([]);
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @throws Exception
	 */
	public function handleDeath(PlayerDeathEvent $event): void {
		$this->spawn($event->getPlayer());
		$event->setDrops([]);
	}

	/**
	 * @param Living $living
	 * @throws Exception
	 */
	public function spawn(Living $living): void {
		$items = $living->getArmorInventory()->getContents();
		if($living instanceof GamePlayer) {
			$inventoryContents = $living->getInventory()->getContents();
		} else if($living instanceof DisconnectedPlayerMob) {
			$inventoryContents = $living->getContents();
		} else {
			return;
		}
		foreach($inventoryContents as $item) $items[] = $item;
		$items[] = GoldenHead::create();

		$face = 4;
		$vector = $living->asVector3()->floor();

		/**
		 * Set the first chest with a custom face value so that pairing works
		 */
		$firstBlock = BlockFactory::get(Block::CHEST, $face);
		$firstTile = Tile::createTile(Tile::CHEST, $living->getLevelNonNull(), TileChest::createNBT($vector, $face));
		$living->getLevelNonNull()->setBlock($firstTile->asVector3(), $firstBlock);

		$secondBlock = BlockFactory::get(Block::CHEST, $face);
		$secondTile = Tile::createTile(Tile::CHEST, $living->getLevelNonNull(), TileChest::createNBT($vector->subtract(0, 0, $living->getZ() < 0 ? 1 : -1), $face));
		$living->getLevelNonNull()->setBlock($secondTile->asVector3(), $secondBlock);
		/**
		 * There's better ways to do this, but this will work fine for now
		 */
		$name = $living instanceof DisconnectedPlayerMob ? $living->getDisconnectedPlayer()->getName() : $living->getName();
		$game = $living instanceof DisconnectedPlayerMob ? $living->getDisconnectedPlayer()->getGame() : $living->getGame();

		$name = UHCUtilities::makePossessive($name);
		if($firstTile instanceof TileChest && $secondTile instanceof TileChest) {
			$corpseName = TextFormat::YELLOW . $name . " Corpse";
			$firstTile->setName($corpseName);
			$secondTile->setName($corpseName);

			$firstTile->pairWith($secondTile);
			$secondTile->pairWith($firstTile);

			$firstTile->getInventory()->setContents($items);

			(new TimebombTask($game, $name, $firstTile->asPosition()))->schedule(TickEnum::SECOND);
		}
	}

	/**
	 * @param TileChest $firstTile
	 * @param TileChest $secondTile
	 * @return Position
	 */
	private function calculatePosition(TileChest $firstTile, TileChest $secondTile): Position {
		$minX = min($firstTile->getFloorX(), $secondTile->getFloorX());
		$minZ = min($firstTile->getFloorZ(), $secondTile->getFloorZ());
		$maxX = max(ceil($firstTile->getX()), ceil($secondTile->getX()));
		$maxZ = max(ceil($firstTile->getZ()), ceil($secondTile->getZ()));

		return new Position(($minX + $maxX) / 2, $firstTile->getFloorY() + 1.5, ($minZ + $maxZ) / 2, $firstTile->getLevelNonNull());
	}
}