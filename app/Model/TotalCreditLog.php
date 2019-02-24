<?php
App::uses('AppModel', 'Model');

/**
 * CreditLog Model
 */
class TotalCreditLog extends AppModel {

    public $useTable = 'total_credit_log';
    public $primaryKey = 'total_credit_log_id';

    public $order = 'TotalCreditLog.date asc';

    /**
     * Gets the total credit at all points in history and puts it in a format friendly to HighCharts.
     *
     * @return array
     */
    public function getAllTime() {

        $startDate = Hash::get($this->find('first', array(
            'fields' => array(
                "date_format(date, '%Y-%m-%d') as date"
            ),
            'group' => array('day(date)', 'TotalCreditLog.date')
        )), '0.date');

        $creditLog = Hash::extract($this->find('all', array(
            'fields' => array(
                'round(avg(amount)) as average'
            ),
            'group' => array('date(date)',  'TotalCreditLog.date')
        )), '{n}.0.average');

        $currencyMult = Configure::read('Store.CurrencyMultiplier');

        return compact('startDate', 'creditLog', 'currencyMult');
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
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
    );
}