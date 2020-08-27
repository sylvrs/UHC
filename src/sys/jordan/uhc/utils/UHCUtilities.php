<?php


namespace sys\jordan\uhc\utils;


use DateInterval;
use DateTime;
use Exception;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\TextFormat;
use ReflectionClass;
use ReflectionException;
use function strtolower;
use function substr;

final class UHCUtilities {

	/** @var string[] */
	private static $colors = [];
	/** @var string[] */
	private static $damageNames;

	/**
	 * @param bool $value
	 * @return string
	 */
	public static function boolConvert(bool $value) {
		return $value ? "✅" : "❌";
	}

	/**
	 * @return string[]
	 * @throws ReflectionException
	 */
	public static function getDamageNames(): array {
		return self::$damageNames ?? (self::$damageNames = array_flip(array_filter((new ReflectionClass(EntityDamageEvent::class))->getConstants(), function (string $name): bool {
				return stripos($name, "CAUSE_") !== false;
		}, ARRAY_FILTER_USE_KEY)));
	}

	/**
	 * @param Block $block
	 * @param int $radius
	 * @return Block[]
	 */
	public static function getSameSurrounding(Block $block, int $radius = 2) {
		$output = [];
		for($x = $block->getFloorX() - $radius; $x <= $block->getFloorX() + $radius; $x++) {
			for($y = $block->getFloorY() - $radius; $y <= $block->getFloorY() + $radius; $y++) {
				for($z = $block->getFloorZ() - $radius; $z <= $block->getFloorZ() + $radius; $z++) {
					$current = $block->getLevel()->getBlockAt($x, $y, $z);
					if($current->getId() !== $block->getId()) continue;
					if(!isset($output[$current->asPosition()->__toString()])) $output[$current->asPosition()->__toString()] = $current;
				}
			}
		}
		return $output;
	}

	/**
	 * @param int $input
	 * @param int $multiple
	 * @return float|int
	 */
	public static function roundUp(int $input, int $multiple = 5) {
		return ceil(($input + $multiple / 2) / $multiple) * $multiple;
	}

	public static function isOre(int $id): bool {
		return in_array($id, [BlockIds::EMERALD_ORE, BlockIds::DIAMOND_ORE, BlockIds::GOLD_ORE, BlockIds::LAPIS_ORE, BlockIds::REDSTONE_ORE, BlockIds::LIT_REDSTONE_ORE, BlockIds::IRON_ORE, BlockIds::COAL_ORE]);
	}

	/**
	 * @param float $input
	 * @return float
	 */
	public static function roundIfNeeded(float $input): float {
		return floor($input) === $input ? $input : round($input, 2);
	}

	/**
	 * @param DateTime $time
	 * @param int $minutes
	 * @return DateTime
	 */
	public static function roundTime(DateTime $time, int $minutes = 5) {
		$cloned = clone $time;
		return $cloned->setTime(
			$cloned->format("H"),
			self::roundUp($cloned->format("i"), $minutes),
			0
		);
	}

	/**
	 * @param DateTime $time
	 * @param int $minutes
	 * @return DateTime
	 */
	public static function addTime(DateTime $time, int $minutes) {
		$cloned = clone $time;
		try {
			$cloned->add(new DateInterval('PT' . $minutes . 'M'));
		} catch (Exception $ignore) {}
		return $cloned;
	}

	/**
	 * Center a line of text based around the length of another line
	 *
	 * @param $toCenter
	 * @param $checkAgainst
	 *
	 * @return string
	 */
	public static function centerText($toCenter, $checkAgainst): string {
		if (strlen($toCenter) >= strlen($checkAgainst)) {
			return $toCenter;
		}

		$times = floor((strlen($checkAgainst) - strlen($toCenter)) / 2);

		return str_repeat(" ", ($times > 0 ? $times : 0)) . $toCenter;
	}

	/**
	 * @param array $array
	 * @return bool
	 */
	public static function hasSameType(array $array): bool {
		foreach($array as $key => $value) {
			$key = $key > 0 ? $key - 1 : $key;
			if(gettype($value) !== gettype($array[$key])) return false;
		}
		return true;
	}

	/**
	 * @param string $string
	 * @param int $count
	 * @return string
	 */
	public static function pluralize(string $string, int $count): string {
		if(abs($count) !== 1) {
			switch(strtolower(substr($string, -1))) {
				case "y":
					return substr($string, 0, -1) . "ies";
				case "s":
					return $string . "es";
				default:
					return $string . "s";
			}
		}
		return $string;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function makePossessive(string $string): string {
		return $string . "'" . (!(strtolower(substr($string, -1)) === "s") ? "s" : "");
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	public static function isCommand(string $string): bool {
		return strpos($string, "/") === 0;
	}

	/**
	 * @param int $num
	 * @param int $divisor
	 * @return int
	 */
	public static function round(int $num, int $divisor = 5): int {
		return floor($num / $divisor) * $divisor;
	}

	/**
	 * @param array $array
	 * @param int $index
	 * @param $item
	 */
	public static function insert(array &$array, int $index, $item) {
		array_splice( $array, $index, 0, $item);
	}

	/**
	 * @param array $array
	 * @param int $num
	 * @return array|mixed|bool
	 */
	public static function getRandom(array $array, int $num = 1) {
		if(count($array) < $num) return false;
		$keys = array_keys($array);
		shuffle($keys);
		$r = [];
		for ($i = 0; $i < $num; $i++) {
			$r[$keys[$i]] = $array[$keys[$i]];
		}
		return $num === 1 ? $r[array_key_first($r)] : $r;
	}

	/**
	 * @return string
	 */
	public static function getRandomColor(): string {
		if(count(self::$colors) <= 0) {
			$disallowed = ["EOL", "ESCAPE", "OBFUSCATED", "BOLD", "STRIKETHROUGH", "UNDERLINE", "ITALIC", "RESET", "BLACK", "WHITE", "GRAY", "DARK_BLUE", "DARK_RED"];
			$class = new ReflectionClass(TextFormat::class);
			/** @var string[] $colors */
			$colors = array_filter($class->getConstants(), function (string $name) use($disallowed) {
				return !in_array($name, $disallowed);
			}, ARRAY_FILTER_USE_KEY);
		} else {
			$colors = self::$colors;
		}
		return ($colors[array_rand($colors)]);
	}

}