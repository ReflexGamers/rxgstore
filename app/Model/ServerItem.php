<?php
App::uses('AppModel', 'Model');
/**
 * ServerItem Model
 *
 * @property Item $Item
 * @property Server $Server
 */
class ServerItem extends AppModel {

    public $useTable = 'server_item';
    public $primaryKey = 'server_item_id';

    public $belongsTo = array(
        'Item', 'Server'
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
    );
}
