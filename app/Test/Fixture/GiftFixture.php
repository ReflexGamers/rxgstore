<?php
/**
 * GiftFixture
 *
 */
class GiftFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'gift';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'gift_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'sender_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'recipient_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'date' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'accepted' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_award' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'message' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'gift_id', 'unique' => 1),
			'sender_id' => array('column' => 'sender_id', 'unique' => 0),
			'recipient_id' => array('column' => 'recipient_id', 'unique' => 0)
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
			'gift_id' => 1,
			'sender_id' => 1,
			'recipient_id' => 1,
			'date' => 1406932194,
			'accepted' => 1,
			'is_award' => 1,
			'message' => 'Lorem ipsum dolor sit amet'
		),
	);

}
