<?php
/**
 * SavedLoginFixture
 *
 */
class SavedLoginFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'saved_login';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'saved_login_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'code' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'remoteip' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'expires' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => true),
		'indexes' => array(
			'PRIMARY' => array('column' => 'saved_login_id', 'unique' => 1),
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
			'saved_login_id' => 1,
			'user_id' => 1,
			'code' => 1,
			'remoteip' => 'Lorem ipsum dolor sit amet',
			'expires' => 1
		),
	);

}
