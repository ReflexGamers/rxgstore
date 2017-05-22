<?php
App::uses('AppModel', 'Model');
/**
 * Liquidation Model
 *
 * @property Activity $Activity
 * @property LiquidationDetail $LiquidationDetail
 * @property User $User
 *
 * Magic Methods (for inspection):
 * @method findAllByUserId
 */
class Liquidation extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'liquidation';
    public $primaryKey = 'liquidation_id';

    public $hasMany = 'LiquidationDetail';
    public $belongsTo = array('Activity', 'User');

    public $order = 'liquidation.liquidation_id DESC';


    /**
     * Generates and returns a unique id used for insertion.
     * @return int
     */
    private function generateId() {
        return $this->Activity->getNewId('Liquidation');
    }

    /**
     * Returns an array which can be used to save a new Liquidation record with associated details.
     *
     * @param int $liquidation_id
     * @param int $user_id
     * @param array $items list of item quantities to liquidate indexed by item_id
     * @param array $prices list of item prices indexed by item_id
     * @return array liquidation query with details
     */
    private function getSaveLiquidationQuery($liquidation_id, $user_id, $items, $prices) {
        $liquidationDetails = array();

        foreach ($items as $item_id => $quantity) {
            $liquidationDetails[] = array(
                'item_id' => $item_id,
                'quantity' => $quantity,
                'price' => $prices[$item_id]
            );
        }

        return array(
            'Liquidation' => array(
                'liquidation_id' => $liquidation_id,
                'user_id' => $user_id
            ),
            'LiquidationDetail' => $liquidationDetails
        );
    }

    /**
     * Returns the sum of item values.
     *
     * @param array $items list of item quantities indexed by item_id
     * @param array $prices list of item prices indexed by item_id
     * @return int
     */
    private function sumItemValues($items, $prices) {
        $total = 0;

        foreach ($items as $item_id => $quantity) {
            $total += $quantity * $prices[$item_id];
        }

        return $total;
    }

    /**
     * Liquidates the provided items at their current price.
     *
     * @throws InsufficientItemsException
     *
     * @param int $user_id the user for which to liquidate the items
     * @param array $items list of item quantities indexed by item_id
     */
    public function performLiquidation($user_id, $items) {

        try {
            $this->User->removeItems($user_id, $items);
        } catch (InsufficientItemsException $e) {
            throw $e;
        }

        $this->LiquidationDetail->Item->Stock->addStock($items);

        $itemPrices = $this->LiquidationDetail->Item->getPrices(array_keys($items));

        $saveLiquidationQuery = $this->getSaveLiquidationQuery($this->generateId(), $user_id, $items, $itemPrices);
        $result = $this->saveAssociated($saveLiquidationQuery, array('atomic' => false));

        $this->User->addCash($user_id, $this->sumItemValues($items, $itemPrices));

        return $result;
    }

    /**
     * Returns a query that can be used to fetch a page of liquidation activity.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param int $limit optional limit for number of liquidation events to return
     * @return array
     */
    public function getActivityQuery($limit = 5) {

        return array(
            'Liquidation' => array(
                'contain' => 'LiquidationDetail',
                'limit' => $limit
            )
        );
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
    );
}
