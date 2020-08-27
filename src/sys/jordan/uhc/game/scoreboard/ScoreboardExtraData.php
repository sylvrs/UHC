<?php


namespace sys\jordan\uhc\game\scoreboard;

/**
 * Class ScoreboardExtraData
 * @package sys\jordan\uhc\game\scoreboard
 *
 * TODO: addData over setData for dynamic extra data handling without having to worry about indexing
 */
class ScoreboardExtraData {

	/** @var string */
	private $name;

	/** @var string[] */
	private $data = [];

	/**
	 * ScoreboardExtraData constructor.
	 * @param string $name
	 */
	public function __construct(string $name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param int $index
	 * @param string $data
	 */
	public function setData(int $index, string $data): void {
		$this->data[$index] = $data;
	}

	/**
	 * @param int $index
	 */
	public function removeData(int $index): void {
		if(isset($this->data[$index])) {
			unset($this->data[$index]);
		}
	}

	/**
	 * @return string[]
	 */
	public function getData(): array {
		return $this->data;
	}

}