<?php
App::uses('Component', 'Controller');
App::uses('ConnectionManager', 'Model');

class ServerUtilityComponent extends Component {

	protected $servers = array();
	protected $passwords = array();
	protected $Item = null;

	public function initialize(Controller $controller) {
		App::import('Vendor', 'SteamCondenser', array('file' => 'SteamCondenser/Servers/SourceServer.php'));
		$this->passwords = ConnectionManager::enumConnectionObjects()['gameServer']['passwords'];
	}

	/**
	 * Returns a pointer to the Item model and loads it if it wasn't already.
	 */
	protected function loadItemModel() {

		if (!$this->Item) {
			$this->Item = ClassRegistry::init('Item');
		}

		return $this->Item;
	}

	/**
	 * Builds a complete command with quoted arguments.
	 *
	 * @param string $command the name of the command to send
	 * @param array $args list of arguments to apply to the command
	 * @return string the full command
	 */
	protected function buildCmd($command, $args) {

		if (empty($args)) return '';

		$cmd = $command;

		foreach ($args as $arg) {
			$cmd .= " \"$arg\"";
		}

		return $cmd;
	}

	/**
	 * Build a list of item arguments for the specified server given a user id and list of items. Limit the items to
	 * only the ones that can be used in that server.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param array $list list of arrays that should each have keys for both 'item_id' and 'quantity'
	 * @return string[] list of arguments that each look like "id name"
	 */
	protected function buildItemArgs($server_ip, $user_id, $list) {

		$this->loadItemModel();

		$items = Hash::combine($this->Item->find('all', array(
			'fields' => array(
				'item_id', 'name', 'plural'
			)
		)), '{n}.Item.item_id', '{n}.Item');

		$serverItems = $this->Item->ServerItem->Server->getUsableItems($server_ip);
		$args = array();

		foreach ($list as $item) {
			$item_id = $item['item_id'];
			$quantity = $item['quantity'];
			$name = $quantity > 1 ? $items[$item_id]['plural'] : $items[$item_id]['name'];
			if (in_array($item_id, $serverItems)) {
				//item is usable in this server
				$args[] = "$quantity $name";
			}
		}

		if (!empty($args)) {
			array_unshift($args, $user_id);
		}

		return $args;
	}

	/**
	 * Builds a command for the specified server given a list of items. Shortcut for calling @buildCmd with the result
	 * of @buildItemArgs as the 2nd argument. Limit the items to only the ones that can be used in that server.
	 *
	 * @param string $server_ip
	 * @param string $command
	 * @param int $user_id
	 * @param array $items list of arrays that should each have keys for both 'item_id' and 'quantity'
	 * @return string the full command
	 */
	protected function buildItemCmd($server_ip, $command, $user_id, $items) {

		return $this->buildCmd($command, $this->buildItemArgs($server_ip, $user_id, $items));
	}

	/**
	 * Broadcasts to the specified server that the user has made a purchase, and reloads the player's inventory.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param array $order the details of the order used to build the command
	 * @return bool result of @exec
	 */
	public function broadcastPurchase($server_ip, $user_id, $order) {

		$itemCmd = $this->buildItemCmd($server_ip, "sm_store_broadcast_purchase", $user_id, $order['OrderDetail']);

		$commands = empty($itemCmd) ? '' : array(
			"sm_store_reload_inventory $user_id",
			$itemCmd
		);

		return $this->exec($server_ip, $commands);
	}

	/**
	 * Broadcasts to the specified server that a specific user has purchased CASH with PayPal.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param int $amount the amount of CASH purchased
	 * @return bool result of @exec
	 */
	public function broadcastPurchaseCash($server_ip, $user_id, $amount) {

		return $this->exec($server_ip,
			$this->buildCmd("sm_store_broadcast_purchase", array($user_id, "$amount CASH"))
		);
	}

