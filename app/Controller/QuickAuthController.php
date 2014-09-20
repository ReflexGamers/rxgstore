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

		$quickauth = Hash::extract($this->QuickAuth->find('all'), '{n}.QuickAuth');
		$this->addPlayers(Hash::extract($quickauth, '{n}.user_id'));

		$this->set(array(
			'tokenExpire' => Configure::read('Store.QuickAuth.TokenExpire'),
			'quickauth' => $quickauth,
			'servers' => $servers
		));
	}

	public function auth() {

		$params = $this->request->query;
		$redirLoc = array('controller' => 'Items', 'action' => 'index');

		if (empty($params)) {
			$this->redirect($redirLoc);
			return;
		}

		$user = $this->Auth->user();
		$config = Configure::read('Store.QuickAuth');

		$auth = Hash::extract($this->QuickAuth->find('first', array(
			'conditions' => array(
				'quick_auth_id' => $params['id'],
				'token' => $params['token'],
				'redeemed = 0'
			)
		)), 'QuickAuth');

		if (!empty($auth)) {

			$this->QuickAuth->id = $auth['quick_auth_id'];
			$this->QuickAuth->saveField('redeemed', 1);

			$aro = $this->Access->findUser($user['user_id']);

			if (empty($aro)) {
				//Record user as member
				$aro->save(array(
					'parent_id' => 1,
					'model' => 'User',
					'foreign_key' => $user['user_id']
				));
			}

			if (empty($user)) {

				if (strtotime($auth['date']) + $config['TokenExpire'] < time()) {

					$this->Session->setFlash(
						'Your QuickAuth token has expired. Please contact an administrator.',
						'default',
						array('class' => 'error')
					);

				} else if (!$this->AccountUtility->loginUser($auth['user_id'])) {

					$this->Session->setFlash(
						'QuickAuth Login failed. Please contact an administrator.',
						'default',
						array('class' => 'error')
					);
				}
			}

		} else if (empty($user)) {

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
