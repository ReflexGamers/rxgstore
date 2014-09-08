<?php
App::uses('AppController', 'Controller');

/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 */
class UsersController extends AppController {
	public $components = array('Paginator', 'RequestHandler');
	public $helpers = array('Html', 'Form', 'Js', 'Time');

	public function profile($steamid) {

		if (!$this->request->is('ajax')) {

			$user_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

			$this->loadModel('Item');
			$this->loadModel('Order');
			$this->loadModel('UserItem');
			$userItems = $this->UserItem->getByUser($user_id);

			$totals = Hash::combine($this->Order->find('all', array(
				'conditions' => array(
					'user_id' => $user_id
				),
				'fields' => array(
					'order_detail.item_id', 'SUM(quantity) as quantity'
				),
				'joins' => array(
					array(
						'table' => 'order_detail',
						'conditions' => array(
							'order.order_id = order_detail.order_id'
						)
					)
				),
				'group' => array(
					'order_detail.item_id'
				)
			)), '{n}.order_detail.item_id', '{n}.{n}.quantity' );

			$pastItems = array();

			foreach ($totals as $item_id => $quantity) {
				if (isset($userItems[$item_id])) {
					$diff = $totals[$item_id] - $userItems[$item_id];

					if ($diff > 0) {
						$pastItems[$item_id] = $diff;
					}
				} else {
					$pastItems[$item_id] = $totals[$item_id];
				}
			}

			$user = $this->User->read('credit', $user_id);

			if (!empty($user)) {
				$this->set('credit', $user['User']['credit']);
			}

			$reviews = Hash::map(
				$this->User->getReviews($user_id),
				'{n}', function ($arr){
					return array_merge(
						$arr['Rating'],
						$arr['review'],
						$arr[0]
					);
				}
			);

			$this->addPlayers(array($user_id));

			$this->set(array(
				'user_id' => $user_id,
				'userItems' => $userItems,
				'pastItems' => $pastItems,
				'reviews' => $reviews,
				'totalSpent' => $this->User->getTotalSpent($user_id)
			));
		}

		$this->set(array(
			'displayType' => 'user'
		));

		$this->activity($steamid);
	}

	public function activity($steamid) {

		$user_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

		$this->loadModel('Activity');
		$this->Paginator->settings = array(
			'Activity' => array(
				'findType' => 'byUser',
				'user_id' => $user_id,
				'limit' => 5
			)
		);

		$activities = $this->Activity->getRecent($this->Paginator->paginate('Activity'));

		$this->addPlayers(Hash::extract($activities, '{n}.{s}.user_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.sender_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.recipient_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.RewardRecipient.{n}'));

		$this->loadModel('Item');
		$items = $this->Item->getAll();

		$this->set(array(
			'items' => $items,
			'itemsIndexed' => Hash::combine($items, '{n}.item_id', '{n}'),
			'activities' => $activities,
			'currencyMult' => Configure::read('Store.CurrencyMultiplier'),
			'cashStackSize' => Configure::read('Store.CashStackSize'),
			'pageLocation' => array('controller' => 'users', 'action' => 'activity', 'id' => $steamid)
		));
	}

	public function impersonate($steamid) {
		$this->AccountUtility->login($steamid);
		$this->redirect(array('controller' => 'users', 'action' => 'profile', 'id' => $steamid));
	}

	public function login() {

		if ($this->Auth->user()) {
			$this->redirect($this->referer());
		}

		App::import('Vendor', 'Openid');

		try {

			$openid = new LightOpenID(Router::url('/', true));

			if (!$openid->mode) {

				//Begin authentication
				$this->Session->write('rememberme', isset($this->request->data['rememberme']));

				$openid->identity = 'http://steamcommunity.com/openid';
				$this->Session->write('Auth.redirect', $this->referer());
				$this->redirect($openid->authUrl());

			} else if ($openid->mode == 'cancel') {

				//User cancelled authentication
				//Redirect at end of function

			} else if ($openid->validate()) {

				//Authentication successful
				$oid = $openid->identity;
				$steamid = substr($oid, strrpos($oid, "/") + 1);
				$save = $this->Session->read('rememberme');
				$this->AccountUtility->login($steamid, $save);
			}
		} catch (ErrorException $e) {
			echo 'auth error: ' . $e->getMessage();
		}

		$this->redirect($this->Auth->redirectUrl());
	}

	public function logout() {
		$this->Cookie->delete('saved_login');
		$this->redirect($this->Auth->logout());
		return;
	}
}
