<?php
App::uses('AppModel', 'Model');
/**
 * GiveawayClaimDetail Model
 *
 * @property Item $Item
 * @property GiveawayClaim $GiveawayClaim
 */
class GiveawayClaimDetail extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'giveaway_claim_detail';
    public $primaryKey = 'giveaway_claim_detail_id';

    public $belongsTo = 'GiveawayClaim';
    public $hasMany = 'Item';

    public $order = 'GiveawayClaimDetail.giveaway_claim_detail_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'giveaway_claim_id' => array(
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
