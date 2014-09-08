<?php
App::uses('Component', 'Controller');

/**
 * Class AccountUtilityComponent
 *
 * @property Controller $controller
 *
 * @property AclComponent $Acl
 * @property AuthComponent $Auth
 * @property AccessComponent $Access
 * @property CookieComponent $Cookie
 * @property RequestHandlerComponent $RequestHandler
 *
 * @property SavedLogin $SavedLogin
 * @property SteamPlayer $SteamPlayer
 * @property User $User
 */
class AccountUtilityComponent extends Component {
	public $components = array('Acl', 'Access', 'Auth', 'Cookie', 'RequestHandler');

	public function initialize(Controller $controller) {
		$this->controller = $controller;
		$this->SavedLogin = ClassRegistry::init('SavedLogin');
		$this->SteamPlayer = ClassRegistry::init('SteamPlayer');
		$this->User = ClassRegistry::init('User');
	}

	public function loginUser($user_id) {
		return $this->login($this->SteamID64FromAccountID($user_id));
	}

	public function login($steamid, $save = false) {

		$steaminfo = $this->SteamPlayer->getByIds(array($steamid));

		if (empty($steaminfo)) {
			return false;
		} else {
			$steaminfo = $steaminfo[0];
		}

		//csgo clanid: 103582791434658915

		$user_id = $this->AccountIDFromSteamID64($steamid);

		if (!$this->User->exists($user_id)) {
			$this->User->save(array(
				'User' => array(
					'user_id' => $user_id
				)
			));
		}

		$this->Auth->login(array(
			'steamid' => $steamid,
			'user_id' => $user_id,
			'name' => $steaminfo['personaname'],
			'avatar' => $steaminfo['avatar'],
			'avatarmedium' => $steaminfo['avatarmedium'],
			'avatarfull' => $steaminfo['avatarfull'],
			'profileurl' => $steaminfo['profileurl']
		));

		if ($save) {
			$this->saveLogin($user_id);
		}

		return true;
	}

	public function saveLogin($user_id) {

		$code = mt_rand(1,999999);
		$expires = time() + Configure::read('Store.SavedLoginDuration');
		$remoteip = $this->controller->request->clientIp();

		$this->SavedLogin->save(array(
			'user_id' => $user_id,
			'code' => $code,
			'remoteip' => $remoteip,
			'expires' => $expires
		), false);

		$id = $this->SavedLogin->id;

		$this->Cookie->write('saved_login', sprintf("%016d%016d", $id, $code), true, '30 days');
	}

	public function trySavedLogin($updateOnly = false) {

		$cookie = $this->Cookie->read('saved_login');

		if (empty($cookie) || strlen($cookie) != 32) return false;

		$id = substr($cookie, 0, 16);
		$code = substr($cookie, 16, 16);
		$time = time();
		$remoteip = $this->controller->request->clientIp();

		$loginInfo = $this->SavedLogin->find('first', array(
			'conditions' => array(
				'saved_login_id' => $id,
				'code' => $code,
				'remoteip' => $remoteip,
				'expires >' => $time
			)
		));

		if (empty($loginInfo)) {
			//echo 'deleting cookie';
			//Cleanup
			$this->SavedLogin->deleteAll(array('expires <= ' => $time), false);
			$this->Cookie->delete('saved_login');
		} else {
			$user_id = $loginInfo['SavedLogin']['user_id'];
			$loginInfo['SavedLogin']['expires'] = $time + Configure::read('Store.SavedLoginDuration');
			$this->SavedLogin->save($loginInfo, false);
			$this->Cookie->write('saved_login', sprintf("%016d%016d", $id, $code), true, '30 days');

			if (!$updateOnly) {
				$steamid = $this->SteamID64FromAccountID($user_id, false);
				$this->login($steamid);
			}
		}

		return true;
	}

	public function getSteamInfo($id, $isSteamid = false) {

		$steamid = $isSteamid ? $id : $this->SteamID64FromAccountID($id);
		$steamPlayer = $this->SteamPlayer->getByIds(array($steamid));

		if (empty($steamPlayer)) return array();

		$player = $steamPlayer[0];
		$player['name'] = $player['personaname'];

		return $player;
	}

