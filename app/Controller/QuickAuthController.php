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

	public function auth() {

		$params = $this->request->query;
		$redirLoc = array('controller' => 'Items', 'action' => 'index');

		if (empty($params)) {
			CakeLog::write('quickauth', "QuickAuth attempted with no URL params.");
			$this->redirect($redirLoc);
			return;
		}

		$user_id = $this->Auth->user('user_id');
		$config = Configure::read('Store.QuickAuth');

		$tokenId = $params['id'];
		$tokenValue = $params['token'];

		$auth = Hash::extract($this->QuickAuth->find('first', array(
			'conditions' => array(
				'quick_auth_id' => $tokenId,
				'token' => $tokenValue,
				'redeemed = 0'
			)
		)), 'QuickAuth');

		if (!empty($auth)) {

			$this->QuickAuth->id = $auth['quick_auth_id'];
			$this->QuickAuth->saveField('redeemed', 1);

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

			if (empty($user_id)) {

				if (strtotime($auth['date']) + $config['TokenExpire'] < time()) {

					CakeLog::write('quickauth', "Attempted usage of expired token $tokenId-$tokenValue by user $user_id.");
					$this->Session->setFlash(
						'Your QuickAuth token has expired. Please contact an administrator.',
						'default',
						array('class' => 'error')
					);

				} else if (!$this->AccountUtility->loginUser($auth['user_id'], array('force' => true))) {

					CakeLog::write('quickauth', "Failed to login user $user_id with token $tokenId-$tokenValue.");
					$this->Session->setFlash(
						'QuickAuth Login failed. Please contact an administrator.',
						'default',
						array('class' => 'error')
					);
				}
			}

		} else if (empty($user_id)) {

			CakeLog::write('quickauth', "Requested token $tokenId-$tokenValue for $user_id was not found or already redeemed.");
			$this->Session->setFlash('Invalid QuickAuth token. Please contact an administrator.', 'default', array('class' => 'error'));
		}

		$this->Session->write('Auth.user.ingame', true);

		if (empty($params['source'])) {
			//Go straight to store
			$this->redirect($redirLoc);
		}

		//Find name of server for url
		$this->loadModel('Server');
		$server = Hash::extract($this->Server->findByServerIp($auth['server'], array('short_name')), 'Server');
		$server = !empty($server) ? $server['short_name'] : $params['source'];
		$redirLoc = array('controller' => 'Items', 'action' => 'index', 'server' => $server);

		if (!in_array($params['source'], $config['PopupFromServers'])) {
			//Go straight to store
			$this->redirect($redirLoc);
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
