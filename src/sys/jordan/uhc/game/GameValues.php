<?php


namespace sys\jordan\uhc\game;


use pocketmine\utils\TextFormat;
use sys\jordan\uhc\GameBase;

final class GameValues {

	/** @var int */
	public static $COUNTDOWN_LENGTH = 60;
	/** @var int */
	public static $TELEPORT_TIME = 50;
	/** @var int */
	public static $DISCONNECTION_LENGTH = 60 * 10;
	/** @var int */
	public static $FINAL_HEAL = 60 * 10;
	/** @var int */
	public static $GLOBAL_MUTE_LENGTH = 60 * 5;
	/** @var int */
	public static $GRACE_LENGTH = 60 * 20;

	/** @var string */
	public static $HOST_FORMAT = TextFormat::YELLOW . "[Host]";

	/** @var string */
	public static $SPECTATOR_FORMAT = TextFormat::RED . "[Spectator]";

	/**
	 * @param GameBase $plugin
	 */
	public static function load(GameBase $plugin): void {
		$timings = $plugin->getConfig()->get("timings", [
			"countdown" => 60,
			"disconnected-player" => 60 * 10,
			"final-heal" => 60 * 10,
			"global-mute" => 60 * 5,
			"grace-period" => 60 * 20
		]);
		self::$COUNTDOWN_LENGTH = $timings["countdown"] ?? 60;
		self::$TELEPORT_TIME = self::$COUNTDOWN_LENGTH - 10; //we should probably add this to the config, but no need
		self::$DISCONNECTION_LENGTH = $timings["disconnected-player"] ?? 60 * 10;
		self::$FINAL_HEAL = $timings["final-heal"] ?? 60 * 10;
		self::$GLOBAL_MUTE_LENGTH = $timings["global-mute"] ?? 60 * 5;
		self::$GRACE_LENGTH = $timings["grace-period"] ?? 60 * 20;
	}

}