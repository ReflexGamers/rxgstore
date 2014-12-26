<?php
App::uses('AppController', 'Controller');

/**
 * SteamPlayerCache Controller
 *
 * @property SteamPlayerCache $SteamPlayerCache
 * @property PaginatorComponent $Paginator
 */
class SteamPlayerCacheController extends AppController {
    public $components = array('RequestHandler', 'Paginator');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Shows the view page for cached Steam Players.
     */
    public function view() {

        if (!$this->Access->check('Cache', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        $this->set('cacheDuration', Configure::read('Store.SteamCacheDuration') / 3600);

        $this->loadShoutbox();
        $this->players(false);
    }

    /**
     * Shows a page of cached players. This is usually included in the view page or called via ajax for paging, but
     * sometimes it's used as the whole response such as when refreshing the entire cache.
     *
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function players($forceRender = true) {

        if (!$this->Access->check('Cache', 'read')) {
            if ($this->request->is('ajax')) {
                $this->autoRender = false;
            } else {
                $this->redirect($this->referer());
            }
            return;
        }

        $this->Paginator->settings = array(
            'SteamPlayerCache' => array(
                'limit' => 50,
            )
        );

        $players = Hash::extract($this->Paginator->paginate('SteamPlayerCache'), '{n}.SteamPlayerCache');

        // convert steamids to get member status
        $accounts = array();
        foreach ($players as $player) {
            $accounts[] = $this->AccountUtility->AccountIDFromSteamID64($player['steamid']);
        }
        $members = $this->Access->getMemberInfo($accounts);

        $i = 0;
        foreach ($players as &$player) {
            $user_id = $accounts[$i++];
            $player['name'] = $player['personaname'];
            $player['member'] = !empty($members[$user_id]);
            $player['division'] = !empty($members[$user_id]['division']) ? $members[$user_id]['division'] : '';
        }

        $this->set(array(
            'cache' => $players,
            'pageModel' => $this->SteamPlayerCache->name,
            'pageLocation' => array('controller' => 'SteamPlayerCache', 'action' => 'players')
        ));

        if ($forceRender) {
            $this->render('players');
        }
    }

    /**
     * Refreshes a player in the cache and renders that player's row in the response.
     *
     * @log [admin.log] the admin id, player id
     * @param int $steamid 64-bit steamid of the cached player to refresh
     */
    public function refresh($steamid) {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        CakeLog::write('admin', "$admin_steamid force refreshed $steamid in the Steam cache.");

        $this->SteamPlayerCache->refresh(array($steamid));

        $player = $this->SteamPlayerCache->findBySteamid($steamid)['SteamPlayerCache'];
        $player['name'] = $player['personaname'];
        $player['member'] = $this->Access->checkIsMember($this->AccountUtility->AccountIDFromSteamID64($steamid));

        $this->set('player', $player);
        $this->render('single.inc');
    }

    /**
     * Refreshes all players in the cache and renders a page of them in the response.
     *
     * @log [admin.log] the admin id
     */
    public function refresh_all() {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        CakeLog::write('admin', "$admin_steamid force refreshed the entire Steam cache.");

        $this->SteamPlayerCache->refreshAll();
        $this->players();
    }

    /**
     * Removes a player from the cache.
     *
     * @log [admin.log] the admin id, player id
     * @param int $steamid the 64-bit steamid of the player to remove
     */
    public function clear($steamid) {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'delete')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        CakeLog::write('admin', "$admin_steamid force cleared $steamid in the Steam cache.");

        $this->SteamPlayerCache->delete($steamid);
        $this->autoRender = false;
    }

    /**
     * Removes all players from the cache and renders an empty list in the response.
     *
     * @log [admin.log] the admin id
     */
    public function clear_all() {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'delete')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        CakeLog::write('admin', "$admin_steamid force cleared the entire Steam cache.");

        $this->SteamPlayerCache->clearAll();
        $this->render('list.inc');
    }
}