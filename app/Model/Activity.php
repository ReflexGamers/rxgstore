<?php
App::uses('AppModel', 'Model');
/**
 * Activity Model
 *
 * @property Gift $Gift
 * @property Order $Order
 * @property PaypalOrder $PaypalOrder
 * @property Review $Review
 * @property Reward $Reward
 * @property Shipment $Shipment
 */
class Activity extends AppModel {

	public $findMethods = array('byUser' => true, 'byItem' => true);

	public $useTable = 'activity';
	public $primaryKey = 'activity_id';

	public $hasOne = array(
		'Gift', 'Order', 'PaypalOrder', 'Review', 'Reward', 'Shipment'
	);

	public $order = 'Activity.activity_id desc';

	public function getNewId($modelName) {
		return $this->query('select NewActivity(' . $this->getDataSource()->value($modelName) . ') as id', false)[0][0]['id'];
	}

	function _findCount($state, $query, $results = array()) {
		if ($state == 'before') {
			if (isset($query['type']) && $query['type'] != 'count') {
				$query = $this->{'_find' . ucfirst($query['type'])}($state, $query);
			}
			return parent::_findCount($state, $query);
		}
		if (isset($query['type']) && isset($this->findMethods[$query['type']]) && $this->findMethods[$query['type']]) {
			return $this->getDataSource()->fetchAll("select count(*) as count from ({$query['raw']}) as Activity")[0][0]['count'];
		}
		return parent::_findCount($state, $query, $results);
	}

	public function _findByItem($state, $query, $results = array()) {

		if ($state == 'before' && (!isset($query['operation']) || $query['operation'] != 'count')) {
			return $query;
		}

		$item_id = $query['item_id'];

		$db = $this->getDataSource();

		$orderQuery = $db->buildStatement(array(
			'fields' => array(
				"Order.order_id as activity_id, 'Order' as model"
			),
			'table' => $db->fullTableName($this->Order),
			'alias' => 'Order',
			'conditions' => array(
				'item_id' => $item_id
			),
			'joins' => array(
				array(
					'table' => 'order_detail',
					'alias' => 'OrderDetail',
					'conditions' => array(
						'Order.order_id = OrderDetail.order_id'
					)
				)
			)
		), $this->Order);

		$giftQuery = $db->buildStatement(array(
			'fields' => array(
				"Gift.gift_id as activity_id, 'Gift' as model"
			),
			'table' => $db->fullTableName($this->Gift),
			'alias' => 'Gift',
			'conditions' => array(
				'item_id' => $item_id
			),
			'joins' => array(
				array(
					'table' => 'gift_detail',
					'alias' => 'GiftDetail',
					'conditions' => array(
						'Gift.gift_id = GiftDetail.gift_id'
					)
				)
			)
		), $this->Gift);

		$rewardQuery = $db->buildStatement(array(
			'fields' => array(
				"Reward.reward_id as activity_id, 'Reward' as model"
			),
			'table' => $db->fullTableName($this->Reward),
			'alias' => 'Reward',
			'conditions' => array(
				'item_id' => $item_id
			),
			'joins' => array(
				array(
					'table' => 'reward_detail',
					'alias' => 'RewardDetail',
					'conditions' => array(
						'Reward.reward_id = RewardDetail.reward_id'
					)
				)
			)
		), $this->Reward);

		$reviewQuery = $db->buildStatement(array(
			'fields' => array(
				"review_id as activity_id, 'Review' as model"
			),
			'table' => $db->fullTableName($this->Review),
			'alias' => 'Review',
			'conditions' => array(
				'Rating.item_id' => $item_id
			),
			'joins' => array(
				array(
					'table' => 'rating',
					'alias' => 'Rating',
					'conditions' => array(
						'Review.rating_id = Rating.rating_id'
					)
				)
			),
		), $this->Review);

		$shipmentQuery = $db->buildStatement(array(
			'fields' => array(
				"Shipment.shipment_id as activity_id, 'Shipment' as model"
			),
			'table' => $db->fullTableName($this->Shipment),
			'alias' => 'Shipment',
			'conditions' => array(
				'item_id' => $item_id
			),
			'joins' => array(
				array(
					'table' => 'shipment_detail',
					'alias' => 'ShipmentDetail',
					'conditions' => array(
						'Shipment.shipment_id = ShipmentDetail.shipment_id'
					)
				)
			)
		), $this->Shipment);


		if (isset($query['limit'])) {
			$offset = isset($query['offset']) ? $query['offset'] : 0;
			$limit = "limit $offset,{$query['limit']}";
		} else {
			$limit = '';
		}

		$rawQuery = "select * from ($orderQuery union all $giftQuery union all $rewardQuery union all $reviewQuery union all $shipmentQuery) as Activity order by activity_id desc $limit";

		if ($state == 'before' && isset($query['operation']) && $query['operation'] == 'count') {
			$query['raw'] = $rawQuery;
			return $query;
		}

		return $db->fetchAll($rawQuery);
	}

