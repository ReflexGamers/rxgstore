<?php
/**
 * ServerFixture
 *
 */
class ServerFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'server';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'server_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'short_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 12, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'server_id', 'unique' => 1)
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
			'server_id' => 1,
			'name' => 'Lorem ipsum dolor sit amet',
			'short_name' => 'Lorem ipsu'
		),
	);

}
