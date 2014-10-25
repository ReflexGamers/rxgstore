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

    public function view() {

        if (!$this->Access->check('Cache', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        $this->set('cacheDuration', Configure::read('Store.SteamCacheDuration') / 3600);

        $this->players();
    }

    public function players() {

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

        $accounts = array();
        foreach ($players as $player) {
            $accounts[] = $this->AccountUtility->AccountIDFromSteamID64($player['steamid']);
        }
        $members = $this->Access->getMembers($accounts);

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
    }

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

        $this->set('player', $player);
        $this->render('single.inc');
    }

    public function refresh_all() {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Cache', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        CakeLog::write('admin', "$admin_steamid force refreshed the entire Steam cache.");

        $this->SteamPlayerCache->refreshAll();

        $this->set('cache', $this->SteamPlayerCache->getAll());
        $this->render('list.inc');
    }

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