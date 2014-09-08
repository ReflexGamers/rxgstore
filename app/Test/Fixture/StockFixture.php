<?php
/**
 * StockFixture
 *
 */
class StockFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'stock';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'quantity' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'maximum' => array('type' => 'integer', 'null' => false, 'default' => '100', 'unsigned' => false),
		'ideal_quantity' => array('type' => 'integer', 'null' => false, 'default' => '50', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'item_id', 'unique' => 1)
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
			'item_id' => 1,
			'quantity' => 1,
			'maximum' => 1,
			'ideal_quantity' => 1
		),
	);

}
