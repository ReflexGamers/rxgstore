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
     * Gets the daily sums of real money spent each day in a given month and puts it in a format
     * friendly to HighCharts.
     *
     * @param int $offsetMonth how many months ago (default 0)
     * @return array
     */
    public function getDailySums($offsetMonth = 0) {

        $month = date('n') - $offsetMonth;
        $startTime = mktime(0, 0, 0, $month, 1);

        $lastDay = ($offsetMonth === 0) ? date('d') : date('t', $startTime);
        $endTime = mktime(0, 0, 0, $month, $lastDay);

        $daysWithPurchases = array();

        $data = Hash::map($this->find('all', array(
            'fields' => array(
                'DAY(date) as day', "date_format(date, '%Y-%m-%d') as date", 'SUM(amount) as spent'
            ),
            'conditions' => array(
                'date >= ' => $this->formatTimestamp($startTime),
                'date <= ' => $this->formatTimestamp($endTime)
            ),
            'group' => 'DATE(date)'
        )), '{n}', function($arr) use (&$daysWithPurchases) {
            $daysWithPurchases[] = Hash::get($arr, '0.day');
            return array(
                Hash::get($arr, '0.date'),
                Hash::get($arr, '0.spent')
            );
        });

        // add missing days with 0
        for ($i = 1; $i <= $lastDay; $i++) {
            if (!in_array($i, $daysWithPurchases)) {
                $data[] = array(
                    date('Y-m-d', mktime(0, 0, 0, $month, $i)),
                    "0"
                );
            }
        }

        $data = Hash::sort($data, '{n}.0', 'asc', 'regular');

        return $data;
    }

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
