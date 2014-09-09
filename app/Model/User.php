<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Gift $Gift
 * @property Order $Order
 * @property PaypalOrder $PaypalOrder
 * @property Rating $Rating
 * @property RewardRecipient $RewardRecipient
 * @property QuickAuth $QuickAuth
 * @property SavedLogin $SavedLogin
 * @property ShoutboxMessage $ShoutboxMessage
 * @property UserItem $UserItem
 * @property UserServer $UserServer
 */
class User extends AppModel {

	public $useTable = 'user';
	public $primaryKey = 'user_id';

	public $hasOne = 'UserServer';

	public $hasMany = array(
		'Gift', 'Order', 'PaypalOrder', 'QuickAuth', 'Rating', 'RewardRecipient', 'SavedLogin', 'ShoutboxMessage', 'UserItem'
	);

	public function deletePreferredServer($user_id) {
		$this->UserServer->delete($user_id);
	}

	public function setPreferredServer($user_id, $server_name) {

		$server = Hash::extract($this->UserServer->Server->findByShortName($server_name, 'server_id'), 'Server');
		if (!empty($server)) {
			$this->UserServer->save(array(
				'user_id' => $user_id,
				'server_id' => $server['server_id']
			));
		}
	}

	public function getPreferredServer($user_id) {

		$server = Hash::extract($this->UserServer->find('first', array(
			'fields' => 'Server.short_name',
			'conditions' => array(
				'user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'server',
					'alias' => 'Server',
					'conditions' => array(
						'Server.server_id = UserServer.server_Id'
					)
				)
			)
		)), 'Server');

		return !empty($server) ? $server['short_name'] : '';
	}

	public function getCurrentServer($user_id) {

		$gameData = Hash::extract($this->read(array('server', 'ingame'), $user_id), 'User');

		if (!empty($gameData['server']) && $gameData['ingame'] + Configure::read('Store.MaxTimeToConsiderInGame') >= time()) {
			return $gameData['server'];
		} else {
			return '';
		}
	}

	public function getPendingGifts($user_id) {

		$gifts = $this->Gift->find('all', array(
			'conditions' => array(
				'recipient_id' => $user_id,
				'accepted = 0'
			),
			'contain' => array(
				'GiftDetail' => array(
					'fields' => array(
						'item_id', 'quantity'
					)
				)
			)
		));

		//Flatten Gift Details
		foreach ($gifts as &$gift) {
			$gift['GiftDetail'] = Hash::combine(
				$gift['GiftDetail'],
				'{n}.item_id', '{n}.quantity'
			);
		}

		return $gifts;
	}

	public function getPendingRewards($user_id) {

		$rewards = $this->RewardRecipient->find('all', array(
			'conditions' => array(
				'recipient_id' => $user_id,
				'accepted = 0'
			),
			'contain' => 'Reward.RewardDetail'
		));

		//Flatten Reward Details
		foreach ($rewards as &$reward) {
			$reward['Reward']['RewardDetail'] = Hash::combine(
				$reward['Reward']['RewardDetail'],
				'{n}.item_id', '{n}.quantity'
			);
		}

		return $rewards;
	}

	public function canRateItem($user_id, $item_id) {

		$order = $this->Order->find('first', array(
			'conditions' => array(
				'user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'order_detail',
					'conditions' => array(
						'Order.order_id = order_detail.order_id',
						'item_id' => $item_id
					)
				)
			)
		));

		return !empty($order);
	}

	public function getTotalSpent($user_id) {

		$itemTotal = $this->Order->find('all', array(
			'fields' => array(
				'SUM(order_detail.quantity * order_detail.price) as total'
			),
			'conditions' => array(
				'user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'order_detail',
					'conditions' => array(
						'Order.order_id = order_detail.order_id'
					)
				)
			)
		));

		if (empty($itemTotal)) return 0;
		$itemTotal = $itemTotal[0][0]['total'];

		$shippingTotal = $this->Order->findAllByUserId($user_id, array('SUM(shipping) as total'));

		return empty($shippingTotal) ? $itemTotal : $itemTotal + $shippingTotal[0][0]['total'];
	}

	public function getTotalBoughtByItem($user_id, $item_id) {

		$quantity = $this->Order->find('first', array(
			'fields' => array(
				'SUM(order_detail.quantity) as quantity'
			),
			'conditions' => array(
				'user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'order_detail',
					'conditions' => array(
						'Order.order_id = order_detail.order_id',
						'order_detail.item_id' => $item_id
					)
				)
			)
		));

		return empty($quantity) ? 0 : $quantity[0]['quantity'];
	}

	public function getReviews($user_id, $limit = 5) {

		return $this->Rating->find('all', array(
			'fields'  => array(
				'Rating.user_id', 'Rating.item_id', 'rating', 'review.review_id', 'review.created', 'review.modified', 'review.content', 'SUM(quantity) as quantity'
			),
			'conditions' => array(
				'Rating.user_id' => $user_id
			),
			'joins' => array(
				array(
					'table' => 'review',
					'conditions' => array(
						'Rating.rating_id = review.rating_id'
					)
				),
				array(
					'table' => 'order_detail',
					'conditions' => array(
						'Rating.item_id = order_detail.item_id'
					)
				),
				array(
					'table' => 'order',
					'conditions' => array(
						'order_detail.order_id = order.order_id',
						'order.user_id' => $user_id
					)
				)
			),
			'order' => 'quantity desc',
			'group' => 'item_id',
			'limit' => $limit
		));

	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'credit' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'ingame' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'last_activity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
