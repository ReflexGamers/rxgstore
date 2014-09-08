<?php
/**
 * RatingFixture
 *
 */
class RatingFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'rating';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'rating_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'rating' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'rating_id', 'unique' => 1),
			'user_id_2' => array('column' => array('user_id', 'item_id'), 'unique' => 1),
			'item_id' => array('column' => 'item_id', 'unique' => 0),
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
			'rating_id' => 1,
			'item_id' => 1,
			'user_id' => 1,
			'rating' => 1
		),
	);

}
