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
