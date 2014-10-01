<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 *
 * @property AccessComponent $Access
 * @property AccountUtilityComponent $AccountUtility
 */
class AppController extends Controller {
	public $viewClass = 'TwigView.Twig';
	public $ext = '.tpl';
	public $layout = '';
	public $components = array(
		'Session',
		'Cookie',
		'AccountUtility',
		'Acl',
		'Access',
		'Auth' => array(
			'loginAction' => array(
				'controller' => 'Users',
				'action' => 'login'
			),
			'logoutRedirect' => array(
				'controller' => 'Items',
				'action' => 'index'
			)
		)
	);

	protected $players = array();
	protected $items = null;

	public function addPlayers($users) {
		if (is_array($users)) {
			$this->players = array_merge($this->players, $users);
		} else{
			$this->players[] = $users;
		}
	}

	public function loadItems() {

		if (empty($this->items)) {

			$this->loadModel('Item');
			$sortedItems = $this->Item->getAllSorted();
			$items = Hash::combine($sortedItems, '{n}.item_id', '{n}');

			$this->set(array(
				'sortedItems' => $sortedItems,
				'items' => $items
			));

			$this->items = $items;
		}

		return $this->items;
	}

	public function loadCashData() {
		$this->set(array(
			'currencyMult' => Configure::read('Store.CurrencyMultiplier'),
			'cashStackSize' => Configure::read('Store.CashStackSize')
		));
	}

	public function loadShoutboxData() {

		$this->loadModel('ShoutboxMessage');
		$messages = $this->ShoutboxMessage->getRecent();
		$this->addPlayers(Hash::extract($messages, '{n}.user_id'));
		$shoutConfig = Configure::read('Store.Shoutbox');

		$this->set(array(
			'messages' => $messages,
			'theTime' => time(),
			'shoutPostCooldown' => $shoutConfig['PostCooldown'],
			'shoutUpdateInterval' => $shoutConfig['UpdateInterval']
		));
	}

	public function renderLog($name = null) {

		if ($name != null) {

			$logFile = new File("../tmp/logs/$name.log", false);
			$log = $logFile->read();
			$logFile->close();

			$this->response->type('text/plain');
			$this->response->body($log);
			$this->autoRender = false;
		}
	}

	public function beforeFilter() {
		$this->Auth->allow();

		$user = $this->Auth->user();
		$isUser = isset($user);

		if ($this->AccountUtility->trySavedLogin($isUser) && !$isUser) {
			//fetch user again after logging in
			$user = $this->Auth->user();
		}

		if ($isUser) {
			$this->loadModel('User');
			$this->User->id = $user['user_id'];
			$this->User->saveField('last_activity', time());
		}

		$this->set(array(
			'user' => $user,
			'access' => $this->Access
		));

//		$log = $this->Item->getDataSource()->getLog(false, false);
//		debug($log);

		/*
		$this->Acl->allow('Advisor', 'QuickAuth');
		$this->Acl->allow('Advisor', 'Rewards');
		$this->Acl->allow('Advisor', 'Permissions', 'read');
		$this->Acl->allow('Advisor', 'Permissions', 'update');
		$this->Acl->allow('Advisor', 'Cache', 'read');
		$this->Acl->allow('Advisor', 'Cache', 'update');
		$this->Acl->allow('Advisor', 'Cache', 'delete');
		$this->Acl->allow('Advisor', 'Chats', 'delete');
		$this->Acl->allow('Advisor', 'Items', 'create');
		$this->Acl->allow('Advisor', 'Items', 'update');
		$this->Acl->allow('Advisor', 'Reviews', 'update');
		$this->Acl->allow('Advisor', 'Reviews', 'delete');
		$this->Acl->allow('Advisor', 'Receipts', 'read');

		$this->Acl->allow('Advisor', 'Stock', 'update');
		$this->Acl->allow('Advisor', 'Users', 'update');
		*/
	}

	public function beforeRender() {

		$cart = $this->Session->read('cart');
		if (!empty($cart)) {
			$cartItems = 0;
			foreach ($cart as $item) {
				$cartItems += $item['quantity'];
			}
			$this->set('cartItems', $cartItems);
		}

		$this->set(array(
			'isAjax' => $this->request->is('ajax'),
			'players' => $this->AccountUtility->getIndexedSteamInfo($this->players)
		));
	}
}
