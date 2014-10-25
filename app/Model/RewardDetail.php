<?php
App::uses('AppModel', 'Model');
/**
 * RewardDetail Model
 *
 * @property Reward $Reward
 * @property Item $Item
 */
class RewardDetail extends AppModel {

    public $useTable = 'reward_detail';
    public $primaryKey = 'reward_detail_id';

    public $belongsTo = array(
        'Reward', 'Item'
    );

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'reward_id' => array(
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
