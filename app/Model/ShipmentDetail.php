<?php
App::uses('AppModel', 'Model');
/**
 * ShipmentDetail Model
 *
 * @property Item $Item
 * @property Shipment $Shipment
 */
class ShipmentDetail extends AppModel {

	public $useTable = 'shipment_detail';
	public $primaryKey = 'shipment_detail_id';

	public $belongsTo = array(
		'Item', 'Shipment'
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'shipment_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'item_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}
