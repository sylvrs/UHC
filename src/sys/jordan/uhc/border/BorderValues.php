<?php


namespace sys\jordan\uhc\border;


use sys\jordan\uhc\GameBase;
use function array_keys;

final class BorderValues {

	/** @var int[][] */
	private static $BORDERS = [];

	/**
	 * @param GameBase $plugin
	 */
	public static function load(GameBase $plugin): void {
		self::parse($plugin->getConfig()->get("border", [
			1500 => [
				"shrinks" => [
					40 => 750,
					50 => 500,
					55 => 250,
					60 => 100,
					65 => 50,
					70 => 25
				]
			]
		]));
	}

	/**
	 * @param int $size
	 * @return int[]
	 */
	public static function get(int $size): array {
		return self::$BORDERS[$size];
	}

	/**
	 * @return int[]
	 */
	public static function getSizes(): array {
		return array_keys(self::$BORDERS);
	}

	/**
	 * @param array $data
	 */
	private static function parse(array $data): void {
		foreach($data as $initialSize => $sizeData) {
			$parsed = [];
			foreach($sizeData["shrinks"] as $time => $shrinkSize) $parsed[$time * 60] = $shrinkSize;
			self::$BORDERS[$initialSize] = $parsed;
		}
	}

}