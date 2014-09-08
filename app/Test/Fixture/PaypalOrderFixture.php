<?php
/**
 * PaypalOrderFixture
 *
 */
class PaypalOrderFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'paypal_order';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'paypal_order_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'date' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'ppsaleid' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'amount' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'fees' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'credit' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'paypal_order_id', 'unique' => 1),
			'user_id' => array('column' => 'user_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'paypal_order_id' => 1,
			'user_id' => 1,
			'date' => 1406932196,
			'ppsaleid' => 'Lorem ipsum dolor sit amet',
			'amount' => 1,
			'fees' => 1,
			'credit' => 1
		),
	);

}
