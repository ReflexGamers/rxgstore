<?php
App::uses('AppModel', 'Model');
/**
 * PromotionClaim Model
 *
 * @property Activity $Activity
 * @property Promotion $Promotion
 * @property PromotionClaimDetail $PromotionClaimDetail
 * @property User $User
 */
class PromotionClaim extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_claim';
    public $primaryKey = 'promotion_claim_id';

    public $belongsTo = array(
        'Activity', 'Promotion', 'User'
    );

    public $hasMany = 'PromotionClaimDetail';

    public $order = 'PromotionClaim.promotion_claim_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'promotion_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'user_id' => array(
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
