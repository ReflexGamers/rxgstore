<?php
App::uses('AppModel', 'Model');
/**
 * GiveawayDetail Model
 *
 * @property Item $Item
 * @property Giveaway $Giveaway
 * @property GiveawayClaimDetail $GiveawayClaimDetail
 */
class GiveawayDetail extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'giveaway_detail';
    public $primaryKey = 'giveaway_detail_id';

    public $belongsTo = array(
        'Item', 'Giveaway'
    );

    public $order = 'GiveawayDetail.giveaway_detail_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'giveaway_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'item_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'quantity' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
