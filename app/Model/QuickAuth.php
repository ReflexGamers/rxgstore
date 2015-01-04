<?php
App::uses('AppModel', 'Model');
/**
 * QuickAuth Model
 *
 * @property User $User
 */
class QuickAuth extends AppModel {

    public $useTable = 'quick_auth';
    public $primaryKey = 'quick_auth_id';

    public $belongsTo = 'User';

    public $order = 'QuickAuth.date desc';


    /**
     * Gets the total number of QuickAuth uses per server and puts it in a format friendly to HighCharts.
     *
     * @return array
     */
    public function getTotalsForChart() {

        $data = $this->find('all', array(
            'fields' => array(
                'Server.name as server', 'count(QuickAuth.quick_auth_id) as count'
            ),
            'conditions' => array(
                'redeemed = 1'
            ),
            'joins' => array(
                array(
                    'table' => 'server',
                    'alias' => 'Server',
                    'conditions' => array(
                        'Server.server_ip = QuickAuth.server'
                    )
                )
            ),
            'group' => 'Server.name',
            'order' => 'count desc'
        ));

        return Hash::map($data, '{n}', function($arr) {
            return array(
                Hash::get($arr, 'Server.server'),
                Hash::get($arr, '0.count')
            );
        });
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
        'token' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'is_member' => array(
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
