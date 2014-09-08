<?php
App::uses('AppModel', 'Model');
/**
 * Rating Model
 *
 * @property Item $Item
 * @property User $User
 */
class Rating extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'rating';
	public $primaryKey = 'rating_id';

	public $hasOne = 'Review';

	public $belongsTo = array(
		'Item', 'User'
	);

	public $order = 'Rating.rating desc';

	public function getByItemAndUser($item_id, $user_id) {

		$rating = $this->find('first', array(
			'fields' => array(
				'rating_id', 'user_id', 'rating'
			),
			'conditions' => array(
				'item_id' => $item_id,
				'user_id' => $user_id
			),
			'contain' => array(
				'Review' => array(
					'fields' => array(
						'review_id', 'created', 'modified', 'content'
					)
				)
			)
		));

		return empty($rating) ? array() : array_merge($rating['Rating'], $rating['Review']);
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'item_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'rating' => array(
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
