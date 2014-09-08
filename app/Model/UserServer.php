<?php
App::uses('AppModel', 'Model');
/**
 * UserServerModel
 *
 * @property Server $Server
 * @property User $User
 */
class UserServer extends AppModel {

	public $useTable = 'user_server';
	public $primaryKey = 'user_id';

	public $belongsTo = array(
		'Server', 'User'
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'server_id' => array(
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
