<?php


namespace sys\jordan\uhc\command;


use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use sys\jordan\core\base\BaseUserCommand;
use sys\jordan\uhc\game\member\TeamManager;
use sys\jordan\uhc\GamePlayer;

class TeamCommand extends BaseUserCommand {

	/**
	 * TeamCommand constructor.
	 * @param Plugin $main
	 */
	public function __construct(Plugin $main) {
		parent::__construct($main, "team", "The main command that handles teams", "/team <accept|deny|invite|leave> [player]", []);
	}


	/**
	 * @param CommandSender|GamePlayer $sender
	 * @param array $args
	 * @return mixed|void
	 */
	public function onExecute(CommandSender $sender, array $args) {
		if($sender->inGame()) {
			if($sender->getGame()->isTeams()) {
				if(count($args) < 1) {
					throw new InvalidCommandSyntaxException;
				}
				$sub = array_shift($args);
				switch(strtolower($sub)) {
					case "accept":
						return $this->handleAccept($sender, $args);
					case "deny":
						return $this->handleDeny($sender, $args);
					case "disband":
						return $this->handleDisband($sender);
					case "invite":
						return $this->handleInvite($sender, $args);
					case "leave":
						return $this->handleLeave($sender);
					case "list":
						return $this->handleList($sender);
					default:
						return TextFormat::RED . $this->getUsage();
				}
			}
			return TextFormat::RED . "You can't use this command while teams are disabled!";
		}
		return TextFormat::RED . "You must be in a game to use this command!";
	}

	/**
	 * @param GamePlayer $player
	 * @param array $args
	 * @return string
	 */
	public function handleAccept(GamePlayer $player, array $args): string {
		if(count($args) < 1) {
			return TextFormat::RED . $this->getUsage();
		}
		if($player->getGame()->hasStarted()) {
			return TextFormat::RED . "You can't use this command after the game has started!";
		}
		$from = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if($from instanceof GamePlayer) {
			if($player->hasInvite($from)) {
				$player->removeInvite($from);
				$manager = $player->getGame()->getManager();
				if($manager instanceof TeamManager) {
					if($from->inTeam()) {
						$from->getTeam()->addPlayer($player);
						$manager->removeSolo($player);
					} else {
						$players = [$from, $player];
						$manager->createTeam($players);
					}
					$from->sendMessage(TextFormat::YELLOW . "{$player->getName()} has accepted the invite!");
					return TextFormat::GREEN . "You have accepted the invite from {$from->getName()}!";
				}
				return TextFormat::RED . "Error: Teams are not enabled!";
			} else {
				return TextFormat::RED . "You do not have an invite from this player!";
			}
		} else {
			return TextFormat::RED . "Unable to locate player: '$args[0]'";
		}
	}

	/**
	 * @param GamePlayer $player
	 * @param array $args
	 * @return string
	 */
	public function handleDeny(GamePlayer $player, array $args): string {
		if(count($args) < 1) {
			return TextFormat::RED . $this->getUsage();
		}
		if($player->getGame()->hasStarted()) {
			return TextFormat::RED . "You can't use this command after the game has started!";
		}
		$from = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if($from instanceof GamePlayer) {
			if($player->hasInvite($from)) {
				$player->removeInvite($from);
				$from->sendMessage(TextFormat::YELLOW . "{$player->getName()} has denied the invite!");
				return TextFormat::GREEN . "You have denied the invite from {$from->getName()}!";
			} else {
				return TextFormat::RED . "You do not have an invite from this player!";
			}
		} else {
			return TextFormat::RED . "Unable to locate player: '$args[0]'";
		}
	}

	/**
	 * @param GamePlayer $player
	 * @return string
	 */
	public function handleDisband(GamePlayer $player): string {
		return "";
	}

