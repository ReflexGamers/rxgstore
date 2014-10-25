<?php
App::uses('AppModel', 'Model');
/**
 * RewardRecipient Model
 *
 * @property Reward $Reward
 * @property User $Recipient
 */
class RewardRecipient extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'reward_recipient';
    public $primaryKey = 'reward_recipient_id';

    public $belongsTo = array(
        'Reward',
        'Recipient' => array(
            'className' => 'User',
            'foreignKey' => 'recipient_id'
        )
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
        'recipient_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'accepted' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
