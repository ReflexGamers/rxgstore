<?php
/**
 * FeatureFixture
 *
 */
class FeatureFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'feature';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'feature_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'description' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'feature_id', 'unique' => 1),
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
			'feature_id' => 1,
			'item_id' => 1,
			'description' => 'Lorem ipsum dolor sit amet'
		),
	);

}
