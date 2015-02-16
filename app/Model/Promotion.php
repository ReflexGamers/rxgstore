<?php
App::uses('AppModel', 'Model');
/**
 * Promotion Model
 *
 * @property PromotionDetail $PromotionDetail
 * @property PromotionUser $PromotionUser
 */
class Promotion extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion';
    public $primaryKey = 'promotion_id';

    public $hasMany = array(
        'PromotionDetail', 'PromotionUser'
    );

    public $order = 'Promotion.promotion_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'name' => array(
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
