<?php
App::uses('AppModel', 'Model');
/**
 * Item Model
 *
 * @property GiftDetail $GiftDetail
 * @property Rating $Rating
 * @property Stock $Stock
 * @property OrderDetail $OrderDetail
 * @property ServerItem $ServerItem
 * @property UserItem $UserItem
 */
class Item extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'item';
	public $primaryKey = 'item_id';

	public $hasOne = 'Stock';

	public $hasMany = array(
		'GiftDetail', 'Feature', 'Rating', 'OrderDetail', 'ServerItem', 'UserItem'
	);

	public $order = 'display_index';


	public function getAll() {
		return Hash::extract($this->find('all'), '{n}.Item');
	}

	public function getAllIndexed() {
		return Hash::combine($this->find('all', array('order' => 'item_id')), '{n}.Item.item_id', '{n}.Item');
	}

	public function getByServer($server) {

		if ($server == 'all') {
			return $this->getBuyable();
		}

		return $this->getBuyable(array(
			'joins' => array(
				array(
					'table' => 'server_item',
					'conditions' => array(
						'item.item_id = server_item.item_id'
					)
				),
				array(
					'table' => 'server',
					'conditions' => array(
						'server.short_name' => $server,
						'OR' => array(
							'server_item.server_id = server.server_id',
							'server_item.server_id = server.parent_id'
						)
					)
				)
			)
		));
	}

	public function getBuyable($opt = null) {

		$options = array(
			'conditions' => array('Item.buyable' => '1'),
			'fields' => array(
				'item_id', 'name', 'plural', 'short_name', 'price'
			)
		);

		if (isset($opt) && is_array($opt)) {
			$options = array_merge($options, $opt);
		}

		return Hash::extract($this->find('all', $options), '{n}.Item');
	}

	public function getStock($item_id = null) {

		$stock = $this->Stock->findByItemId($item_id);

		if (empty($stock)) {
			return false;
		}

		return $stock['Stock'];
	}

	public function getTopBuyers($item_id, $limit = 5) {

		return $this->OrderDetail->find('all', array(
			'fields' => array(
				'Order.user_id', 'SUM(OrderDetail.quantity) as quantity', 'SUM(OrderDetail.quantity * price) as total'
			),
			'joins' => array(
				array(
					'table' => 'order',
					'conditions' => array(
						'OrderDetail.order_id = Order.order_id',
						'OrderDetail.item_id' => $item_id
					)
				)
			),
			'group' => 'user_id',
			'order' => 'quantity desc',
			'limit' => $limit
		));
	}

	public function getReviews($item_id, $limit = 5) {

		return $this->Rating->find('all', array(
			'fields'  => array(
				'Rating.user_id', 'Rating.item_id', 'rating', 'review.review_id', 'review.created', 'review.modified', 'review.content', 'SUM(quantity) as quantity'
			),
			'joins' => array(
				array(
					'table' => 'review',
					'conditions' => array(
						'Rating.rating_id = review.rating_id',
						'Rating.item_id' => $item_id
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
						'Rating.user_id = order.user_id'
					)
				)
			),
			'order' => 'quantity desc',
			'group' => 'user_id',
			'limit' => $limit
		));
	}

	public function getTotalRatings($item_id) {

		$rating = $this->Rating->find('first', array(
			'fields' => array(
				'COUNT(*) as count, AVG(rating) as average'
			),
			'conditions' => array(
				'item_id' => $item_id
			)
		));

		return empty($rating) ? array('count' => 0, 'average' => 0) : $rating['0'];
	}

	public function getAllTotalRatings() {

		return $this->Rating->find('all', array(
			'fields' => array(
				'item_id, COUNT(*) as count, AVG(rating) as average'
			),
			'group' => 'item_id',
			'order' => 'item_id'
		));
	}


/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'display_index' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'plural' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'short_name' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'buyable' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'price' => array(
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
