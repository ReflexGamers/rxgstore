<?php
App::uses('AppController', 'Controller');

/**
 * Users Controller
 *
 * @property QuickAuth $QuickAuth
 * @property PaginatorComponent $Paginator
 */
class QuickAuthController extends AppController {
	public $components = array('Paginator', 'RequestHandler');
	public $helpers = array('Html', 'Form', 'Js', 'Time');

	public function view() {

		if (!$this->Access->check('QuickAuth', 'read')) {
			$this->redirect($this->referer());
			return;
		}

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
			'tokenExpire' => Configure::read('Store.QuickAuth.TokenExpire'),
			'servers' => $servers
		));

		$this->records();
	}

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
		$this->addPlayers(Hash::extract($quickauth, '{n}.user_id'));

		$this->set(array(
			'quickauth' => $quickauth,
			'pageModel' => $this->QuickAuth->name,
			'pageLocation' => array('controller' => 'QuickAuth', 'action' => 'records')
		));
	}

	/**
	 * Handles quick authentication when the person's steamid is known such as in a game server.
	 *
	 * Expects query parameters 'id' and 'token'.
	 *
	 * A weird bug in the Source engine causes the page to be requested twice so re-using tokens is silently ignored.
	 */
	public function auth() {

		$params = $this->request->query;
		$redirLoc = array('controller' => 'Items', 'action' => 'index');

		if (empty($params)) {
			CakeLog::write('quickauth', "QuickAuth attempted with no URL params.");
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

			//Silently ignore if already redeemed (probably double request)
			if (!$auth['redeemed']) {

				$this->QuickAuth->id = $auth['quick_auth_id'];
				$this->QuickAuth->saveField('redeemed', 1);

				$user_id = $auth['user_id'];

				$aro = $this->Access->findUser($user_id);

				if (empty($aro)) {
					//Record user as member
					CakeLog::write('quickauth', "Promoted user $user_id to member.");
					$aro->save(array(
						'parent_id' => 1,
						'model' => 'User',
						'foreign_key' => $user_id
					));
				}

				if (empty($user)) {

					//not already logged in
					$diff = strtotime($auth['date']) + $config['TokenExpire'] - time();

					if ($diff < 0) {

						//Expired unredeemed token
						$diff = abs($diff);
						CakeLog::write('quickauth', "Attempted usage of token $tokenId-$tokenValue which expired $diff seconds ago.");
						$this->Session->setFlash('Authentication token expired. Please contact an administrator.', 'default', array('class' => 'error'));

					} else if (!$this->AccountUtility->loginUser($user_id, array('force' => true))) {

						//Failed to login user
						CakeLog::write('quickauth', "Failed to login user $user_id with token $tokenId-$tokenValue.");
						$this->Session->setFlash('Login failed. Please contact an administrator.', 'default', array('class' => 'error'));
					}

				} else {

					//User already logged in
					CakeLog::write('quickauth', "Authentication for token $tokenId-$tokenValue skipped. User already logged in.");
				}
			}

		} else if (empty($user)) {

			//Token not found in db, not already logged in
			CakeLog::write('quickauth', "Requested token $tokenId-$tokenValue was not found.");
			$this->Session->setFlash('Invalid Authentication token. Please contact an administrator.', 'default', array('class' => 'error'));
		}

		$this->Session->write('Auth.user.ingame', true);

		if (empty($params['source'])) {
			//Go straight to store
			$this->redirect($redirLoc);
			return;
		}

		//Find name of server for url
		if (!empty($auth)) {
			$this->loadModel('Server');
			$server = Hash::extract($this->Server->findByServerIp($auth['server'], array('short_name')), 'Server');
		}

		//if server not found, use source from url
		$server = !empty($server) ? $server['short_name'] : $params['source'];
		$redirLoc = array('controller' => 'Items', 'action' => 'index', 'server' => $server);

		if (!in_array($params['source'], $config['PopupFromServers'])) {
			//Go straight to store
			$this->redirect($redirLoc);
			return;
		}

		//Render page with JS popup
		$this->set(array(
			'popupUrl' => $redirLoc,
			'height' => $config['WindowHeight'],
			'width' => $config['WindowWidth']
		));

		$this->render('/Common/popup');
	}
}
