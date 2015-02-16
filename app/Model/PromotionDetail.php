<?php
App::uses('AppModel', 'Model');
/**
 * PromotionDetail Model
 *
 * @property Item $Item
 * @property Promotion $Promotion
 * @property PromotionUserDetail $PromotionUserDetail
 */
class PromotionDetail extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_detail';
    public $primaryKey = 'promotion_detail_id';

    public $belongsTo = array(
        'Item', 'Promotion'
    );

    public $hasMany = 'PromotionUserDetail';

    public $order = 'PromotionDetail.promotion_detail_id DESC';


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
