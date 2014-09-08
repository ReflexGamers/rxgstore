<?php
App::uses('AppController', 'Controller');

class GiftsController extends AppController {
	public $components = array('Paginator', 'RequestHandler', 'ServerUtility');
	public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function accept($gift_id) {

		$user_id = $this->Auth->user('user_id');

		$gift = $this->Gift->find('first', array(
			'conditions' => array(
				'gift_id' => $gift_id,
				'recipient_id' => $user_id,
				'accepted = 0'
			),
			'contain' => 'GiftDetail'
		));

		$this->loadModel('Item');
		$this->loadModel('UserItem');

		if (!empty($gift)) {

			$this->UserItem->query('LOCK TABLES user_item WRITE, user_item as UserItem WRITE');

			$userItems = Hash::combine(
				$this->UserItem->findAllByUserId($user_id),
				'{n}.UserItem.item_id', '{n}.UserItem'
			);

			foreach ($gift['GiftDetail'] as $detail) {

				$item_id = $detail['item_id'];
				$quantity = $detail['quantity'];

				if (empty($userItems[$item_id])) {
					$userItems[$item_id] = array(
						'user_id' => $user_id,
						'item_id' => $item_id,
						'quantity' => $quantity
					);
				} else {
					$userItems[$item_id]['quantity'] += $quantity;
				}
			}

			$this->UserItem->saveMany($userItems, array('atomic' => false));
			$this->UserItem->query('UNLOCK TABLES');

			$this->Gift->id = $gift_id;
			$this->Gift->saveField('accepted', 1);

			$this->loadModel('User');
			$server = $this->User->getCurrentServer($user_id);

			//Refresh user's inventory
			if (!empty($server)) {
				$this->ServerUtility->exec($server, "sm_reload_user_inventory $user_id");
			}

		}

		$this->set(array(
			'items' => $this->Item->getAll(),
			'quantity' => $this->UserItem->getByUser($user_id)
		));

		$this->render('/Items/list.inc');
	}

