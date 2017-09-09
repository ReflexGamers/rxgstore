<?php
App::uses('AppController', 'Controller');

/**
 * QuickAuth Controller
 *
 * @property AclComponent $Acl
 * @property QuickAuth $QuickAuth
 * @property PaginatorComponent $Paginator
 *
 * Magic Properties (for inspection):
 * @property Server $Server
 * @property SteamPlayerCache $SteamPlayerCache
 */
class QuickAuthController extends AppController {
    public $components = array('Acl', 'Paginator', 'RequestHandler');
    public $helpers = array('Html', 'Form', 'Js', 'Time');

    /**
     * Shows the QuickAuth records page for admins.
     */
    public function view() {

        if (!$this->Access->check('QuickAuth', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        $this->set(array(
            'tokenExpire' => Configure::read('Store.QuickAuth.TokenExpire'),
            'dayAgo' => time() - 86400,
            'weekAgo' => time() - 604800
        ));

        $this->loadShoutbox();
        $this->records();
    }

    /**
     * Returns json data for overall QuickAuth usage by server.
     *
     * @param int $since how far back to get data
     */
    public function totals($since = 0) {

        if (!$this->Access->check('QuickAuth', 'read')) {
            $this->autoRender = false;
            return;
        }

        $this->set(array(
            'data' => $this->QuickAuth->getTotalsByServer($since),
            '_serialize' => array('data')
        ));
    }

    /**
     * Shows a page of QuickAuth records. Called by view action or via ajax directly.
     */
    public function records() {

        if (!$this->Access->check('QuickAuth', 'read')) {
            if ($this->request->is('ajax')) {
                $this->autoRender = false;
            } else {
                $this->redirect($this->referer());
            }
            return;
        }

        $this->Paginator->settings = array(
            'QuickAuth' => array(
                'limit' => 25,
            )
        );

        $quickauth = Hash::extract($this->Paginator->paginate('QuickAuth'), '{n}.QuickAuth');
        $this->addPlayers($quickauth, '{n}.user_id');

        $this->loadModel('Server');
        $servers = Hash::combine($this->Server->find('all', array(
            'fields' => array(
                'server_ip', 'name', 'short_name'
            ),
            'conditions' => array(
                'server_ip is not null'
            )
        )), '{n}.Server.server_ip', '{n}.Server');

        $this->set(array(
            'servers' => $servers,
            'quickauth' => $quickauth,
            'pageModel' => $this->QuickAuth->name,
            'pageLocation' => array('controller' => 'QuickAuth', 'action' => 'records')
        ));
    }

    /**
     * Handles quick authentication when a person's steamid is known such as in a game server.
     *
     * How to use on a remote server:
     *
     *   1) Connect remotely to the store database and save a record to the quick_auth table with the following fields:
     *
     *        token: randomly generated positive integer
     *        user_id: the player's signed 32-bit SteamID - for SourceMod use GetSteamAccountID()
     *        server: the server ip they are coming from (or a string like 'forums' if coming from a website)
     *        is_member: optional boolean for whether you know the user is an rxg member (e.g., csgo rxg tag)
     *
     *   2) Use LAST_INSERT_ID() to get the id of the new record
     *
     *   3) Send the user to /quickauth with the following query parameters:
     *
     *        id: the record id obtained from step #2
     *        token: the token you saved in step #1
     *        game: optional abbreviated name of the game mod (e.g., 'csgo', 'tf2')
     *        page: optional URL to redirect to after logging in
     *
     * The random token does not have to be unique because when paired with the unique id, the combination will be both
     * unique and random.
     *
     * Once the user is sent to the URL, the rest of the login process will be handled by this action and the user will
     * be sent to the main store page. If the user is coming from a game server that requires a popup to properly show
     * the window (e.g., a CS:GO server), the 'game' query parameter specified in the URL must be equal to one of the
     * values in the Store.QuickAuth.PopupFromGames array in the rxgstore.php config or it will not work.
     *
     * Note: You do not need to worry about timezones and clock synchronization because the date field is set by the
     * database when you insert the token as long as your script does not specify it. To configure the amount of time
     * tokens are usable, edit Store.QuickAuth.TokenExpire in the rxgstore.php config.
     *
     * Note: A bug in the Source engine causes the page to be requested twice so re-using tokens is silently ignored.
     */
    public function auth() {

        $params = $this->request->query;

        // default redirect location (main store page)
        $redirLoc = array('controller' => 'Items', 'action' => 'index');

        if (empty($params)) {
            CakeLog::write('quickauth', "QuickAuth attempted with no query string.");
            $this->redirect($redirLoc);
            return;
        }

        // set redirect to specific page
        if (!empty($params['page'])) {
            $redirLoc = $params['page'];
        }

        if (empty($params['id']) || empty($params['token'])) {
            CakeLog::write('quickauth', "QuickAuth attempted with incomplete query string.");
            $this->redirect($redirLoc);
            return;
        }

        $user = $this->Auth->user('user_id');
        $config = Configure::read('Store.QuickAuth');

        $tokenId = $params['id'];
        $tokenValue = $params['token'];

        $auth = Hash::extract($this->QuickAuth->find('first', array(
            'conditions' => array(
                'quick_auth_id' => $tokenId,
                'token' => $tokenValue
            )
        )), 'QuickAuth');

        if (!empty($auth)) {

            // silently ignore if already redeemed (probably double request)
            if (!$auth['redeemed']) {

                $user_id = $auth['user_id'];

                // was the user's steam data precached
                $this->loadModel('SteamPlayerCache');
                $auth['cached'] = $this->SteamPlayerCache->getPlayerStatus($user_id);

                // set token as redeemed and save
                $auth['redeemed'] = true;
                $this->QuickAuth->save($auth);

                // if confirmed member, promote to member if not already set
                if ($auth['is_member'] && !$this->Access->checkIsMember($user_id)) {

                    CakeLog::write('quickauth', "Promoted user $user_id to member.");
                    $this->Acl->Aro->save(array(
                        'parent_id' => 1,
                        'model' => 'User',
                        'foreign_key' => $user_id
                    ));
                }

                // do login process if not already logged in
                if (empty($user)) {

                    $diff = strtotime($auth['date']) + $config['TokenExpire'] - time();

                    if ($diff < 0) {

                        // token expired
                        $diff = abs($diff);
                        CakeLog::write('quickauth', "Attempted usage of token $tokenId-$tokenValue which expired $diff seconds ago.");
                        $this->Flash->set('Authentication token expired. Please contact an administrator.', ['params' => ['class' => 'error']]);

                    } else {

                        $flags = AccountUtilityComponent::LOGIN_FORCE;

                        if (!empty($params['game']) && in_array($params['game'], $config['SkipBanCheckFromGames'])) {
                            $flags |= AccountUtilityComponent::LOGIN_SKIP_BAN_CHECK;
                        }

                        // try to login user
                        if (!$this->AccountUtility->login($user_id, $flags)) {

                            // failed to login user
                            CakeLog::write('quickauth', "Failed to login user $user_id with token $tokenId-$tokenValue.");
                            $this->Flash->set('Login failed. Please contact an administrator.', ['params' => ['class' => 'error']]);
                        }
                    }

                } else {

                    // user already logged in
                    CakeLog::write('quickauth_skip', "Authentication for token $tokenId-$tokenValue skipped. User already logged in.");
                }
            }

        } else if (empty($user)) {

            // token not found in db, nor is user logged in already
            CakeLog::write('quickauth', "Requested token $tokenId-$tokenValue was not found.");
            $this->Flash->set('Invalid Authentication token. Please contact an administrator.', ['params' => ['class' => 'error']]);
        }

        // check if coming from a web server or in-game
        if (!empty($params['game'])) {
            $this->Session->write('Auth.User.ingame', $params['game']);
            $this->Flash->set('You may also visit the store at store.reflex-gamers.com', ['key' => 'quickauth', 'params' => ['class' => 'info']]);
        } else {
            // go straight to store if game not specified
            $this->redirect($redirLoc);
            return;
        }

        // construct page from server if no `page` param
        if (empty($params['page'])) {

            // find name of server for url
            if (!empty($auth)) {
                $this->loadModel('Server');
                $server = Hash::extract($this->Server->findByServerIp($auth['server'], array('short_name')), 'Server');
            }

            // if server not found, use game from url
            $server = !empty($server) ? $server['short_name'] : $params['game'];
            $redirLoc = array('controller' => 'Items', 'action' => 'index', 'server' => $server);
        }

        // if server is not in the popup list, send user straight to store
        if (!in_array($params['game'], $config['PopupFromGames'])) {
            $this->redirect($redirLoc);
            return;
        }

        // render page with JS popup
        $this->set(array(
            'popupUrl' => $redirLoc,
            'height' => $config['WindowHeight'],
            'width' => $config['WindowWidth']
        ));

        $this->render('/Common/popup');
    }
}
