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
     * Gets the yearly sums of real money spent each month and puts it in a format friendly to
     * HighCharts.
     *
     * @param int $yearCount maximum years of data
     * @return array
     */
    public function getYearlySums($yearCount = 10) {
        $thisYear = date('Y');
        $startYear = $thisYear - $yearCount + 1;
        $startTime = mktime(0, 0, 0, 1, 1, $startYear);
        $endTime = time();

        $yearsWithPurchases = array();

        $data = Hash::map(
            $this->find('all',
                array(
                    'fields' => array(
                        'YEAR(date) as year, SUM(amount) as spent'
                    ),
                    'conditions' => array(
                        'date >= ' => $this->formatTimestamp($startTime),
                        'date <= ' => $this->formatTimestamp($endTime)
                    ),
                    'group' => 'YEAR(date)',
                    'order' => 'YEAR(date) asc'
                )
            ),
            '{n}',
            function($arr) use (&$yearsWithPurchases) {
                $year = Hash::get($arr, '0.year');
                $amount = (int)Hash::get($arr, '0.spent');
                $yearsWithPurchases[] = $year;
                return array($year, $amount);
            });

        return $data;
    }

    /**
     * Gets the monthly sums of real money spent each month for the given year and puts it in a
     * format friendly to HighCharts.
     *
     * @param int $year
     * @return array
     */
    public function getMonthlySums($year) {

        $startTime = mktime(0, 0, 0, 1, 1, $year);
        $endTime = mktime(0, 0, 0, 1, 1, $year+1);

        $monthsWithPurchases = array();

        $data = Hash::map(
            $this->find('all',
                array(
                    'fields' => array(
                        'MONTH(date) as month', 'SUM(amount) as spent'
                    ),
                    'conditions' => array(
                        'date >= ' => $this->formatTimestamp($startTime),
                        'date < ' => $this->formatTimestamp($endTime)
                    ),
                    'group' => 'MONTH(date)',
                    'order' => 'MONTH(date) asc'
                )
            ),
            '{n}',
            function($arr) use (&$monthsWithPurchases) {
                $month = Hash::get($arr, '0.month');
                $amount = (int)Hash::get($arr, '0.spent');
                $monthsWithPurchases[] = $month;
                return array($month, $amount);
            }
        );

        // add missing months with amount = 0
        for ($i = 1; $i <= 12; $i++) {
            if (!in_array($i, $monthsWithPurchases)) {
                $data[] = array($i, 0);
            }
        }

        $data = Hash::sort($data, '{n}.0', 'asc');

        $newData = array();

        // month numbers to abbreviated names
        foreach ($data as $i => $row) {
            // Example: 'Apr'
            $month = DateTime::createFromFormat('!m', $row[0])->format('M');
            $amount = $row[1];
            $newData[] = array($month, $amount);
        }

        return $newData;
    }

    /**
     * Gets the daily sums of real money spent each day in a given month and puts it in a format
     * friendly to HighCharts.
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getDailySums($year, $month) {

        $startTime = mktime(0, 0, 0, $month, 1, $year);
        $endTime = mktime(0, 0, 0, $month + 1, 1, $year);

        $lastDayOfMonth = date('t', $startTime);

        $daysWithPurchases = array();

        $data = Hash::map(
            $this->find('all',
                array(
                    'fields' => array(
                        'DAY(date) as day', 'SUM(amount) as spent'
                    ),
                    'conditions' => array(
                        'date >= ' => $this->formatTimestamp($startTime),
                        'date < ' => $this->formatTimestamp($endTime)
                    ),
                    'group' => 'DAY(date)',
                    'order' => 'DAY(date) asc'
                )
            ),
            '{n}',
            function($arr) use (&$daysWithPurchases) {
                $day = (int)Hash::get($arr, '0.day');
                $amount = (int)Hash::get($arr, '0.spent');
                $daysWithPurchases[] = $day;
                return array($day, $amount);
            });

        // add missing days with 0
        for ($i = 1; $i <= $lastDayOfMonth; $i++) {
            if (!in_array($i, $daysWithPurchases)) {
                $data[] = array($i, 0);
            }
        }

        $data = Hash::sort($data, '{n}.0', 'asc');

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
