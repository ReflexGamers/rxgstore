<?php
App::uses('AppModel', 'Model');
/**
 * Order Model
 *
 * @property Activity $Activity
 * @property OrderDetail $OrderDetail
 * @property User $User
 */
class Order extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'order';
	public $primaryKey = 'order_id';

	public $hasMany = 'OrderDetail';
	public $belongsTo = array('Activity', 'User');

	public $order = 'Order.order_id DESC';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
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
	);
}
