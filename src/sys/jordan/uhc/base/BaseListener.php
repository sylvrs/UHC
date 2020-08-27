<?php


namespace sys\jordan\uhc\base;


use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\utils\GameBaseTrait;
use function get_class;
use function sprintf;

class BaseListener implements Listener {

	use GameBaseTrait;

	public function __construct(GameBase $plugin) {
		$this->setPlugin($plugin);
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$plugin->getLogger()->info(sprintf("%s has been registered!", get_class($this)));
	}


	public function unregister(): void {
		HandlerList::unregisterAll($this);
	}

}