<?php
App::uses('AppModel', 'Model');
/**
 * Feature Model
 *
 * @property Item $Item
 *
 * Magic Methods (for inspection):
 * @method findAllByItemId
 */
class Feature extends AppModel {

    public $useTable = 'feature';
    public $primaryKey = 'feature_id';

    public $belongsTo = 'Item';


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
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
        'description' => array(
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
