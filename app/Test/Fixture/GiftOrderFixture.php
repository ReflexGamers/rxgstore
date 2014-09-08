<?php
/**
 * GiftOrderFixture
 *
 */
class GiftOrderFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'gift_order';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'gift_order_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'gift_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'unique'),
		'order_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'unique'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'gift_order_id', 'unique' => 1),
			'gift_id' => array('column' => 'gift_id', 'unique' => 1),
			'order_id' => array('column' => 'order_id', 'unique' => 1)
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
			'gift_order_id' => 1,
			'gift_id' => 1,
			'order_id' => 1
		),
	);

}
