<?php
App::uses('AppModel', 'Model');
/**
 * PromotionClaimDetail Model
 *
 * @property PromotionDetail $PromotionDetail
 * @property PromotionClaim $PromotionClaim
 */
class PromotionClaimDetail extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_claim_detail';
    public $primaryKey = 'promotion_claim_detail_id';

    public $belongsTo = array(
        'PromotionDetail', 'PromotionClaim'
    );

    public $order = 'PromotionClaim.promotion_claim_detail_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'promotion_claim_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'promotion_detail_id' => array(
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
