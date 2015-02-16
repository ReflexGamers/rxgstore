<?php
App::uses('AppModel', 'Model');
/**
 * PromotionUser Model
 *
 * @property Activity $Activity
 * @property Promotion $Promotion
 * @property PromotionUserDetail $PromotionUserDetail
 * @property User $User
 */
class PromotionUser extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_user';
    public $primaryKey = 'promotion_user_id';

    public $belongsTo = array(
        'Activity', 'Promotion', 'User'
    );

    public $hasMany = 'PromotionUserDetail';

    public $order = 'PromotionUser.promotion_user_id DESC';


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
