<?php
App::uses('AppController', 'Controller');

/**
 * SteamPlayerCache Controller
 *
 * @property SteamPlayerCache $SteamPlayerCache
 */
class SteamPlayerCacheController extends AppController {
	public $components = array('RequestHandler');
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
		$this->set('cache', $this->SteamPlayerCache->getAll());
	}

	public function refresh($steamid) {

		$this->request->allowMethod('post');

		if (!$this->Access->check('Cache', 'update')) {
			$this->redirect($this->referer());
			return;
		}

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

		$this->SteamPlayerCache->delete($steamid);
		$this->autoRender = false;
	}

	public function clear_all() {

		$this->request->allowMethod('post');

		if (!$this->Access->check('Cache', 'delete')) {
			$this->redirect($this->referer());
			return;
		}

		$this->SteamPlayerCache->clearAll();
		$this->render('list.inc');
	}
}