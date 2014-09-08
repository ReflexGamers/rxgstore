<?php
App::uses('AppController', 'Controller');

class CartController extends AppController {
	public $components = array('RequestHandler');
	public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function view() {

		$this->loadModel('Item');
		$this->loadModel('Stock');
		$this->loadModel('User');

		$cart = $this->Session->read('cart');
		$items = Hash::extract($this->Item->find('all'), '{n}.Item');
		$this->User->id = $this->Auth->user('user_id');
		$stock = $this->Stock->find('list');

		$config = Configure::Read('Store.Shipping');

		$this->set(array(
			'cart' => $cart,
			'items' => $items,
			'stock' => $stock,
			'credit' => $this->User->field('credit'),
			'shippingCost' => $config['Cost'],
			'shippingFreeThreshold' => $config['FreeThreshold']
		));
	}

	public function process() {

		$this->request->allowMethod('post');

		if (empty($this->request->data['ProcessAction'])) {
			$this->Session->setFlash('Oops! There was an error processing your cart.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'cart', 'action' => 'view'));
			return;
		}

		$processAction = $this->request->data['ProcessAction'];

		if ($processAction == 'empty') {
			$this->Session->delete('cart');
			$this->Session->setFlash('Your cart has been emptied.', 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'items', 'action' => 'index'));
			return;
		}

		if (empty($this->request->data['OrderDetail'])) {
			$this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'cart', 'action' => 'view'));
			return;
		}

		$orderDetails = $this->request->data['OrderDetail'];
		$cart = $this->Session->read('cart');
		$user_id = $this->Auth->user('user_id');

		$this->loadModel('Stock');
		$this->loadModel('Item');
		$this->loadModel('User');

		$stock = $this->Stock->find('list');
		$items = $this->Item->getAllIndexed();
		$userCredit = $this->User->read('credit', $user_id)['User']['credit'];

		$subTotal = 0;

		foreach ($orderDetails as $key => &$detail) {

			$item_id = $detail['item_id'];
			$quantity = $detail['quantity'];

			if ($quantity < 1) {
				unset($orderDetails[$key]);
				unset($cart[$item_id]);
				continue;
			}

			if (isset($cart[$item_id])) {
				$cart[$item_id]['quantity'] = $quantity;
			} else {
				$cart[$item_id] = array(
					'quantity' => $quantity,
					'price' => $items[$item_id]['price']
				);
			}

			$detail['price'] = $cart[$item_id]['price'];

			if ($stock[$item_id] < $quantity) {
				if ($processAction == 'checkout') {
					$this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in stock to complete your purchase.", 'default', array('class' => 'error'));
					$this->render('checkout');
					return;
				} else {
					//place max items available in cart
					$cart[$item_id] = $stock[$item_id];
				}
			}

			if ($processAction == 'checkout') {
				$subTotal += $detail['price'] * $quantity;
			}
		}

		if ($processAction == 'checkoout' && empty($orderDetails)) {
			$this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'cart', 'action' => 'view'));
			return;
		}

		$this->Session->write('cart', $cart);

		if ($processAction == 'update') {
			$this->Session->setFlash('Your cart has been updated.', 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'cart', 'action' => 'view'));
			return;
		}

		//Checkout stuff
		$config = Configure::Read('Store.Shipping');

		$shipping = $subTotal >= $config['FreeThreshold'] ? 0 : $config['Cost'];
		$total = $subTotal + $shipping;

		if ($total > $userCredit) {
			$this->Session->setFlash('You do not have enough CASH to complete this purchase!', 'default', array('class' => 'error'));
			return;
		}

		$this->Session->write('order', array(
			'total' => $total,
			'subtotal' => $subTotal,
			'models' =>  array(
				'Order' => array(
					'user_id' => $user_id,
					'shipping' => $shipping
				),
				'OrderDetail' => $orderDetails
			)
		));

		$this->set(array(
			'items' => $items,
			'cart' => $orderDetails,
			'subTotal' => $subTotal,
			'shipping' => $shipping,
			'total' => $total,
			'credit' => $userCredit
		));

		$this->render('checkout');
	}

	public function add($item_id = null) {

		$this->request->allowMethod('post');
		$this->autoRender = false;

		$this->loadModel('Item');
		$item = $this->Item->read(array('name', 'plural', 'buyable', 'price'), $item_id);

		if (empty($item)) {
			throw new NotFoundException(__('Invalid item'));
		}

		$item = $item['Item'];

		if ($item['buyable']) {

			$quantity = $this->request->data['Cart']['quantity'];
			$cart = $this->Session->read('cart');

			if (isset($cart[$item_id])) {
				$cart[$item_id]['quantity'] += $quantity;
			} else {
				$cart[$item_id] = array(
					'quantity' => $quantity,
					'price' => $item['price']
				);
			}

			$this->Session->write('cart', $cart);

			$name = $quantity > 1 ? $item['plural'] : $item['name'];
			$this->Session->setFlash("$quantity $name added to cart!", 'default', array('class' => 'success'));

		} else {

			$this->Session->setFlash('Oops! That item is no longer for sale.', 'default', array('class' => 'error'));
		}

		if ($this->request->is('ajax')) {
			$this->render('/Common/flash');
		} else {
			$this->redirect($this->referer());
		}
	}

	public function link() {
		$this->render('link.inc');
	}

	public function remove($item_id = null) {

		$this->request->allowMethod('post');
		$this->autoRender = false;

		$this->loadModel('Item');

		if (!$this->Item->exists($item_id)) {
			throw new NotFoundException(__('Invalid item'));
		}

		$cart = $this->Session->read('cart');

		if (isset($cart[$item_id])) {
			unset($cart[$item_id]);
		}

		$this->Session->write('cart', $cart);
	}

}