<?php
App::uses('Component', 'Controller');

class ConversionComponent extends Component {
	public $components = array('AccountUtility');

	public function initialize(Controller $controller) {
		$this->Activity = ClassRegistry::init('Activity');
		$this->User = ClassRegistry::init('User');
		$this->UserItem = ClassRegistry::init('UserItem');
		$this->Order = ClassRegistry::init('Order');
		$this->PayPalOrder = ClassRegistry::init('PayPalOrder');
	}

	public function convertAll() {
		set_time_limit(120);

		$this->convertUsers();
		$this->convertInventories();
		$this->convertOrders();

		$this->AccountUtility->permissions();
		$this->AccountUtility->syncSourcebans();
	}

	public function convertUsers() {

		echo 'beginning conversion';

		$db = ConnectionManager::getDataSource('oldStore');
		$result = $db->rawQuery("SELECT * FROM USER WHERE credit != 0");

		$currencyMultiplier = Configure::read('Store.CurrencyMultiplier');

		while( $row = $result->fetch() ) {
			$this->User->save(array(
				'user_id' => $row['ACCOUNT'],
				'credit' => $row['CREDIT'] * $currencyMultiplier
			));
			$this->User->clear();
		}

		echo 'ending conversion';
	}

	public function convertInventories() {

		echo 'beginning conversion';

		$db = ConnectionManager::getDataSource('oldStore');
		$result = $db->rawQuery("SELECT * FROM INVENTORY WHERE amount != 0");

		while( $row = $result->fetch() ) {
			$this->UserItem->save(array(
				'user_id' => $row['ACCOUNT'],
				'item_id' => $row['ITEMID'],
				'quantity' => $row['AMOUNT']
			));
			$this->UserItem->clear();
		}

		echo 'ending conversion';
	}

	public function convertOrders() {

		echo 'beginning conversion';

		App::import('Vendor', 'transaction');
		$db = ConnectionManager::getDataSource('oldStore');
		$result = $db->rawQuery("SELECT ID,DATE,TYPE,STEAM,TOTAL,PPSALEID,PAYPAL,FEES,CASH,TRANSACTION FROM RECEIPTS WHERE STATE = 'OKAY' ORDER BY DATE");

		$currencyMultiplier = Configure::read('Store.CurrencyMultiplier');

		while( $row = $result->fetch() ) {
			//print_r($row);
			if ($row['TYPE'] == 'ITEMS') {

				$date = $row['DATE'];
				$user_id = $this->AccountUtility->AccountIDFromSteamID64($row['STEAM']);
				$items = unserialize($row['TRANSACTION'])->items->items;

				//print_r($items);

				$activity_id = $this->Activity->getNewId('Order');

				$order = array(
					'Order' => array(
						'order_id' => $activity_id,
						'user_id' => $user_id,
						'shipping' => 5 * $currencyMultiplier,
						'date' => $date
					),
					'OrderDetail' => array()
				);

				foreach ($items as $item) {
					$order['OrderDetail'][] = array(
						'item_id' => $item->id,
						'quantity' => $item->amount,
						'price' => $item->price * $currencyMultiplier
					);
				}

				//print_r($order);

				$this->Order->saveAssociated($order, array('atomic' => false));
				$this->Order->clear();

			} else if ($row['TYPE'] == 'CASH') {

				$paypalorder = array(
					'PayPalOrder' => array(
						'paypal_order_id' => $this->Activity->getNewId('PaypalOrder'),
						'user_id' => $this->AccountUtility->AccountIDFromSteamID64($row['STEAM']),
						'date' => $row['DATE'],
						'ppsaleid' => $row['PPSALEID'],
						'amount' => $row['PAYPAL'],
						'fee' => $row['FEES'],
						'credit' => $row['CASH'] * $currencyMultiplier
					)
				);

				//print_r($paypalorder);

				$this->PayPalOrder->saveAssociated($paypalorder, array('atomic' => false));
				$this->PayPalOrder->clear();
			}


		}

		echo 'ending conversion';
	}
}