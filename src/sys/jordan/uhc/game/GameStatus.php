<?php


namespace sys\jordan\uhc\game;


interface GameStatus {
	/** @var int */
	public const WAITING = 0;
	/** @var int */
	public const SETUP = 1;
	/** @var int */
	public const COUNTDOWN = 2;
	/** @var int */
	public const PLAYING = 3;
	/** @var int */
	public const POSTGAME = 4;
}