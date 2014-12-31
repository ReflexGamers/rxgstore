<?php
App::uses('AppModel', 'Model');
/**
 * PaypalOrder Model
 *
 * @property Activity $Activity
 * @property User $User
 */
class PaypalOrder extends AppModel {

    public $useTable = 'paypal_order';
    public $primaryKey = 'paypal_order_id';

    public $belongsTo = array('Activity', 'User');

    public $order = 'PaypalOrder.paypal_order_id DESC';


    /**
     * Returns the top buyers of CASH. Top buyers are determined by the amount bought, not the amount spent.
     *
     * @param int $limit optional limit for number of top buyers to return
     * @return array
     */
    public function getTopBuyers($limit = 7) {

        return Hash::combine($this->find('all', array(
            'fields' => array(
                'user_id', 'SUM(credit) as received', 'SUM(amount) as spent'
            ),
            'group' => 'user_id',
            'order' => 'received desc',
            'limit' => $limit
        )), '{n}.PaypalOrder.user_id', '{n}.{n}');
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
        'amount' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'fees' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'credit' => array(
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
