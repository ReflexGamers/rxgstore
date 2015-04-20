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
     * Gets the monthly sums of real money spent each month in the past year and puts it in a format
     * friendly to HighCharts.
     *
     * @param int $monthCount how many past months to show
     * @return array
     */
    public function getMonthlySums($monthCount = 12) {

        $thisMonth = date('n');
        $startMonth = $thisMonth - $monthCount;
        $startTime = mktime(0, 0, 0, $startMonth, 1);
        $endTime = time();

        $monthsWithPurchases = array();

        $data = Hash::map($this->find('all', array(
            'fields' => array(
                'YEAR(date) as year, MONTH(date) as month', 'SUM(amount) as spent'
            ),
            'conditions' => array(
                'date >= ' => $this->formatTimestamp($startTime),
                'date <= ' => $this->formatTimestamp($endTime)
            ),
            'group' => 'YEAR(date), MONTH(date)',
            'order' => 'YEAR(date) asc, MONTH(date) asc'
        )), '{n}',
            function($arr) use (&$monthsWithPurchases) {
                $month = Hash::get($arr, '0.month');
                $monthsWithPurchases[] = $month;
                return array(
                    Hash::get($arr, '0.year'),
                    $month,
                    (int)Hash::get($arr, '0.spent')
                );
            }
        );

        // add missing months with 0
        for ($i = 1; $i <= 12; $i++) {
            if (!in_array($i, $monthsWithPurchases)) {
                $year = date('Y');
                if ($i > $thisMonth) {
                    $year--;
                }
                $data[] = array(
                    "$year",
                    "$i",
                    0
                );
            }
        }

        usort($data, function($a, $b) {
            $aYear = (int)$a[0];
            $bYear = (int)$b[0];
            $aMonth = (int)$a[1];
            $bMonth = (int)$b[1];

            if ($aYear > $bYear) {
                return 1;
            } else if ($bYear > $aYear) {
                return -1;
            } else {
                return ($aMonth > $bMonth) ? 1 : -1;
            }
        });

        for ($i = 0; $i < count($data); $i++) {
            if (empty($data[$i])) {
                $data[$i] = array(
                    $data[$i][0],
                    DateTime::createFromFormat('!m', $i)->format('M'),
                    '0'
                );
            } else {
                $row = $data[$i];
                $data[$i] = array(
                    $row[0],
                    DateTime::createFromFormat('!m', $row[1])->format('M'),
                    $row[2]
                );
            }
        }

        // simplify names
        foreach ($data as $i => $row) {
            // Example: 'Apr 2015'
            $name = "{$row[1]} {$row[0]}";
            $data[$i] = array($name, $row[2]);
        }

        return $data;
    }

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

        if ($offsetMonth === 0) {
            $lastDayOfMonth = date('d');
            $endTime = time();
        } else {
            $lastDayOfMonth = date('t', $startTime);
            $endTime = mktime(0, 0, 0, $month, $lastDayOfMonth);
        }

        $daysWithPurchases = array();

        $data = Hash::map($this->find('all', array(
            'fields' => array(
                'DAY(date) as day', 'SUM(amount) as spent'
            ),
            'conditions' => array(
                'date >= ' => $this->formatTimestamp($startTime),
                'date <= ' => $this->formatTimestamp($endTime)
            ),
            'group' => 'DATE(date)'
        )), '{n}', function($arr) use (&$daysWithPurchases) {
            $daysWithPurchases[] = Hash::get($arr, '0.day');
            return array(
                (int)Hash::get($arr, '0.day'),
                (int)Hash::get($arr, '0.spent')
            );
        });

        // add missing days with 0
        for ($i = 1; $i <= $lastDayOfMonth; $i++) {
            if (!in_array($i, $daysWithPurchases)) {
                $data[] = array(
                    $i,
                    0
                );
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
