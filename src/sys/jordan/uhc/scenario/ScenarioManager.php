<?php


namespace sys\jordan\uhc\scenario;


use sys\jordan\uhc\GameBase;
use sys\jordan\uhc\scenario\defaults\Backpacks;
use sys\jordan\uhc\scenario\defaults\BloodDiamonds;
use sys\jordan\uhc\scenario\defaults\CatEyes;
use sys\jordan\uhc\scenario\defaults\CutClean;
use sys\jordan\uhc\scenario\defaults\Diamondless;
use sys\jordan\uhc\scenario\defaults\DoubleJump;
use sys\jordan\uhc\scenario\defaults\DoubleOres;
use sys\jordan\uhc\scenario\defaults\Enchantless;
use sys\jordan\uhc\scenario\defaults\Fireless;
use sys\jordan\uhc\scenario\defaults\GunsNRoses;
use sys\jordan\uhc\scenario\defaults\HasteyBoys;
use sys\jordan\uhc\scenario\defaults\InfiniteEnchanter;
use sys\jordan\uhc\scenario\defaults\Inventors;
use sys\jordan\uhc\scenario\defaults\Mana;
use sys\jordan\uhc\scenario\defaults\MonstersInc;
use sys\jordan\uhc\scenario\defaults\Switcheroo;
use sys\jordan\uhc\scenario\defaults\Timber;
use sys\jordan\uhc\scenario\defaults\Timebomb;
use sys\jordan\uhc\utils\GameBaseTrait;

class ScenarioManager {

	use GameBaseTrait;

	/** @var Scenario[] */
	private $scenarios = [];

	/**
	 * ScenarioManager constructor.
	 * @param GameBase $plugin
	 */
	public function __construct(GameBase $plugin) {
		$this->setPlugin($plugin);
		$this->load();
		new ScenarioListener($plugin);
	}

	private function load(): void {
		//$this->addScenario(new Backpacks);
		$this->addScenario(new BloodDiamonds);
		$this->addScenario(new CatEyes);
		$this->addScenario(new CutClean);
		$this->addScenario(new Diamondless);
		$this->addScenario(new DoubleJump);
		$this->addScenario(new DoubleOres);
		$this->addScenario(new Enchantless);
		$this->addScenario(new Fireless);
		$this->addScenario(new GunsNRoses);
		$this->addScenario(new HasteyBoys);
		$this->addScenario(new InfiniteEnchanter);
		$this->addScenario(new Inventors);
		$this->addScenario(new Mana);
		$this->addScenario(new MonstersInc);
		$this->addScenario(new Switcheroo);
		$this->addScenario(new Timebomb);
		$this->addScenario(new Timber);
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array {
		return $this->scenarios;
	}

	/**
	 * @param Scenario $scenario
	 */
	public function addScenario(Scenario $scenario): void {
		$this->scenarios[$scenario->getName()] = $scenario;
		$this->sort();
	}

	/**
	 * Sorts the scenarios alphabetically (by key)
	 */
	public function sort(): void {
		ksort($this->scenarios);
	}

}