	/**
	 * Broadcasts to the specified server that a specific user has sent a gift, and reloads the player's inventory.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param array $gift the details of the gift used to build the command
	 * @return bool result of @exec
	 */
	public function broadcastGiftSend($server_ip, $user_id, $gift) {

		$itemCmd = $this->buildItemCmd($server_ip, "sm_store_broadcast_gift_send", $user_id, $gift['GiftDetail']);

		$commands = empty($itemCmd) ? '' : array(
			"sm_store_reload_inventory $user_id",
			$itemCmd
		);

		return $this->exec($server_ip, $commands);
	}

	/**
	 * Broadcasts to the specified server that a specific user has received a gift, and reloads the player's inventory.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param array $gift the details of the gift used to build the command
	 * @return bool result of @exec
	 */
	public function broadcastGiftReceive($server_ip, $user_id, $gift) {

		$itemCmd = $this->buildItemCmd($server_ip, "sm_store_broadcast_gift_receive", $user_id, $gift['GiftDetail']);

		$commands = empty($itemCmd) ? '' : array(
			"sm_store_reload_inventory $user_id",
			$itemCmd
		);

		return $this->exec($server_ip, $commands);
	}

	/**
	 * Broadcasts to the specified server that a specific user has received a reward, and reloads the player's inventory.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param array $reward the details of the reward used to build the command
	 * @return bool result of @exec
	 */
	public function broadcastRewardReceive($server_ip, $user_id, $reward) {

		$itemCmd = $this->buildItemCmd($server_ip, "sm_store_broadcast_reward_receive", $user_id, $reward['RewardDetail']);

		$commands = empty($itemCmd) ? '' : array(
			"sm_store_reload_inventory $user_id",
			$itemCmd
		);

		return $this->exec($server_ip, $commands);
	}

	/**
	 * Commands the specified server to unload a specific user's inventory.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @return bool result of @exec
	 */
	public function unloadUserInventory($server_ip, $user_id) {

		return $this->exec($server_ip, "sm_store_unload_inventory $user_id");
	}

	/**
	 * Broadcasts to the specified server that a specific user wrote a review about an item.
	 *
	 * @param string $server_ip
	 * @param int $user_id
	 * @param int $item_id
	 * @return bool result of @exec
	 */
	public function broadcastReview($server_ip, $user_id, $item_id) {

		$this->loadItemModel();
		$serverItems = $this->Item->ServerItem->Server->getUsableItems($server_ip);

		$command = in_array($item_id, $serverItems) ?
			$this->buildCmd($server_ip, 'sm_store_broadcast_review', array($user_id, $this->Item->read('name', $item_id))) :
			'';

		return $this->exec($server_ip, $command);
	}

	/**
	 * Runs the specified command at the specified server ip. If the command is empty, this will return true, but that
	 * SHOULD only happen if the command is built for a specific server that does not support any of the related items.
	 * Otherwise, the return value will indicate whether the command was successfully sent to the server.
	 *
	 * @param string $server_ip
	 * @param mixed $command prepared command string or array of commands
	 * @return bool whether or not the server could be reached, or true if an empty command
	 */
	public function exec($server_ip, $command = '') {

		if (empty($command)) {
			return true;
		}

		if (is_array($command)) {
			$command = implode($command, ';');
		}

		//create only one server object per server per request
		if (isset($servers[$server_ip])) {
			$server = $servers[$server_ip];
		} else {
			$server = $servers[$server_ip] = new SteamCondenser\Servers\SourceServer($server_ip);
		}

		if (empty($this->passwords[$server_ip])) {
			//trigger_error('No RCON password on file for server.', E_USER_ERROR);
			CakeLog::write('server', "No known RCON password for server $server_ip");
			return false;
		}

		try {

			//authenticate only once
			if (!$server->isRconAuthenticated()) {
				$server->rconAuth($this->passwords[$server_ip]);
			}

			$server->rconExec($command);

		} catch (SteamCondenser\Exceptions\RCONNoAuthException $e) {

			CakeLog::write('server', "Could not authenticate with the game server. $e");
			return false;

		} catch (Exception $e) {

			CakeLog::write('server', "Unknown RCON-related Error. $e");
			return false;
		}

		return true;
	}
}