	/**
	 * @param GamePlayer $player
	 * @param array $args
	 * @return string
	 */
	public function handleInvite(GamePlayer $player, array $args): string {
		if(count($args) < 1) {
			return TextFormat::RED . $this->getUsage();
		}
		if($player->getGame()->hasStarted()) {
			return TextFormat::RED . "You can't use this command after the game has started!";
		}
		$to = $this->getPlugin()->getServer()->getPlayer($args[0]);
		if($to instanceof GamePlayer && $to->inGame() && $to->getGame() === $player->getGame()) {
			$game = $player->getGame();
			if($to === $player) {
				return TextFormat::RED . "You can't send an invite to yourself!";
			}
			if($game->isHost($to) && !$game->getManager()->isPlayer($to)) {
				return TextFormat::RED . "You can't send an invite to the host!";
			}

			if($to->inTeam()) {
				return TextFormat::RED . "You can't send an invite to a player already on a team!";
			}
			/** @var TeamManager $manager */
			$manager = $game->getManager();

			if($player->inTeam()) {
				if(count($player->getTeam()->getPlayers()) >= $manager->getMaxPlayerCount()) {
					return TextFormat::RED . "You have reached the maximum amount of players for your team!";
				} else if(!$player->getTeam()->isAllowed($to)) {
					return TextFormat::RED . "You have reached the maximum amount of non-mobile players on your team!";
				}
			}

			if(!$to->hasInvite($player)) {
				$to->addInvite($player);
				$to->sendMessage(TextFormat::YELLOW . "You have received an invite from {$player->getName()}!");
				$to->sendMessage(TextFormat::YELLOW . "Type '/team accept {$player->getName()}' to accept the invite or '/team deny {$player->getName()}' to deny the invite!");
				$to->sendMessage(TextFormat::YELLOW . "This invite will expire in 1 minute!");
				return TextFormat::GREEN . "Invite successfully sent to {$to->getName()}!";
			} else {
				return TextFormat::RED . "There is already a pending invite to this player!";
			}
		} else {
			return TextFormat::RED . "Unable to locate player: '$args[0]'";
		}
	}

	/**
	 * @param GamePlayer $player
	 * @return string
	 */
	public function handleLeave(GamePlayer $player): string {
		if($player->getGame()->hasStarted()) {
			return TextFormat::RED . "You can't use this command after the game has started!";
		}
		$game = $player->getGame();
		$manager = $game->getManager();
		if($game->isTeams()) {
			if($player->inTeam()) {
				$team = $player->getTeam();
				$team->removePlayer($player);
				$manager->addSolo($player);
				$team->broadcast(TextFormat::YELLOW . "{$player->getName()} has left the team!");
				if(count($team->getPlayers()) <= 1) {
					$team->broadcast(TextFormat::RED . "Too few players! Disbanding team!");
					foreach($team->getPlayers() as $teamPlayer) {
						$team->removePlayer($teamPlayer);
						$manager->addSolo($teamPlayer);
					}
					$manager->removeTeam($team);
				}
				return TextFormat::GREEN . "You have left the team!";
			}
			return TextFormat::RED . "You must be in a team to use this command!";
		}
		return TextFormat::RED . "Error: Teams are not enabled!";
	}

	/**
	 * @param GamePlayer $player
	 * @return string
	 */
	public function handleList(GamePlayer $player): string {
		$game = $player->getGame();
		/** @var TeamManager $manager */
		$manager = $game->getManager();
		$player->sendMessage(TextFormat::WHITE . "------ " . TextFormat::YELLOW . "Teams List" . TextFormat::WHITE .  " ------");
		foreach($manager->getTeams() as $team) {
			$message = TextFormat::WHITE . "Team " . TextFormat::YELLOW . "{$team->getId()}" . TextFormat::WHITE . ": ";
			$message .= implode(", ", array_map(function (GamePlayer $player): string {
				return ($player->inGame() && !$player->getGame()->getManager()->isDead($player) ? TextFormat::GREEN : TextFormat::RED) . $player->getName() . TextFormat::WHITE;
			}, $team->getPlayers()));
			$player->sendMessage($message);
		}
		return TextFormat::WHITE . "-----------------------";
	}

	/**
	 * @inheritDoc
	 */
	public function setOverloads(): void {

	}
}