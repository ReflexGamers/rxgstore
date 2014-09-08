<?php
/**
 * ShoutboxMessageFixture
 *
 */
class ShoutboxMessageFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'shoutbox_message';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'shoutbox_message_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'date' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'content' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'shoutbox_message_id', 'unique' => 1),
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
			'shoutbox_message_id' => 1,
			'user_id' => 1,
			'date' => 1406932197,
			'content' => 'Lorem ipsum dolor sit amet'
		),
	);

}
