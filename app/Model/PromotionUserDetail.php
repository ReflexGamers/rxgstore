<?php
App::uses('AppModel', 'Model');
/**
 * PromotionUserDetail Model
 *
 * @property PromotionDetail $PromotionDetail
 * @property PromotionUser $PromotionUser
 */
class PromotionUserDetail extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_user_detail';
    public $primaryKey = 'promotion_user_detail_id';

    public $belongsTo = array(
        'PromotionDetail', 'PromotionUser'
    );

    public $order = 'PromotionUser.promotion_user_detail_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'promotion_user_id' => array(
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