	public function _findByUser($state, $query, $results = array()) {

		if ($state == 'before' && (!isset($query['operation']) || $query['operation'] != 'count')) {
			return $query;
		}

		$user_id = $query['user_id'];

		$db = $this->getDataSource();

		$orderQuery = $db->buildStatement(array(
			'fields' => array(
				"order_id as activity_id, 'Order' as model"
			),
			'table' => $db->fullTableName($this->Order),
			'alias' => 'Order',
			'conditions' => array(
				'user_id' => $user_id
			)
		), $this->Order);

		$paypalQuery = $db->buildStatement(array(
			'fields' => array(
				"paypal_order_id as activity_id, 'PaypalOrder' as model"
			),
			'table' => $db->fullTableName($this->PaypalOrder),
			'alias' => 'PaypalOrder',
			'conditions' => array(
				'user_id' => $user_id
			)
		), $this->PaypalOrder);

		$giftQuery = $db->buildStatement(array(
			'fields' => array(
				"gift_id as activity_id, 'Gift' as model"
			),
			'table' => $db->fullTableName($this->Gift),
			'alias' => 'Gift',
			'conditions' => array(
				'anonymous = 0',
				'OR' => array(
					'sender_id' => $user_id,
					'recipient_id' => $user_id
				)
			)
		), $this->Gift);

		$rewardQuery = $db->buildStatement(array(
			'fields' => array(
				"reward_id as activity_id, 'Reward' as model"
			),
			'table' => $db->fullTableName($this->Reward->RewardRecipient),
			'alias' => 'RewardRecipient',
			'conditions' => array(
				'recipient_id' => $user_id
			)
		), $this->Reward->RewardRecipient);

		$reviewQuery = $db->buildStatement(array(
			'fields' => array(
				"review_id as activity_id, 'Review' as model"
			),
			'table' => $db->fullTableName($this->Review),
			'alias' => 'Review',
			'conditions' => array(
				'Rating.user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'rating',
					'alias' => 'Rating',
					'conditions' => array(
						'Review.rating_id = Rating.rating_id'
					)
				)
			),
		), $this->Review);


		if (isset($query['limit'])) {
			$offset = isset($query['offset']) ? $query['offset'] : 0;
			$limit = "limit $offset,{$query['limit']}";
		} else {
			$limit = '';
		}

		$rawQuery = "select * from ($orderQuery union all $paypalQuery union all $giftQuery union all $rewardQuery union all $reviewQuery) as Activity order by activity_id desc $limit";

		if ($state == 'before' && isset($query['operation']) && $query['operation'] == 'count') {
			$query['raw'] = $rawQuery;
			return $query;
		}

		return $db->fetchAll($rawQuery);
	}

	public function getRecent($data) {

		/*$data = Hash::extract($this->find('all', array(
			'limit' => $limit
		)), '{n}.Activity');*/

		$models = Hash::extract($data, '{n}.Activity.model');
		$ids = Hash::extract($data, '{n}.Activity.activity_id');

		$activities = array();

		if (in_array('Order', $models)) {
			$activities = array_merge(
				$activities,
				$this->Order->find('all', array(
					'conditions' => array(
						'order_id' => $ids
					),
					'contain' => 'OrderDetail'
				)
			));
		}

		if (in_array('PaypalOrder', $models)) {
			$activities = array_merge(
				$activities,
				$this->PaypalOrder->find('all', array(
					'conditions' => array(
						'paypal_order_id' => $ids
					)
				)
			));
		}

		if (in_array('Gift', $models)) {
			$activities = array_merge(
				$activities,
				$this->Gift->find('all', array(
					'conditions' => array(
						'gift_id' => $ids
					),
					'contain' => 'GiftDetail'
				)
			));
		}

		if (in_array('Reward', $models)) {
			$activities = array_merge(
				$activities,
				$this->Reward->find('all', array(
						'conditions' => array(
							'reward_id' => $ids
						),
						'contain' => array(
							'RewardDetail',
							'RewardRecipient'
						)
					)
			));
		}

		if (in_array('Review', $models)) {
			$activities = array_merge($activities, $this->Review->find('all', array(
				'fields' => array(
					'review_id', 'rating_id', 'created as date', 'created', 'modified', 'content'
				),
				'conditions' => array(
					'review_id' => $ids
				),
				'contain' => array(
					'Rating' => array(
						'fields' => array(
							'rating_id', 'item_id', 'user_id', 'rating'
						)
					)
				)
			)));
		}

		if (in_array('Shipment', $models)) {
			$activities = array_merge(
				$activities,
				$this->Shipment->find('all', array(
						'conditions' => array(
							'shipment_id' => $ids
						),
						'contain' => 'ShipmentDetail',
					)
			));
		}


		$activities = Hash::sort($activities, '{n}.{s}.date', 'desc');

		foreach ($activities as &$activity) {

			if (isset($activity['Order'])) {

				$subTotal = 0;

				foreach ($activity['OrderDetail'] as $detail) {
					$subTotal += $detail['price'] * $detail['quantity'];
				}

				$activity['Order']['subTotal'] = $subTotal;

				$activity['OrderDetail'] = Hash::combine(
					$activity['OrderDetail'],
					'{n}.item_id', '{n}.quantity'
				);

			} else if (isset($activity['GiftDetail'])) {

				$activity['GiftDetail'] = Hash::combine(
					$activity['GiftDetail'],
					'{n}.item_id', '{n}.quantity'
				);

			} else if (isset($activity['RewardDetail'])) {

				$activity['RewardDetail'] = Hash::combine(
					$activity['RewardDetail'],
					'{n}.item_id', '{n}.quantity'
				);

				if (isset($activity['RewardRecipient'])) {
					$activity['RewardRecipient'] = Hash::extract(
						$activity['RewardRecipient'], '{n}.recipient_id'
					);
				}

			} else if (isset($activity['ShipmentDetail'])) {

				$activity['ShipmentDetail'] = Hash::combine(
					$activity['ShipmentDetail'],
					'{n}.item_id', '{n}.quantity'
				);
			}
		}

		return $activities;
	}
}
