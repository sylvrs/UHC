<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\tile\Tile;
use ReflectionException;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\game\team\Team;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\CommandScenario;
use sys\jordan\uhc\scenario\module\backpacks\BackpacksCommand;
use sys\jordan\uhc\scenario\module\backpacks\BackpacksTile;
use sys\jordan\uhc\scenario\module\backpacks\TeamBackpack;
use function in_array;

class Backpacks extends CommandScenario {

	/** @var TeamBackpack[] */
	private $backpacks = [];

	public function __construct() {
		parent::__construct("Backpacks", "Access an extra inventory to store your items in!");
		$this->setCommand(new BackpacksCommand(GameBase::getInstance()));
		try {
			Tile::registerTile(BackpacksTile::class);
		} catch (ReflectionException $e) {
			GameBase::getInstance()->getLogger()->error($e);
		}
	}

	/**
	 * @param Game $game
	 */
	public function onEnable(Game $game): void {
		if($game->isTeams()) {
			parent::onEnable($game);
			return;
		}
		$game->getScenarioList()->removeScenario($this);
	}

	/**
	 * @return TeamBackpack[]
	 */
	public function getBackpacks(): array {
		return $this->backpacks;
	}

	/**
	 * @param Team $team
	 */
	public function createBackpack(Team $team): void {
		if(isset($this->backpacks[$team->getId()])) {
			return;
		}
		$this->backpacks[$team->getId()] = new TeamBackpack;
	}

	/**
	 * @param Team $team
	 * @return bool
	 */
	public function hasBackpack(Team $team): bool {
		return isset($this->backpacks[$team->getId()]);
	}

	/**
	 * @param Team $team
	 * @return TeamBackpack|null
	 */
	public function getBackpack(Team $team): TeamBackpack {
		if(!$this->hasBackpack($team)) $this->createBackpack($team);
		return $this->backpacks[$team->getId()];
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function handleTransaction(InventoryTransactionEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getTransaction()->getSource();
		if($player->inTeam() && $this->hasBackpack($player->getTeam())) {
			$backpack = $this->getBackpack($player->getTeam());
			foreach($event->getTransaction()->getInventories() as $inventory) {
				if(in_array($inventory, $backpack->getInventories())) {
					$backpack->sync($event->getTransaction());
				}
			}
		}
	}

}