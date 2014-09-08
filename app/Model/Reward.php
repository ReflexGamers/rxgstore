<?php
App::uses('AppModel', 'Model');
/**
 * Reward Model
 *
 * @property Activity $Activity
 * @property User $Sender
 * @property RewardDetail $RewardDetail
 * @property RewardRecipient $RewardRecipient
 */
class Reward extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'reward';
	public $primaryKey = 'reward_id';

	public $hasMany = array(
		'RewardDetail', 'RewardRecipient'
	);

	public $belongsTo = array(
		'Activity',
		'Sender' => array(
			'className' => 'User',
			'foreignKey' => 'sender_id'
		)
	);

	public $order = 'Reward.reward_id DESC';


/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'sender_id' => array(
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
