<?php
/**
 * QuickAuthFixture
 *
 */
class QuickAuthFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'quick_auth';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'quick_auth_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'token' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'is_member' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'quick_auth_id', 'unique' => 1),
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
			'quick_auth_id' => 1,
			'user_id' => 1,
			'token' => 1,
			'is_member' => 1
		),
	);

}
