<?php


namespace sys\jordan\uhc\game;


use pocketmine\Server;
use Throwable;

class GameLogger {

	use GameTrait;

	/**
	 * GameLogger constructor.
	 * @param Game $game
	 */
	public function __construct(Game $game) {
		$this->setGame($game);
	}

	/**
	 * @param $message
	 */
	public function emergency($message){
		$this->log(LogLevel::EMERGENCY, $message);
	}

	/**
	 * @param $message
	 */
	public function alert($message){
		$this->log(LogLevel::ALERT, $message);
	}

	/**
	 * @param $message
	 */
	public function critical($message){
		$this->log(LogLevel::CRITICAL, $message);
	}

	/**
	 * @param $message
	 */
	public function error($message){
		$this->log(LogLevel::ERROR, $message);
	}

	/**
	 * @param $message
	 */
	public function warning($message){
		$this->log(LogLevel::WARNING, $message);
	}

	/**
	 * @param $message
	 */
	public function notice($message){
		$this->log(LogLevel::NOTICE, $message);
	}

	/**
	 * @param $message
	 */
	public function info($message){
		$this->log(LogLevel::INFO, $message);
	}

	/**
	 * @param $message
	 */
	public function debug($message){
		$this->log(LogLevel::DEBUG, $message);
	}

	/**
	 * @param Throwable $e
	 * @param null $trace
	 */
	public function logException(Throwable $e, $trace = null){
		Server::getInstance()->getLogger()->logException($e, $trace);
	}

	/**
	 * @param $level
	 * @param $message
	 */
	public function log($level, $message){
		Server::getInstance()->getLogger()->log($level, "[Game '{$this->getGame()->getLevel()->getName()}/{$this->getGame()->getLevel()->getFolderName()}'] " . $message);
	}

}

interface LogLevel {
	const EMERGENCY = 'emergency';
	const ALERT = 'alert';
	const CRITICAL = 'critical';
	const ERROR = 'error';
	const WARNING = 'warning';
	const NOTICE = 'notice';
	const INFO = 'info';
	const DEBUG = 'debug';
}