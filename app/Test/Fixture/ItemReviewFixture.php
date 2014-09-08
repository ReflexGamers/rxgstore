<?php
/**
 * ReviewFixture
 *
 */
class ReviewFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'review';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'review_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'rating_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'created' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'content' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'modified' => array('type' => 'timestamp', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'review_id', 'unique' => 1),
			'rating_id' => array('column' => 'rating_id', 'unique' => 0)
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
			'review_id' => 1,
			'rating_id' => 1,
			'created' => 1406932195,
			'content' => 'Lorem ipsum dolor sit amet',
			'modified' => 1406932195
		),
	);

}
