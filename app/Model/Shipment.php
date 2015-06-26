<?php
App::uses('AppModel', 'Model');
/**
 * Shipment Model
 *
 * @property Activity $Activity
 * @property ShipmentDetail $ShipmentDetail
 * @property User $User
 */
class Shipment extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'shipment';
    public $primaryKey = 'shipment_id';

    public $hasMany = 'ShipmentDetail';

    public $belongsTo = array(
        'Activity', 'User'
    );

    public $order = 'Shipment.shipment_id DESC';


    /**
     * Saves a shipment with its details given an array of item quantities indexed by item_id.
     *
     * @param array $items list of item quantities indexed by item_id
     * @param int $user_id the user with which to associate the shipment
     * @return array the save result
     */
    public function saveShipment($items, $user_id = 0) {

        $shipmentDetail = array();

        // create details
        foreach ($items as $item_id => $quantity) {
            $shipmentDetail[] = array(
                'item_id' => $item_id,
                'quantity' => $quantity
            );
        }

        return $this->saveAssociated(array(
            'Shipment' => array(
                'shipment_id' => $this->Activity->getNewId('Shipment'),
                'user_id' => $user_id
            ),
            'ShipmentDetail' => $shipmentDetail
        ), array('atomic' => false));
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'user_id' => array(
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
