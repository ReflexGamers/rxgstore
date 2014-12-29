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
		$precached = $this->SteamPlayerCache->countPrecachedPlayers();
		$total = $valid + $expired;

		$this->out("Total: $total");
		$this->out("Valid: $valid");
		$this->out("Expired: $expired");
		$this->out("Precached: $precached");
	}

	/**
	 * Deletes all players from the Steam Cache.
	 */
	public function clear() {

		$valid = $this->SteamPlayerCache->countValidPlayers();
		$expired = $this->SteamPlayerCache->countExpiredPlayers();
		$total = $valid + $expired;

		$this->SteamPlayerCache->clearAll();

		$this->out("Deleted all $total players.");
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
	 * Precaches all players currently connected to store-enabled servers.
	 */
	public function precache_ingame() {

		$accounts = $this->User->getAllPlayersIngame();

		$count = count($accounts);
		$this->SteamPlayerCache->refreshPlayers($accounts, true);
		$message = "Precached $count players.";

		CakeLog::write('steam_precache', $message);
		$this->out($message);
	}

	/**
	 * Precaches all known players who have purchased items or used QuickAuth since
	 * Store.SteamCache.PrecacheQuickAuthTime seconds ago.
	 */
	public function precache_known() {

		set_time_limit(300);

		$accounts = $this->SteamPlayerCache->getKnownPlayers();

		$total = count($accounts);
		$succeeded = count($this->SteamPlayerCache->refreshPlayers($accounts, false, 120));
		$failed = $total - $succeeded;
		$failedMessage = ($failed > 0) ? "Failed to fetch $failed players." : '';
		$message = "Successfully refreshed $succeeded of $total known players.$failedMessage";

		CakeLog::write('steam_refresh', $message);
		$this->out($message);
	}
}