	public function getIndexedSteamInfo($accounts) {

		if (empty($accounts)) return array();

		$accounts = array_unique($accounts);
		$user = $this->Auth->user();

		if (count($accounts) == 1 && $accounts[0] == $user['user_id']) {
			return array($user['user_id'] => array_merge($user, array(
				'member' => $this->Access->checkIsMember($user['user_id'])
			)));
		}

		$steamids = array();

		foreach ($accounts as $acc) {
			$steamids[] = $this->SteamID64FromAccountID($acc);
		}

		$i = 0;
		$steamPlayers = array();
		//Steam API has 100 player per call limit
		while ($batch = array_slice($steamids, $i++ * 100, 100)) {
			$steamPlayers = array_merge($steamPlayers, $this->SteamPlayer->getByIds($batch));
		}

		$members = $this->Access->getMemberStatus($accounts);
		$players = array();

		foreach ($steamPlayers as $player) {
			$user_id = $this->AccountIDFromSteamID64($player['steamid']);
			$players[$user_id] = array(
				'steamid' => $player['steamid'],
				'name' => $player['personaname'],
				'avatar' => $player['avatar'],
				'avatarmedium' => $player['avatarmedium'],
				'avatarfull' => $player['avatarfull'],
				'profile' => $player['profileurl'],
				'member' => $members[$user_id]
			);
		}

		return $players;
	}

	public function syncSourcebans() {

		$aro = $this->Acl->Aro;

		$groupIds = $aro->find('list', array(
			'fields' => array(
				'alias', 'id'
			),
			'conditions' => array(
				'foreign_key is null'
			)
		));

		$savedAdminIds = $aro->find('list', array(
			'fields' => array(
				'foreign_key', 'id'
			),
			'conditions' => array(
				'foreign_key is not null'
			)
		));

		$db = ConnectionManager::getDataSource('sourcebans');
		$result = $db->rawQuery("SELECT authid, user, srv_group FROM rxg__admins where srv_group is not null");

		$newAdmins = array();

		while($row = $result->fetch()) {

			$user_id = $this->AccountIDFromSteamID32($row['authid']);
			$groupName = Inflector::singularize($row['srv_group']);

			if (empty($groupIds[$groupName])) {
				$groupName = 'Member';
			}

			$group_id = $groupIds[$groupName];

			$data = array(
				'parent_id' => $group_id,
				'model' => 'User',
				'foreign_key' => $user_id,
				'alias' => $row['user']
			);

			$isNewAdmin = false;

			if (isset($savedAdminIds[$user_id])) {
				$data['id'] = $savedAdminIds[$user_id];
			} else {
				$isNewAdmin = true;
			}

			$aro->create();
			$aro->save($data);

			if ($isNewAdmin) {
				$newAdmins[] = $aro->id;
			}

			$aro->clear();

			unset($savedAdminIds[$user_id]);
		}

		$demoteAdmins = array();

		if (!empty($savedAdminIds)) {
			//Demote leftover admins who weren't in sourcebans database
			$demoteAdmins = Hash::map($savedAdminIds, '{n}', function($id){
				return array('Aro.id' => $id);
			});

			$aro->updateAll(
				array('parent_id' => 1), // Member
				array('OR' => $demoteAdmins)
			);
		}

		/*Configure::store('Permissions', 'default', array(
			'Store.AdminsLastUpdated' => time()
		));*/

		return array(
			'added' => $newAdmins,
			'demoted' => $demoteAdmins
		);
	}

