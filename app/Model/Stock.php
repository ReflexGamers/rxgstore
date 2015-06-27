<?php
App::uses('AppModel', 'Model');
/**
 * Stock Model
 *
 * @property Item $Item
 *
 * Magic Methods (for inspection):
 * @method findByItemId
 */
class Stock extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'stock';
    public $primaryKey = 'item_id';

    public $belongsTo = 'Item';

    public $displayField = 'quantity';


    /**
     * Performs automatic stocking. Updates current and maximum stock. Uses getAutoStockAmounts() to
     * determine quantities.
     *
     * @log [auto_stock.log] for each item that the maximum is updated
     * @param int [$days=7] the number of days for which to suggest stock
     * @return array
     */
    public function autoStock($days = 7) {

        $amounts = $this->getAutoStockAmounts($days);

        $oldStock = $amounts['old'];
        $newStock = $amounts['new'];
        $addStock = $amounts['add'];

        // log changes to maximum stock
        foreach ($newStock as $item_id => $item) {
            $oldMax = $oldStock[$item_id]['maximum'];
            $newMax = $item['maximum'];

            if ($newMax != $oldMax) {
                CakeLog::write('auto_stock', "Updated maximum stock for item #$item_id from $oldMax to $newMax");
            }
        }

        // save updated maximums
        $this->saveMany($newStock, array(
            'fields' => array('maximum'),
            'atomic' => false
        ));

        // add new stock
        $addedStock = $this->addStock($addStock);

        // do not save a shipment if nothing stocked
        if (empty($addedStock)) {
            return array();
        }

        // save shipment
        $this->Item->ShipmentDetail->Shipment->saveShipment($addedStock);

        return $addStock;
    }

    /**
     * Calculates how much should be stocked and returns the resulting amounts. This returns an
     * array with three keys which contain child arrays:
     *
     * array(
     *   'old' => // current stock: item_id => array(item_id, quantity, minimum, maximum)
     *   'new' => // new proposed stock: item_id => array(item_id, quantity, maximum)
     *   'add' => // proposed additions: item_id => quantity
     * )
     *
     * @param int [$days=7] the number of days for which to suggest stock
     * @return array array with keys 'old', 'new' and 'add'
     */
    public function getAutoStockAmounts($days = 7) {

        $config = Configure::read('Store.AutoStock');

        $suggestedStock = $this->getSuggestedStock($days);
        $itemsToStock = array_keys($suggestedStock);

        // get current stock
        $currentStock = Hash::combine($this->find('all', array(
            'fields' => array(
                'item_id', 'quantity', 'minimum', 'maximum'
            ),
            'conditions' => array(
                'item_id' => $itemsToStock
            )
        )), '{n}.Stock.item_id', '{n}.Stock');

        $newStock = array();
        $addStock = array();

        // calculate how much to add to reach suggested values
        foreach ($currentStock as $item_id => $item) {
            $quantity = $item['quantity'];
            $minimum = $item['minimum'];
            $suggested = $suggestedStock[$item_id];

            $newMaximum = $this->dynamicRound($suggested * $config['MaxStockMult']);

            $newStock[$item_id] = array(
                'item_id' => $item_id,
                'quantity' => $suggested,
                'maximum' => $newMaximum
            );

            // avoid micro stocking
            if ($quantity < $minimum || $quantity < $suggested * $config['AntiMicroThreshold']) {
                $addStock[$item_id] = $suggested - $quantity;
            }
        }

        return array(
            'old' => $currentStock,
            'new' => $newStock,
            'add' => $addStock
        );
    }

    /**
     * Returns an array of suggested stock quantities for all items. This computes the average
     * purchased for a given week in the past month, as well as the amount in the past week. The
     * suggested value is whichever is greater times the Store.AutoStock.OverStockMult config value
     * in rxgstore.php.
     *
     * @param int [$days=7] the number of days for which to suggest stock
     * @return array
     */
    public function getSuggestedStock($days = 7) {

        $config = Configure::read('Store.AutoStock');

        $OrderDetail = $this->Item->OrderDetail;

        $longDays = $days * 4;

        $monthAgo = strtotime("-$longDays days");
        $weekAgo = strtotime("-$days days");

        $query = array(
            'joins' => array(
                array(
                    'table' => 'order',
                    'alias' => 'Order',
                    'conditions' => array(
                        'Order.order_id = OrderDetail.order_id'
                    )
                )
            ),
            'group' => 'OrderDetail.item_id'
        );

        // calc average for each week this month
        $monthQuery = array_merge_recursive($query, array(
            'fields' => array(
                'OrderDetail.item_id', 'coalesce(ceil(sum(OrderDetail.quantity) / 4), 0) as total'
            ),
            'conditions' => array(
                'Order.date >' => $this->formatTimestamp($monthAgo)
            )
        ));

        // calc average past week
        $weekQuery = array_merge_recursive($query, array(
            'fields' => array(
                'OrderDetail.item_id', 'coalesce(sum(OrderDetail.quantity), 0) as total'
            ),
            'conditions' => array(
                'Order.date >' => $this->formatTimestamp($weekAgo)
            )
        ));

        $monthData = Hash::combine($OrderDetail->find('all', $monthQuery), '{n}.OrderDetail.item_id', '{n}.0.total');
        $weekData = Hash::combine($OrderDetail->find('all', $weekQuery), '{n}.OrderDetail.item_id', '{n}.0.total');

        $uniqueItems = array_unique(array_merge(
            array_keys($monthData), array_keys($weekData)
        ));

        $suggestedStock = array();

        foreach ($uniqueItems as $item_id) {
            $weekTotal = !empty($weekData[$item_id]) ? $weekData[$item_id] : 0;
            $monthTotal = $monthData[$item_id];
            $suggestedStock[$item_id] = $this->dynamicRound(max($monthTotal, $weekTotal) * $config['OverStockMult']);
        }

        return $suggestedStock;
    }

    /**
     * Rounds the number to the nearest N depending on the size of the number.
     *
     * @param int $num the number to round
     * @return int the rounded number
     */
    private function dynamicRound($num) {
        if ($num < 50) {
            return ceil($num / 5) * 5;
        } else if ($num < 100) {
            return ceil($num / 10) * 10;
        } else if ($num < 250) {
            return ceil($num / 25) * 25;
        } else if ($num < 500) {
            return ceil($num / 50) * 50;
        } else {
            return ceil($num / 100) * 100;
        }
    }

    /**
     * Adds the specified amount of each item to the current stock, up to the current maximum for
     * each item. This returns an array of quantities actually stocked in case that differs from
     * what you requested. You should call this before saving a shipment for accurate records.
     *
     * @param array $items list of item quantities indexed by item_id
     * @return array list of item quantities actually stocked
     */
    public function addStock($items) {

        $this->query('LOCK TABLES stock WRITE');

        // get current stock for all items in list
        $allStock = Hash::combine($this->find('all', array(
            'fields' => array(
                'item_id', 'quantity', 'maximum'
            ),
            'conditions' => array(
                'item_id' => array_keys($items)
            )
        )), '{n}.Stock.item_id', '{n}.Stock');

        // this will store items actually stocked (excluding those that were at maximum or negative)
        $amountsStocked = array();

        // attempt to modify stocks
        foreach ($items as $item_id => $addStock) {
            $stockData = &$allStock[$item_id];

            $currentStock = $stockData['quantity'];
            $maxStock = $stockData['maximum'];

            // do not stock negative amounts
            $addStock = max($addStock, 0);

            // do not stock more than up to the maximum
            $newStock = min($currentStock + $addStock, $maxStock);

            $addStock = $newStock - $currentStock;

            if ($addStock > 0) {
                $stockData['quantity'] = $newStock;
                $amountsStocked[$item_id] = $addStock;
            } else {
                // no change
                unset($allStock[$item_id]);
            }
        }

        $this->saveMany($allStock, array(
            'fields' => array('quantity'),
            'atomic' => false
        ));

        $this->query('UNLOCK TABLES');

        return $amountsStocked;
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
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
        'maximum' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'ideal_quantity' => array(
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
