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
        $this->Auth->allow('search', 'search_results');
    }

    /**
     * Shows the view page for cached Steam Players.
     */
    public function view() {

        if (!$this->Access->check('Cache', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        $this->set('cacheDuration', Configure::read('Store.SteamCache.Duration') / 3600);

        $this->loadShoutbox();
        $this->players(false);
    }

    /**
     * Search page for cached steam players.
     */
    public function search() {

        $term = !empty($this->request->query['term']) ? $this->request->query['term'] : '';

        if (!empty($term)) {
            $this->search_results($term, false);
        }

        $this->loadShoutbox();
    }

    /**
     * Shows search results for a specific term. Called by the view action or via ajax directly.
     *
     * @param string $term search term
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function search_results($term = '', $forceRender = true) {

        $this->Paginator->settings = $this->SteamPlayerCache->getSearchQueryPage($term);
        $results = Hash::extract($this->Paginator->paginate('SteamPlayerCache'), '{n}.SteamPlayerCache.user_id');
        $this->addPlayers($results);

        $this->set(array(
            'term' => $term,
            'results' => $results,
            'pageModel' => $this->SteamPlayerCache->name,
            'pageLocation' => array('controller' => 'SteamPlayerCache', 'action' => 'search_results', 'term' => $term)
        ));

        if ($forceRender) {
            $this->render('results');
        }
    }

    /**
     * Returns json data for amount of players cached.
     */
    public function totals_cached() {

        $this->set(array(
            'data' => $this->SteamPlayerCache->getTotalsCached(),
            '_serialize' => array('data')
        ));
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
                'limit' => 50
            )
        );

        $players = Hash::extract($this->Paginator->paginate('SteamPlayerCache'), '{n}.SteamPlayerCache');

        // convert steamids to get member status
        $accounts = array();
        foreach ($players as $player) {
            $accounts[] = $this->AccountUtility->AccountIDFromSteamID64($player['steamid']);
        }
        $members = $this->Access->getMemberInfo($accounts);
        $playerServers = $this->User->getIngamePlayerServers();

        $i = 0;
        foreach ($players as &$player) {
            $user_id = $accounts[$i++];
            $player['name'] = $player['personaname'];
            $player['member'] = !empty($members[$user_id]);
            $player['division'] = !empty($members[$user_id]['division']) ? $members[$user_id]['division'] : '';
            $player['server'] = !empty($playerServers[$user_id]) ? $playerServers[$user_id] : '';
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
     * Deletes all expired players from the cache.
     *
     * @log [admin.log]
     */
    public function clear_expired() {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'delete')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->Auth->user('steamid');
        CakeLog::write('admin', "$admin_steamid force pruned all expired players from the Steam cache.");

        $this->SteamPlayerCache->pruneExpiredPlayers();
        $this->autoRender = false;
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

        $admin_steamid = $this->Auth->user('steamid');
        CakeLog::write('admin', "$admin_steamid force refreshed $steamid in the Steam cache.");

        $user_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);
        $this->SteamPlayerCache->refreshPlayers(array($user_id));

        $player = $this->SteamPlayerCache->findBySteamid($steamid)['SteamPlayerCache'];
        $player['name'] = $player['personaname'];
        $player['member'] = $this->Access->checkIsMember($user_id);

        $this->set('player', $player);
        $this->render('single.inc');
    }

//    /**
//     * Refreshes all players in the cache and renders a page of them in the response.
//     *
//     * @log [admin.log] the admin id
//     */
//    public function refresh_all() {
//
//        $this->request->allowMethod('post');
//
//        if (!$this->Access->check('Cache', 'update')) {
//            $this->redirect($this->referer());
//            return;
//        }
//
//        $admin_steamid = $this->Auth->user('steamid');
//        CakeLog::write('admin', "$admin_steamid force refreshed the entire Steam cache.");
//
//        $this->SteamPlayerCache->refreshAll();
//        $this->players();
//    }

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

        $admin_steamid = $this->Auth->user('steamid');
        CakeLog::write('admin', "$admin_steamid force cleared $steamid in the Steam cache.");

        $this->SteamPlayerCache->delete($steamid);
        $this->autoRender = false;
    }

//    /**
//     * Removes all players from the cache and renders an empty list in the response.
//     *
//     * @log [admin.log] the admin id
//     */
//    public function clear_all() {
//
//        $this->request->allowMethod('post');
//
//        if (!$this->Access->check('Cache', 'delete')) {
//            $this->redirect($this->referer());
//            return;
//        }
//
//        $admin_steamid = $this->Auth->user('steamid');
//        CakeLog::write('admin', "$admin_steamid force cleared the entire Steam cache.");
//
//        $this->SteamPlayerCache->clearAll();
//        $this->render('list.inc');
//    }
}