<?php


namespace sys\jordan\uhc\scenario\defaults;


use pocketmine\block\BlockIds;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Sword;
use pocketmine\utils\TextFormat;
use sys\jordan\uhc\game\Game;
use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\scenario\Scenario;
use sys\jordan\uhc\utils\GoldenHead;

class Inventors extends Scenario {

	/** @var string[] */
	private $crafted = [];

	/**
	 * @var int[]
	 */
	private $whitelistedItems = [
		BlockIds::ENCHANTMENT_TABLE,
		BlockIds::ANVIL
	];

	public function __construct() {
		parent::__construct("Inventors", "The first crafter of an item will be broadcasted in chat");
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
	 * @param CraftItemEvent $event
	 */
	public function handleCraft(CraftItemEvent $event): void {
		/** @var GamePlayer $player */
		$player = $event->getPlayer();
		foreach($event->getOutputs() as $output) {
			if(($output instanceof Armor || $output instanceof Pickaxe || $output instanceof Axe || $output instanceof Sword || $output instanceof GoldenHead || in_array($output->getId(), $this->whitelistedItems)) && !isset($this->crafted[$output->getVanillaName()])) {
				$this->crafted[$output->getVanillaName()] = true;
				$player->getGame()->broadcast($this->asPrefix() . TextFormat::YELLOW . " {$player->getName()} was the first to craft {$output->getVanillaName()}!");
			}
		}
	}
}