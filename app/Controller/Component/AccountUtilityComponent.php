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
	const LOGIN_FORCE = 1;
	const LOGIN_SAVE = 2;

	public $components = array('Acl', 'Access', 'Auth', 'Cookie', 'RequestHandler');

	public function initialize(Controller $controller) {
		$this->controller = $controller;
		$this->SavedLogin = ClassRegistry::init('SavedLogin');
		$this->SteamPlayer = ClassRegistry::init('SteamPlayer');
		$this->User = ClassRegistry::init('User');
	}

	/**
	 * Logs in the current user to the account specified by user_id. Different from login(), it uses user_id instead of
	 * steamid. This is used by QuickAuth.
	 *
	 * @param $user_id
	 * @param int $flags
	 * @return bool success of login attempt
	 */
	public function loginUser($user_id, $flags = 0) {
		return $this->login($this->SteamID64FromAccountID($user_id), $flags);
	}

	/**
	 * Logs in a player by steamid.
	 *
	 * @param $steamid
	 * @param int $flags
	 * @return bool success of login attempt
	 */
	public function login($steamid, $flags = 0) {

		$steaminfo = $this->SteamPlayer->getByIds(array($steamid));

		if (empty($steaminfo) && !($flags & self::LOGIN_FORCE)) {
			return false;
		} else {
			$steaminfo = $steaminfo[0];
		}

		//rxg csgo clanid: 103582791434658915

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

		if ($flags & self::LOGIN_SAVE) {
			$this->saveLogin($user_id);
		}

		return true;
	}

	/**
	 * Saves the user's login in the database and give them a cookie.
	 *
	 * @param $user_id
	 */
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

	/**
	 * Checks for a saved login in the database and tries to match it with the user's cookie to log them in. If already
	 * logged in, it just updates the record and sends them a new cookie.
	 *
	 * @param bool $updateOnly
	 * @return bool success of attempt (false if expired cookie or no cookie found)
	 */
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

	/**
	 * Gets steam info for a player by user_id or steamid.
	 *
	 * @param string $id user_id or steamid depending on $isSteamid param
	 * @param bool $isSteamid whether the id is a steamid or a user_id
	 * @return array player data
	 */
	public function getSteamInfo($id, $isSteamid = false) {

		$steamid = $isSteamid ? $id : $this->SteamID64FromAccountID($id);
		$steamPlayer = $this->SteamPlayer->getByIds(array($steamid));

		if (empty($steamPlayer)) return array();

		$player = $steamPlayer[0];
		$player['name'] = $player['personaname'];
		$player['member'] = $this->Access->checkIsMember($isSteamid ? $this->AccountIDFromSteamID64($id) : $id);

		return $player;
	}

	/**
	 * Gets player data for all of the players specified by their account ids (user_id). Result is an indexed array of
	 * players' data where each player's user_id the key for their corresponding info.
	 *
	 * If all the players have recent data in the cache, it will be pulled from there instead of through an API call.
	 *
	 * @param array $accounts an array of account ids (aka user_id)
	 * @return array player data indexed by user_id
	 */
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

	/**
	 * Synchronizes the permission tables with Sourcebans and the forums.
	 *
	 * @return array result of sync with keys 'added', 'updated' and 'removed' as sub-arrays with the changed admin data
	 */
	public function syncPermissions() {

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

	/**
	 * Initializes permissions. Empty the acos, aros and acos_aros tables before running.
	 */
	public function initPermissions() {

		$aco = $this->Acl->Aco;
		$aro = $this->Acl->Aro;

		$aro->query('ALTER TABLE aros auto_increment = 1');

		$objects = array(
			array('alias' => 'Cache'),
			array('alias' => 'Chats'),
			array('alias' => 'Items'),
			array('alias' => 'Logs'),
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

	/**
	 * Converts a 64-bit SteamID to the signed 32-bit format.
	 *
	 * @param $steamid64
	 * @return false|string
	 */
	public function AccountIDFromSteamID64($steamid64) {
		return SteamID::Parse($steamid64, SteamID::FORMAT_STEAMID64)->Format(SteamID::FORMAT_S32);
	}

	/**
	 * Converts a signed 32-bit SteamID to the 64-bit format.
	 *
	 * @param $accountid
	 * @return false|string
	 */
	public function SteamID64FromAccountID($accountid) {
		return SteamID::Parse($accountid, SteamID::FORMAT_S32)->Format(SteamID::FORMAT_STEAMID64);
	}

	/**
	 * Converts a 32-bit SteamID (string) to the signed 32-bit format.
	 *
	 * @param $steamid32
	 * @return false|string
	 */
	public function AccountIDFromSteamID32($steamid32) {
		return SteamID::Parse($steamid32, SteamID::FORMAT_STEAMID32)->Format(SteamID::FORMAT_S32);
	}

	/**
	 * Converts a player's vanity URL to a signed 32-bit SteamID.
	 *
	 * @param $vanityUrl
	 */
	public function AccountIDFromVanityUrl($vanityUrl) {
		$apiKey = ConnectionManager::enumConnectionObjects()['steam']['apikey'];
		SteamID::SetSteamAPIKey($apiKey);
		SteamID::Parse($vanityUrl, SteamID::FORMAT_VANITY)->Format(SteamID::FORMAT_S32);
	}

	/**
	 * Parses a list of SteamIDs and returns an array of account ids (aka user_id). Supports status printouts as long as
	 * the SteamIDs are in the SteamID32 or SteamID3 formats.
	 *
	 * @param array $ids list of SteamIDs or lines to parse them from
	 * @return array of account ids (aka user_id)
	 */
	public function resolveAccountIDs($ids) {

		$accounts = array();

		foreach ($ids as $id) {

			if ($id == 'me') {
				$accounts[] = $this->Auth->user('user_id');
			} else {

				// parse steamid32 or steamid3 out of line first
				if (preg_match('/(\[U:1:[0-9]+\])/', $id, $matches) || preg_match('/(STEAM_[0-1]:[0-1]:[0-9]+)/', $id, $matches)) {
					$id = $matches[1];
				}

				$accounts[] = SteamID::Parse($id, SteamID::FORMAT_AUTO)->Format(SteamID::FORMAT_S32);
			}
		}

		return $accounts;
	}
}