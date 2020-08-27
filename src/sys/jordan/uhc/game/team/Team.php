<?php


namespace sys\jordan\uhc\game\team;

use sys\jordan\uhc\GamePlayer;
use sys\jordan\uhc\utils\UHCUtilities;

class Team {

	/** @var string */
	private $format;

	/** @var int */
	private $id;

	/** @var GamePlayer[] */
	private $players = [];

	/**
	 * Team constructor.
	 * @param int $id
	 */
	public function __construct(int $id) {
		$this->id = $id;
		$this->format = UHCUtilities::getRandomColor() . "[Team $id]";
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return "Team #{$this->getId()}";
	}

	/**
	 * @return string
	 */
	public function getFormat(): string {
		return $this->format;
	}

	/**
	 * @param string $format
	 */
	public function setFormat(string $format): void {
		$this->format = $format;
	}

}