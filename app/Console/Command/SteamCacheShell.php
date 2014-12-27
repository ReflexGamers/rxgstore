<?php

/**
 * Class CronShell
 *
 * @property SteamPlayerCache $SteamPlayerCache
 * @property User $User
 */
class SteamCacheShell extends AppShell {
	public $uses = array('SteamPlayerCache', 'User');

	public function main() {

		$this->info();
	}

	/**
	 * Prints status info for the Steam Cache, such as how many players are cached, how many are expired, etc.
	 */
	public function info() {

		$valid = $this->SteamPlayerCache->countValidPlayers();
		$expired = $this->SteamPlayerCache->countExpiredPlayers();

		$total = $valid + $expired;

		$totalMessage = ($total !== $valid) ? " ($total total)" : "";

		$this->out("The Steam Cache has $valid valid players and $expired expired players$totalMessage.");
	}

	/**
	 * Prunes all expired players from the Steam Cache.
	 */
	public function prune() {

		$expired = $this->SteamPlayerCache->countExpiredPlayers();

		if ($expired > 0) {
			$this->SteamPlayerCache->pruneExpiredPlayers();
			$message = "$expired expired player(s) pruned.";
		} else {
			$message = 'No expired players found.';
		}

		$this->out($message);
		CakeLog::write('steam', "Attempting prune: $message");
	}

	/**
	 * Refreshes all expired players in the Steam Cache.
	 */
	public function refresh_expired() {

		$expired = $this->SteamPlayerCache->countExpiredPlayers();

		if ($expired > 0) {
			$this->SteamPlayerCache->pruneExpiredPlayers();
			$message = "$expired expired player(s) pruned.";
		} else {
			$message = 'No expired players found.';
		}

		$this->SteamPlayerCache->refreshExpiredPlayers();

		$this->out($message);
		CakeLog::write('steam', "Attempting expired refresh: $message");
	}

	/**
	 * Preaches all players in store-enabled servers.
	 */
	public function precache() {

		$steamids = $this->User->getAllPlayersIngame();

		$playerCount = count($steamids);

		$this->out("$playerCount players found in-game.");
	}
}