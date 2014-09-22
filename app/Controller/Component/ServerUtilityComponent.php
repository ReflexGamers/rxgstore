<?php
App::uses('Component', 'Controller');
App::uses('ConnectionManager', 'Model');

class ServerUtilityComponent extends Component {
	protected $servers = array();
	protected $passwords = array();

	public function initialize(Controller $controller) {
		App::import('Vendor', 'SteamCondenser', array('file' => 'SteamCondenser/Servers/SourceServer.php'));
		$this->passwords = ConnectionManager::enumConnectionObjects()['gameServer']['passwords'];
	}

	public function exec($server_ip, $command = '') {

		if (empty($command)) return false;

		if (isset($servers[$server_ip])) {
			$server = $servers[$server_ip];
		} else {
			$server = $servers[$server_ip] = new SteamCondenser\Servers\SourceServer($server_ip);
		}

		if (empty($this->passwords[$server_ip])) {
			//trigger_error('No RCON password on file for server.', E_USER_ERROR);
			CakeLog::write('server', 'No RCON password on file for server.');
			return false;
		}

		try {
			if (!$server->isRconAuthenticated()) {
				$server->rconAuth($this->passwords[$server_ip]);
			}
			$server->rconExec($command);
		} catch (SteamCondenser\Exceptions\RCONNoAuthException $e) {
			//trigger_error('Could not authenticate with the game server.', E_USER_ERROR);
			CakeLog::write('server', "Could not authenticate with the game server. $e");
			return false;
		} catch (Exception $e) {
			//trigger_error('Unknown RCON-related Error.', E_USER_ERROR);
			CakeLog::write('server', "Unknown RCON-related Error. $e");
			return false;
		}

		return true;
	}
}