	public function permissions() {

		$aco = $this->Acl->Aco;
		$aro = $this->Acl->Aro;

		$aro->query('ALTER TABLE Aros auto_increment = 1');

		$objects = array(
			array('alias' => 'Cache'),
			array('alias' => 'Chats'),
			array('alias' => 'Items'),
			array('alias' => 'Permissions'),
			array('alias' => 'QuickAuth'),
			array('alias' => 'Receipts'),
			array('alias' => 'Reviews'),
			array('alias' => 'Rewards'),
			array('alias' => 'Stock'),
			array('alias' => 'Users')
		);

		foreach ($objects as $object) {
			$aco->create();
			$aco->save($object);
		}

		$groups = array(
			array(
				'alias' => 'Member',
			),
			array(
				'alias' => 'Basic Admin',
				'parent_id' => 1
			),
			array(
				'alias' => 'Full Admin',
				'parent_id' => 2
			),
			array(
				'alias' => 'Advisor',
				'parent_id' => 3
			),
			array(
				'alias' => 'Captain',
				'parent_id' => 4
			),
			array(
				'alias' => 'Cabinet',
				'parent_id' => 5
			),
			array(
				'alias' => 'Director',
				'parent_id' => 6
			),
		);

		foreach ($groups as $group) {
			$aro->create();
			$aro->save(array_merge($group, array('model' => null)));
		}
	}

	public function AccountIDFromSteamID64( $steamid64 ) {
		$steamid64 = bcsub( $steamid64, "76561197960265728" );
		if( bccomp( $steamid64, "2147483648" ) >= 0 ) {
			$steamid64 = bcsub( $steamid64, "4294967296" );
		}
		return (int)$steamid64;
	}

	public function SteamID64FromAccountID( $accountid ) {
		$num = (string)$accountid;
		if( $accountid < 0 ) {
			$num = bcadd( $num, "4294967296" );
		}
		$num = bcadd( $num, "76561197960265728", 0 );
		//$accountid += 0x0110000100000000 ;
		return $num;
	}

	public function AccountIDFromSteamID32( $steamid32 ) {

		return preg_replace_callback( '/STEAM_[0-1]:([0-1]):([0-9]+)/', function($matches){
			return (int)($matches[2]) * 2 + (int)($matches[1]);
		}, $steamid32 );
	}

	public function getContents($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	public function resolveVanityUrl( $vanityurl ) {

		$apiKey = ConnectionManager::enumConnectionObjects()['steam']['apikey'];
		$url = "http://api.steampowered.com/ISteamUser/ResolveVanityUrl/v0001/?key={$apiKey}&vanityurl={$vanityurl}&format=json";

		$response = json_decode($this->getContents($url), true);

		return empty($response) ? '' : $response['response']['steamid'];
	}

	public function resolveAccountIDs( $ids ) {

		$accounts = array();

		foreach ( $ids as $str ) {

			if ( $str == 'me') {

				$accounts[] = $this->Auth->user('user_id');

			} else if ( preg_match( '/(STEAM_[0-1]:[0-1]:[0-9]+)/', $str, $matches ) ) {

				//searches entire line (works with status2)
				//steamid 32 match
				$accounts[] = $this->AccountIDFromSteamID32( $matches[1] );

			} else if ( preg_match( '/\[U:[0-1]:([0-9]+)\]/', $str, $matches ) ) {

				$accounts[] = $matches[1];

			} else if ( preg_match( '/^765(?P<idmatch>[0-9]+)\/?$/', $str, $matches ) == 1 ) {

				//steamid 64 match
				$accounts[] = $this->AccountIDFromSteamID64( $matches['idmatch'] );

			} else if ( preg_match( '/^(http:\/\/)*(www.)*steamcommunity.com\/profiles\/(?P<idmatch>[0-9]+)\/?$/', $str, $matches ) == 1 ) {

				//community url match (steamid 64)
				$accounts[] = $this->AccountIDFromSteamID64( $matches['idmatch']);

			} else if ( preg_match( '/^((http:\/\/)*(www.)*steamcommunity.com\/id\/)*(?P<idmatch>[a-zA-Z0-9_]+)\/?$/', $str, $matches ) == 1 ) {

				//vanity url match
				$acc = $this->resolveVanityUrl( $matches['idmatch'] );
				$accounts[] = empty($acc) ? "NOT FOUND: $str" : $this->AccountIDFromSteamID64( $acc );

			}
		}

		return $accounts;
	}
}