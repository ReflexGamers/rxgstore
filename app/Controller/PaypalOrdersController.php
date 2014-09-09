<?php
App::uses('AppController', 'Controller');

class PaypalOrdersController extends AppController {
	public $components = array('Paginator', 'RequestHandler', 'Paypal');
	public $helpers = array('Html', 'Form', 'Js', 'Time', 'Session');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function addfunds() {

		$config = Configure::read('Store');
		$options = $config['Paypal']['Options'];

		$this->set(array(
			'options' => $options,
			'currencyMult' => $config['CurrencyMultiplier'],
			'minPrice' => max(array_keys($options)) / 100,
			'maxMult' => max($options)
		));

		$this->activity(false);
	}

	public function activity($doRender = true) {

		$this->Paginator->settings = array(
			'PaypalOrder' => array(
				'limit' => 5,
			)
		);

		$paypalOrders = $this->Paginator->paginate('PaypalOrder');

		$this->addPlayers(Hash::extract($paypalOrders, '{n}.PaypalOrder.user_id'));

		$this->set(array(
			'activities' => $paypalOrders,
			'currencyMult' => Configure::read('Store.CurrencyMultiplier'),
			'cashStackSize' => Configure::read('Store.CashStackSize'),
			'pageLocation' => array('controller' => 'PaypalOrders', 'action' => 'activity')
		));

		if ($doRender) {
			$this->render('/Activity/recent');
		}
	}

	public function begin() {

		$this->request->allowMethod('post');

		if (empty($this->request->data['PaypalOrder'])) {
			$this->Session->setFlash('You did not specify an amount.', 'default', array('class' => 'error'));
			$this->redirect(array('controller' => 'PaypalOrders', 'action' => 'addfunds'));
			return;
		}

		$data = $this->request->data['PaypalOrder'];
		$config = Configure::read('Store');
		$options = $config['Paypal']['Options'];
		$optAmounts = array_values($options);
		$optPrices = array_keys($options);

		if (isset($data['option'])) {

			$option = $data['option'];
			$price = $optPrices[$option];
			$amount = $price * $optAmounts[$option];

		} else {

			$price = $data['amount'] * 100;

			if ($price < max($optPrices)) {
				$this->Session->setFlash('An error occurred.', 'default', array('class' => 'error'));
				$this->redirect(array('controller' => 'PaypalOrders', 'action' => 'addfunds'));
				return;
			}

			$amount = ceil($price * max($optAmounts));
		}

		$amount *= $config['CurrencyMultiplier'];
		$challenge = mt_rand();

		try {

			$payment = $this->Paypal->createPayment(
				Router::url(array('controller' => 'PaypalOrders', 'action' => 'confirm', '?' => array('challenge' => $challenge)), true),
				Router::url(array('controller' => 'PaypalOrders', 'action' => 'cancel'), true),
				$price
			);

			$approvalUrl = $this->Paypal->findApprovalUrl($payment);

			if (empty($approvalUrl)) {
				throw new Exception('Paypal Error');
			}

			$this->Session->write('buycash', array(
				'challenge' => $challenge,
				'amount' => $amount,
				'price' => $price,
				'account' => $this->Auth->user('user_id'),
				'payment' => $payment
			));

			$this->redirect($approvalUrl);

		} catch (Exception $e) {

			//$this->Session->setFlash('Oops! An error occurred. Your PAYPAL has not been charged.', 'default', array('class' => 'error'));
			//$this->redirect(array('action' => 'addfunds'));
			echo $e;
		}
	}

	public function confirm() {

		$data = $this->Session->read('buycash');
		$query = $this->request->query;

		if (empty($data) || empty($query['challenge']) || $query['challenge'] != $data['challenge'] || empty($query['PayerID'])) {
			$this->Session->setFlash('Oops! An error occurred. Your PAYPAL has not been charged.', 'default', array('class' => 'error'));
			$this->redirect(array('action' => 'addfunds'));
			return;
		}

		$response = $this->Paypal->executePayment($data['payment'], $this->request->query['PayerID']);

		if ($response->state == 'approved') {

			$user_id = $this->Auth->user('user_id');

			$this->loadModel('User');
			$this->User->query('LOCK TABLES user WRITE');
			$this->User->id = $user_id;
			$this->User->saveField('credit', $this->User->field('credit') + $data['amount']);
			$this->User->query('UNLOCK TABLES');

			$this->loadModel('Activity');

			$this->PaypalOrder->save(array(
				'paypal_order_id' => $this->Activity->getNewId('PaypalOrder'),
				'user_id' => $this->Auth->user('user_id'),
				'ppsaleid' => $response->transactions[0]->related_resources[0]->sale->id,
				'amount' => $data['price'],
				'fee' => isset($data['payment']->transactions->amount->details->fee) ? $data['payment']->transactions->amount->details->fee : 0,
				'credit' => $data['amount']
			));

			$this->Session->setFlash('The CASH has been added to your account.', 'default', array('class' => 'success'));
		}

		//$this->render('addfunds');
		$this->redirect(array('controller' => 'PaypalOrders', 'action' => 'addfunds'));
	}

	public function cancel() {

		$this->Session->setFlash('Your transaction was cancelled and your PAYPAL was not charged.', 'default', array('class' => 'error'));
		$this->redirect(array('controller' => 'PaypalOrders', 'action' => 'addfunds'));
		return;

	}
}
