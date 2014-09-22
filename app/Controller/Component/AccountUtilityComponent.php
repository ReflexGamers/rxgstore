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
 * @property Model/User $User
 */
class AccountUtilityComponent extends Component {
	public $components = array('Acl', 'Access', 'Auth', 'Cookie', 'RequestHandler');

	public function initialize(Controller $controller) {
		$this->controller = $controller;
		$this->SavedLogin = ClassRegistry::init('SavedLogin');
		$this->SteamPlayer = ClassRegistry::init('SteamPlayer');
		$this->User = ClassRegistry::init('User');
	}

	public function loginUser($user_id, $options = array()) {
		return $this->login($this->SteamID64FromAccountID($user_id), $options);
	}

	public function login($steamid, $options = array()) {

		$steaminfo = $this->SteamPlayer->getByIds(array($steamid));

		if (empty($steaminfo) && (empty($options['force']) || !$options['force'])) {
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
			'profile' => $steaminfo['profileurl']
		));

		if (!empty($options['save']) && $options['save']) {
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
		$player['member'] = $this->Access->checkIsMember($isSteamid ? $this->AccountIDFromSteamID64($id) : $id);

		return $player;
	}

	public function getIndexedSteamInfo($accounts) {

		if (empty($accounts)) return array();

		$accounts = array_unique($accounts);
		/*$user = $this->Auth->user();

		if (count($accounts) == 1 && !empty($user) && $accounts[0] == $user['user_id']) {
			$user_id = $user['user_id'];
			$members = $this->Access->getMembers($user_id);
			return array($user['user_id'] => array_merge($user, array(
				'member' => !empty($members[$user_id]),
				'division' => !empty($members[$user_id]['division']) ? $members[$user_id]['division'] : ''
			)));
		}*/

		$steamids = array();

		foreach ($accounts as $acc) {
			$steamids[] = $this->SteamID64FromAccountID($acc);
		}

		$steamPlayers = $this->SteamPlayer->getByIds($steamids);
		$members = $this->Access->getMembers($accounts);
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
				'member' => !empty($members[$user_id]),
				'division' => !empty($members[$user_id]['division']) ? $members[$user_id]['division'] : ''
			);
		}

		return $players;
	}

	public function syncSourcebans() {

		$aro = $this->Acl->Aro;

		//Group ids indexed by name
		$groupNames = $aro->find('list', array(
			'fields' => array(
				'id', 'alias'
			),
			'conditions' => array(
				'foreign_key is null'
			),
			'recursive' => -1
		));

		$groupIds = array_flip($groupNames);
		$memberGroupId = $groupIds['Member'];

		//Currently saved admins indexed by user_id
		$savedMembers = Hash::combine($aro->find('all', array(
			'fields' => array(
				'id', 'foreign_key', 'parent_id', 'alias', 'division'
			),
			'conditions' => array(
				'foreign_key is not null'
			),
			'recursive' => -1
		)), '{n}.Aro.foreign_key', '{n}.Aro');


		//Get sourcebans data
		$db = ConnectionManager::getDataSource('sourcebans');
		$result = $db->rawQuery("SELECT authid, user, srv_group FROM rxg__admins where srv_group is not null");

		//Sourcebans admins indexed by user_id
		$sbAdmins = array();

		while ($row = $result->fetch()) {

			$user_id = $this->AccountIDFromSteamID32($row['authid']);
			$groupName = $row['srv_group'];

			if (empty($groupName) || empty($groupIds[$groupName])) {
				$groupName = 'Member';
			}

			$sbAdmins[$user_id] = array(
				'alias' => $row['user'],
				'parent_id' => $groupIds[$groupName]
			);
		}


		//Get forum data
		$db = ConnectionManager::getDataSource('forums');
		$config = Configure::read('Store.Forums');

		$groups = implode(',', $config['MemberGroups']);
		$divisions = $config['Divisions'];

		$result = $db->rawQuery("SELECT steamid, user.username, steamuser.steamid, userfield.field5 FROM steamuser JOIN user ON steamuser.userid = user.userid JOIN userfield on userfield.userid = user.userid WHERE user.usergroupid IN ($groups)");

		//Linked members/admins indexed by user_id
		$forumMembers = array();

		while ($row = $result->fetch()) {
			$user_id = $this->AccountIDFromSteamID64($row['steamid']);
			$division = empty($divisions[$row['field5']]) ? '' : $divisions[$row['field5']];
			$forumMembers[$user_id] = array(
				'alias' => $row['username'],
				'division' => $division
			);
		}

		$insertAdmins = array_diff_key($sbAdmins, $savedMembers);
		$insertMembers = array_diff_key($forumMembers, $savedMembers, $insertAdmins);

		$results = array(
			'added' => array(),
			'updated' => array(),
			'removed' => array()
		);

		CakeLog::write('permsync', 'Performed Sync.');

		//Update/remove existing records
		foreach ($savedMembers as $user_id => $data) {

			$forumData = !empty($forumMembers[$user_id]) ? $forumMembers[$user_id] : '';
			$division = !empty($forumData['division']) ? $forumData['division'] : '';

			if (empty($sbAdmins[$user_id])) {

				//not in sourcebans db
				if (empty($forumData)) {

					//Not a linked member either so remove
					$steamid = $this->SteamID64FromAccountID($data['foreign_key']);
					$division = !empty($data['division']) ? $data['division'] : 'No Division';
					CakeLog::write('permsync', " - deleted {$groupNames[$data['parent_id']]}: '{$data['alias']}' / $steamid / $division");

					$aro->clear();
					$aro->delete($data['id']);
					$results['removed'][] = $data;

				} else if ($data['parent_id'] != $memberGroupId || $data['alias'] != $forumData['alias'] || $data['division'] != $division) {

					//Linked member, not admin, needs updating/demoting
					$steamid = $this->SteamID64FromAccountID($data['foreign_key']);
					CakeLog::write('permsync', " - updated '{$data['alias']}' / $steamid");

					if ($data['parent_id'] != $memberGroupId) {
						CakeLog::write('permsync', "   - updated rank: {$groupNames[$data['parent_id']]} -> $groupNames[$memberGroupId]");
					}

					if ($data['alias'] != $forumData['alias']) {
						CakeLog::write('permsync', "   - updated alias: '{$data['alias']}' -> '{$forumData['alias']}'");
					}

					if ($data['division'] != $division) {
						$oldDivision = !empty($data['division']) ? $data['division'] : 'none';
						CakeLog::write('permsync', "   - updated division: $oldDivision -> $division");
					}

					$data['parent_id'] = $memberGroupId;
					$data['alias'] = $forumData['alias'];
					$data['division'] = $division;
					$aro->clear();
					$aro->save($data);
					$results['updated'][] = $data;
				}

			} else {

				//admin is in sourcebans db
				$adminData = $sbAdmins[$user_id];

				if ($data['parent_id'] != $adminData['parent_id'] || $data['alias'] != $adminData['alias'] || $data['division'] != $division) {

					//needs updating
					$steamid = $this->SteamID64FromAccountID($data['foreign_key']);
					CakeLog::write('permsync', " - updated '{$data['alias']}' / $steamid");

					if ($data['parent_id'] != $adminData['parent_id']) {
						CakeLog::write('permsync', "   - updated rank: {$groupNames[$data['parent_id']]} -> {$groupNames[$adminData['parent_id']]}");
					}

					if ($data['alias'] != $adminData['alias']) {
						CakeLog::write('permsync', "   - updated alias: '{$data['alias']}' -> '{$forumData['alias']}'");
					}

					if ($data['division'] != $division) {
						$oldDivision = !empty($data['division']) ? $data['division'] : 'none';
						CakeLog::write('permsync', "   - updated division: $oldDivision -> $division");
					}

					$data['alias'] = $adminData['alias'];
					$data['parent_id'] = $adminData['parent_id'];
					$data['division'] = $division;
					$aro->clear();
					$aro->save($data);
					$results['updated'][] = $data;
				}
			}
		}

		//Insert new admins
		foreach ($insertAdmins as $user_id => $data) {

			if (!empty($forumMembers[$user_id])) {
				$data['division'] = $forumMembers[$user_id]['division'];
			}

			$division = !empty($data['division']) ? $data['division'] : 'No Division';
			$rank = $groupNames[$data['parent_id']];

			$steamid = $this->SteamID64FromAccountID($user_id);
			CakeLog::write('permsync', " - added $rank: '{$data['alias']}' / $steamid / $division");

			$data['model'] = 'User';
			$data['foreign_key'] = $user_id;
			$aro->clear();
			$aro->save($data);
			$results['added'][] = $data;
		}

		//Insert new members
		foreach ($insertMembers as $user_id => $data) {

			$division = !empty($data['division']) ? $data['division'] : 'No Division';

			$steamid = $this->SteamID64FromAccountID($user_id);
			CakeLog::write('permsync', " - added Member: '{$data['alias']}' / $steamid / $division");

			$data['model'] = 'User';
			$data['foreign_key'] = $user_id;
			$data['parent_id'] = $memberGroupId;
			$aro->clear();
			$aro->save($data);
			$results['added'][] = $data;
		}

		return $results;
	}

	public function syncMembers() {

		$db = ConnectionManager::getDataSource('forums');
		$config = Configure::read('Store.Forums');

		$groups = implode(',', $config['MemberGroups']);
		$divisions = $config['Divisions'];

		$result = $db->rawQuery("SELECT steamid FROM steamuser.steamid, userfield.field5 JOIN user ON steamuser.userid = user.userid JOIN userfield on userfield.userid = user.userid WHERE user.usergroupid IN ($groups)");

		$linkedMembers = array();

		while($row = $result->fetch()) {
			$linkedMembers[] = $this->AccountIDFromSteamID64($row['steamid']);
		}

		$aro = $this->Acl->Aro;

		$savedMembers = Hash::extract($aro->find('all', array(
			'fields' => array(
				'foreign_key'
			),
			'conditions' => array(
				'foreign_key is not null'
			),
			'recursive' => -1
		)), '{n}.Aro.foreign_key');

		$addMembers = array_diff($linkedMembers, $savedMembers);
		$removeMembers = array_diff($savedMembers, $linkedMembers);

		if (empty($addMembers) && empty($removeMembers)) {
			return array();
		}

		$memberGroup = $aro->find('first', array(
			'fields' => array('id'),
			'conditions' => array(
				'alias' => 'Member'
			),
			'recursive' => -1
		));

		if (empty($memberGroup['Aro']['id'])) {
			return array();
		}

		$memberGroupId = $memberGroup['Aro']['id'];

		foreach ($addMembers as $user_id) {
			$aro->clear();
			$aro->save(array(
				'parent_id' => $memberGroupId,
				'model' => 'User',
				'foreign_key' => $user_id
			));
		}

		foreach ($removeMembers as $user_id) {
			$aro->clear();
			$aro->delete(array(
				'foreign_key' => $user_id
			));
		}

		return array(
			'added' => $addMembers,
			'removed' => $removeMembers
		);
	}

	public function permissions() {

		$aco = $this->Acl->Aco;
		$aro = $this->Acl->Aro;

		$aro->query('ALTER TABLE aros auto_increment = 1');

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