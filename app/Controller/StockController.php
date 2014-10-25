<?php
App::uses('AppController', 'Controller');

class StockController extends AppController {
	public $components = array('RequestHandler', 'Paginator');
	public $helpers = array('Html', 'Form', 'Js', 'Session', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function edit() {

		if (!$this->Access->check('Stock', 'update')) {
			$this->redirect($this->referer());
			return;
		}

		if (isset($this->request->data['Stock'])) {

			$newStock = $this->request->data['Stock'];
			$stock = Hash::combine($this->Stock->find('all'), '{n}.Stock.item_id', '{n}.Stock');
			$shipmentDetail = array();

			$stockChanged = false;

			foreach ($newStock as &$item) {
				$item_id = $item['item_id'];
				if (!empty($item['quantity'])) {
					$quantity = $item['quantity'];
					$qty = $quantity > 0 ? $quantity : 0;
					$oldQty = $stock[$item_id]['quantity'];
					$stock[$item_id]['quantity'] = min($stock[$item_id]['quantity'] + $qty, $stock[$item_id]['maximum']);
					$shipmentDetail[] = array(
						'item_id' => $item_id,
						'quantity' => $stock[$item_id]['quantity'] - $oldQty
					);
					$stockChanged = true;
				}
			}

			if ($stockChanged) {

				$this->Stock->saveMany($stock, array(
					'fields' => array('quantity'),
					'atomic' => false
				));

				$this->loadModel('Activity');
				$this->loadModel('Shipment');
				$this->Shipment->saveAssociated(array(
					'Shipment' => array(
						'shipment_id' => $this->Activity->getNewId('Shipment'),
						'user_id' => $this->Auth->user('user_id')
					),
					'ShipmentDetail' => $shipmentDetail
				), array('atomic' => false));

				$this->Session->setFlash('The shipment was received successfully!', 'default', array('class' => 'success'));
			}
		}

		$stock = Hash::map($this->Stock->find('all', array(
			'contain' => array(
				'Item' => array(
					'fields' => array(
						'name', 'short_name'
					)
				)
			)
		)), '{n}', function($arr){
			return array_merge($arr['Stock'], $arr['Item']);
		});

		$this->set('stock', $stock);

		$this->activity(false);
	}


	public function activity($doRender = true) {

		$this->loadModel('Shipment');

		$this->Paginator->settings = array(
			'Shipment' => array(
				'contain' => 'ShipmentDetail',
				'limit' => 5,
			)
		);

		$shipments = $this->Paginator->paginate('Shipment');

		foreach ($shipments as &$shipment) {

			$shipment['ShipmentDetail'] = Hash::combine(
				$shipment['ShipmentDetail'],
				'{n}.item_id', '{n}.quantity'
			);
		}

		$this->addPlayers($shipments, '{n}.Shipment.user_id');

		$this->loadItems();

		$this->set(array(
			'pageModel' => 'Shipment',
			'activities' => $shipments,
			'activityPageLocation' => array('controller' => 'Stock', 'action' => 'activity')
		));

		if ($doRender) {
			$this->set('title', 'Shipment Activity');
			$this->render('/Activity/list');
		}
	}
}