<?php
App::uses('Component', 'Controller');

/**
 * Class AccountUtilityComponent
 *
 * @property Controller $controller
 *
 * @property AuthComponent $Auth
 * @property AccessComponent $Access
 * @property CookieComponent $Cookie
 * @property RequestHandlerComponent $RequestHandler
 * @property SessionComponent $Session
 *
 * @property SavedLogin $SavedLogin
 * @property SteamPlayer $SteamPlayer
 * @property User $User
 */
class AccountUtilityComponent extends Component {
    const LOGIN_FORCE = 1;
    const LOGIN_SAVE = 2;

    public $components = array('Access', 'Auth', 'Cookie', 'RequestHandler', 'Session');

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

        if ($this->isPlayerBanned($steamid)) {
            $this->Session->setFlash('You are currently banned from our servers and may not use the store.', 'flash_closable', array('class' => 'error'));
            return false;
        }

        $steaminfo = $this->SteamPlayer->getByIds(array($steamid));

        if (empty($steaminfo) && !($flags & self::LOGIN_FORCE)) {
            return false;
        } else {
            $steaminfo = $steaminfo[0];
        }

        // rxg csgo clanid: 103582791434658915

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
            // cleanup
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
     * Queries the Sourcebans database to see if the player is banned and returns true/false.
     *
     * @param int $steamid the 64-bit steamid of the player
     * @return bool whether the player is currently banned
     */
    public function isPlayerBanned($steamid) {

        $steamid32 = SteamID::Parse($steamid, SteamID::FORMAT_STEAMID64)->Format(SteamID::FORMAT_STEAMID32);

        preg_match('/STEAM_1:([0-1]:[0-9]+)/', $steamid32, $matches);
        $steamPattern = 'STEAM_[0-1]:' . $matches[1];

        // query sourcebans
        $db = ConnectionManager::getDataSource('sourcebans');
        $result = $db->rawQuery(
            "SELECT * FROM rxg__bans JOIN rxg__admins on rxg__bans.aid = rxg__admins.aid WHERE rxg__bans.authid RLIKE ':steamPattern' AND (ends > ':time' OR length = 0) AND RemovedOn is null ORDER BY ends limit 1",
            array(
                'steamPattern' => $steamPattern,
                'time' => time()
            )
        );

        return (bool)$result->fetch();
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
        $steamids = array();

        foreach ($accounts as $acc) {
            $steamids[] = $this->SteamID64FromAccountID($acc);
        }

        $steamPlayers = $this->SteamPlayer->getByIds($steamids);
        $members = $this->Access->getMemberInfo($accounts);
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

                $steamid = SteamID::Parse($id, SteamID::FORMAT_AUTO);

                if ($steamid === false) {
                    $result = false;
                } else {
                    $result = $steamid->Format(SteamID::FORMAT_S32);
                }

                $accounts[] = $result;
            }
        }

        return $accounts;
    }
}