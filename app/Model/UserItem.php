<?php
App::uses('AppModel', 'Model');
/**
 * UserItem Model
 *
 * @property User $User
 * @property Item $Item
 */
class UserItem extends AppModel {

    public $useTable = 'user_item';
    public $primaryKey = 'user_item_id';

    public $belongsTo = array(
        'Item', 'User'
    );

    public function getByUser($user_id, $opt = null) {

        $options = array(
            'fields' => array('item_id', 'quantity'),
            'conditions' => array(
                'user_id' => $user_id,
                'quantity > 0'
            )
        );

        if (isset($opt) && is_array($opt)) {
            $options = array_merge($options, $opt);
        }

        return $this->find('list', $options);
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'user_id' => array(
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