	public function compose($steamid) {

		if (empty($steamid)) {
			$this->redirect(array('controller' => 'items', 'action' => 'index'));
			return;
		}

		$player = $this->AccountUtility->getSteamInfo($steamid, true);

		if (empty($player)) {
			throw new NotFoundException(__('Invalid recipient'));
		}

		$user_id = $this->Auth->user('user_id');
		$recipient_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

		if ($user_id == $recipient_id) {
			$this->Session->setFlash('You cannot send a gift to yourself.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'items', 'action' => 'index'));
			return;
		}

		$this->loadModel('Item');
		$this->loadModel('UserItem');

		$this->set(array(
			'player' => $player,
			'userItems' => $this->UserItem->getByUser($user_id)
		));

		$this->activity(false);
	}

	public function activity($doRender = true) {

		$this->Paginator->settings = array(
			'Gift' => array(
				'contain' => 'GiftDetail',
				'limit' => 5
			)
		);

		$gifts = $this->Paginator->paginate('Gift');

		foreach ($gifts as &$gift) {

			$gift['GiftDetail'] = Hash::combine(
				$gift['GiftDetail'],
				'{n}.item_id', '{n}.quantity'
			);
		}

		$this->addPlayers(Hash::extract($gifts, '{n}.{s}.sender_id'));
		$this->addPlayers(Hash::extract($gifts, '{n}.{s}.recipient_id'));

		$this->loadModel('Item');
		$items = $this->Item->getAll();

		$this->set(array(
			'activities' => $gifts,
			'items' => $items,
			'itemsIndexed' => Hash::combine($items, '{n}.item_id', '{n}'),
			'currencyMult' => Configure::read('Store.CurrencyMultiplier'),
			'cashStackSize' => Configure::read('Store.CashStackSize'),
			'pageLocation' => array('controller' => 'gifts', 'action' => 'activity')
		));

		if ($doRender) {
			$this->render('/Activity/recent');
		}
	}

	public function package($steamid) {

		$this->request->allowMethod('post');

		if (empty($steamid)) {
			$this->redirect(array('controller' => 'items', 'action' => 'index'));
			return;
		}

		$player = $this->AccountUtility->getSteamInfo($steamid, true);
		if (empty($player)) {
			throw new NotFoundException(__('Invalid recipient'));
		}

		$giftDetails = $this->request->data['GiftDetail'];
		$message = empty($this->request->data['Gift']['message']) ? '' : $this->request->data['Gift']['message'];
		$anonymous = empty($this->request->data['Gift']['anonymous']) ? false : $this->request->data['Gift']['anonymous'] == 'on';

		$user_id = $this->Auth->user('user_id');
		$recipient_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

		if ($user_id == $recipient_id) {
			$this->Session->setFlash('You cannot send a gift to yourself.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'items', 'action' => 'index'));
			return;
		}

		$this->loadModel('Item');
		$this->loadModel('UserItem');

		$userItems = $this->UserItem->getByUser($user_id);
		$items = $this->Item->getAllIndexed();
		$totalValue = 0;

		foreach ($giftDetails as $key => $detail) {

			$item_id = $detail['item_id'];
			$quantity = $detail['quantity'];

			if (empty($quantity) || $quantity < 1) {
				unset($giftDetails[$key]);
				continue;
			}

			if (empty($userItems[$item_id]) || $userItems[$item_id] < $quantity) {
				$this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in your inventory to package this gift.", 'default', array('class' => 'error'));
				$this->redirect(array('action' => 'compose', 'id' => $steamid));
				return;
			}

			//Current price for estimated value
			$totalValue += $items[$item_id]['price'] * $quantity;
		}

		if(empty($giftDetails)) {
			$this->redirect(array('action' => 'compose', 'id' => $steamid));
			return;
		}

		$this->Session->write("gift-$steamid", array(
			'Gift' => array(
				'sender_id' => $user_id,
				'recipient_id' => $this->AccountUtility->AccountIDFromSteamID64($steamid),
				'anonymous' => $anonymous,
				'message' => $message
			),
			'GiftDetail' => $giftDetails
		));

		$this->set(array(
			'player' => $player,
			'userItems' => $userItems,
			'data' => $giftDetails,
			'message' => $message,
			'anonymous' => $anonymous,
			'totalValue' => $totalValue,
			'items' => $items
		));

		$this->Session->setFlash('Please confirm the contents of your gift below and then click send.');
		$this->render('compose');
	}

	public function send($steamid) {

		$this->request->allowMethod('post');

		$player = $this->AccountUtility->getSteamInfo($steamid, true);
		if (empty($player)) {
			throw new NotFoundException(__('Invalid recipient'));
		}

		$gift = $this->Session->read("gift-$steamid");

		if (empty($gift)) {
			$this->Session->setFlash('Oops! It appears you have not prepared a gift for this recipient.', 'default', array('class' => 'error'));
			$this->redirect(array('action' => 'compose', 'id' => $steamid));
			return;
		}

		$this->loadModel('Item');
		$this->loadModel('UserItem');
		$this->loadModel('User');

		$user_id = $this->Auth->user('user_id');
		$this->User->id = $user_id;
		$this->User->saveField('locked', time());

		$server = $this->User->getCurrentServer($user_id);

		if (!empty($server)) {
			if (!$this->ServerUtility->exec($server, "sm_unload_user_inventory $user_id")) {
				$this->Session->setFlash('Oops! Our records show you are connected to a server but we are unable to contact it. You will not be able to send a gift until we can contact your server.', 'default', array('class' => 'error'));
				$this->User->saveField('locked', 0);
				$this->redirect(array('action' => 'compose', 'id' => $steamid));
			};
		}

		$items = $this->Item->getAllIndexed();

		$this->UserItem->query('LOCK TABLES user_item WRITE, user_item as UserItem WRITE, activity as Activity WRITE, gift as Gift WRITE, gift_detail WRITE, gift_detail as GiftDetail WRITE');

		$userItems = Hash::combine(
			$this->UserItem->findAllByUserId($user_id),
			'{n}.UserItem.item_id', '{n}.UserItem'
		);

		foreach ($gift['GiftDetail'] as $detail) {

			$item_id = $detail['item_id'];
			$quantity = $detail['quantity'];

			if (empty($userItems[$item_id]) || $userItems[$item_id]['quantity'] < $quantity) {
				$this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in your inventory to package this gift.", 'default', array('class' => 'error'));
				$this->Session->delete("gift-$steamid");
				$this->UserItem->query('UNLOCK TABLES');
				$this->User->saveField('locked', 0);
				$this->redirect(array('action' => 'compose', 'id' => $steamid));
				return;
			}

			$userItems[$item_id]['quantity'] -= $quantity;
		}

		//Commit
		$this->loadModel('Activity');

		$gift['Gift']['gift_id'] = $this->Activity->getNewId('Gift');
		$result = $this->Gift->saveAssociated($gift, array('atomic' => false));

		if (!$result['Gift'] || in_array(false, $result['GiftDetail'])) {
			$this->Session->setFlash('There was an error sending the gift. Please contact an administrator', 'default', array('class' => 'error'));
			$this->UserItem->query('UNLOCK TABLES');
			$this->User->saveField('locked', 0);
			$this->redirect(array('action' => 'compose', 'id' => $steamid));
			return;
		}

		$this->UserItem->saveMany($userItems, array('atomic' => false));
		$this->UserItem->query('UNLOCK TABLES');
		$this->User->saveField('locked', 0);

		//Refresh user's inventory
		if (!empty($server)) {
			$this->ServerUtility->exec($server, "sm_reload_user_inventory $user_id");
		}

		$this->Session->delete("gift-$steamid");

		$this->Session->setFlash("Your gift has been sent! Gift number - #{$this->Gift->id}", 'default', array('class' => 'success'));
		$this->redirect(array('action' => 'compose', 'id' => $steamid));
	}
}