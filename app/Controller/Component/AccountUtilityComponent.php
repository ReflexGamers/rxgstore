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
 * @property PermissionsComponent $Permissions
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
    const LOGIN_SKIP_BAN_CHECK = 4;

    public $components = array('Access', 'Auth', 'Cookie', 'Permissions', 'RequestHandler', 'Session');

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
     * @param int $user_id the signed 32-bit steamid of the user to login
     * @param int $flags login flags
     * @return bool success of login attempt
     */
    public function loginUser($user_id, $flags = 0) {
        return $this->login($this->SteamID64FromAccountID($user_id), $flags);
    }

    /**
     * Logs in a player by steamid.
     *
     * @param int $steamid the 64-bit steamid of the user to login
     * @param int $flags login flags
     * @return bool success of login attempt
     */
    public function login($steamid, $flags = 0) {

        if (!($flags & self::LOGIN_SKIP_BAN_CHECK) && $this->Permissions->isPlayerBanned($steamid)) {
            $this->Session->setFlash('You are currently banned and may not use the store.', 'flash_closable', array('class' => 'error'));
            $this->SavedLogin->deleteForUser($this->AccountIDFromSteamID64($steamid));
            $this->Cookie->delete('saved_login');
            return false;
        }

        $steaminfo = $this->SteamPlayer->getByIds(array($steamid));

        if (empty($steaminfo) && !($flags & self::LOGIN_FORCE)) {
            return false;
        } else {
            $steaminfo = $steaminfo[0];
        }

        // rxg csgo clan id: 103582791434658915

        $user_id = $this->AccountIDFromSteamID64($steamid);

        if (!$this->User->exists($user_id)) {
            $this->User->save(array(
                'User' => array(
                    'user_id' => $user_id
                )
            ));
        }

        $loginSuccess = $this->Auth->login(array(
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

        if (!$loginSuccess) {
            CakeLog::write('login', "Login failed for $steamid.");
        }

        return $loginSuccess;
    }

    /**
     * Saves the user's login in the database and gives them a browser cookie.
     *
     * @param int $user_id
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
     * @param bool $updateOnly whether to attempt to login the user. set false if already logged in for sure
     * @return bool success of attempt (false if expired cookie or no cookie found)
     */
    public function trySavedLogin($updateOnly = false) {

        $cookie = $this->Cookie->read('saved_login');

        if (empty($cookie) || strlen($cookie) != 32) return false;

        $id = substr($cookie, 0, 16);
        $code = substr($cookie, 16, 16);
        $remoteip = $this->controller->request->clientIp();

        $loginInfo = $this->SavedLogin->findActive($id, $code, $remoteip);

        if (empty($loginInfo)) {

            // not found or expired
            $this->SavedLogin->deleteAllExpired();
            $this->Cookie->delete('saved_login');
            CakeLog::write('saved_login', "Saved Login token $id-$code for $remoteip not found or expired.");

            return false;

        } else {

            // update record and cookie
            $this->SavedLogin->updateRecord($loginInfo);
            $this->Cookie->write('saved_login', sprintf('%016d%016d', $id, $code), true, '30 days');

            // log the user in
            if (!$updateOnly) {
                $this->loginUser($loginInfo['SavedLogin']['user_id']);
                CakeLog::write('saved_login', "Saved Login token $id-$code successfully used.");
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
     * the players' data where each player's user_id is the key for their corresponding info.
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
     * @param int $steamid64
     * @return false|string
     */
    public function AccountIDFromSteamID64($steamid64) {
        return SteamID::Parse($steamid64, SteamID::FORMAT_STEAMID64)->Format(SteamID::FORMAT_S32);
    }

    /**
     * Converts a signed 32-bit SteamID to the 64-bit format.
     *
     * @param int $accountid
     * @return false|string
     */
    public function SteamID64FromAccountID($accountid) {
        return SteamID::Parse($accountid, SteamID::FORMAT_S32)->Format(SteamID::FORMAT_STEAMID64);
    }

    /**
     * Converts a 32-bit SteamID (string) to the signed 32-bit format.
     *
     * @param int $steamid32
     * @return false|string
     */
    public function AccountIDFromSteamID32($steamid32) {
        return SteamID::Parse($steamid32, SteamID::FORMAT_STEAMID32)->Format(SteamID::FORMAT_S32);
    }

    /**
     * Converts a player's vanity URL to a signed 32-bit SteamID.
     *
     * @param string $vanityUrl
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
     * @param array $failed list of SteamIDs or lines that could not be resolved
     * @return array of account ids (aka user_id)
     */
    public function resolveAccountIDs($ids, &$failed) {

        // api key for vanity URL check if no other tests work
        $apiKey = ConnectionManager::enumConnectionObjects()['steam']['apikey'];
        SteamID::SetSteamAPIKey($apiKey);

        $accounts = array();

        foreach ($ids as $id) {

            if ($id == 'me') {
                $accounts[] = $this->Auth->user('user_id');
            } else {

                // parse steamid32 or steamid3 out of line first
                if (preg_match('/(\[U:1:[0-9]+\])/', $id, $matches) || preg_match('/(STEAM_[0-1]:[0-1]:[0-9]+)/', $id, $matches)) {
                    $id = $matches[1];
                }

                try {
                    $steamid = SteamID::Parse($id, SteamID::FORMAT_AUTO, true);

                    if ($steamid === false) {
                        $failed[] = $id;
                    } else {
                        $accounts[] = $steamid->Format(SteamID::FORMAT_S32);
                    }

                } catch (SteamIDResolutionException $e) {

                    // probably not found but could be other failure
                    $failed[] = $id;

                    CakeLog::write('steamid', "{$e->reason} - $id");
                }
            }
        }

        return $accounts;
    }
}