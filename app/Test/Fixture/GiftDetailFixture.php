<?php
/**
 * GiftDetailFixture
 *
 */
class GiftDetailFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'gift_detail';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'gift_detail_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'gift_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'gift_detail_id', 'unique' => 1),
			'gift_id' => array('column' => array('gift_id', 'item_id'), 'unique' => 1),
			'item_id' => array('column' => 'item_id', 'unique' => 0)
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
			'gift_detail_id' => 1,
			'gift_id' => 1,
			'item_id' => 1,
			'quantity' => 1
		),
	);

}
