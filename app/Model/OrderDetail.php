<?php
App::uses('AppModel', 'Model');
/**
 * OrderDetail Model
 *
 * @property Order $Order
 * @property Item $Item
 */
class OrderDetail extends AppModel {

    public $useTable = 'order_detail';
    public $primaryKey = 'order_detail_id';

    public $belongsTo = array(
        'Order', 'Item'
    );


    /**
     * Gets the total amount of CASH spent on each item and puts it in a format friendly to HighCharts.
     *
     * @param int $since how far back to get data
     * @return array
     */
    public function getTotalsSpent($since = 0) {

        $query = array(
            'fields' => array(
                'Item.name as name', 'sum(OrderDetail.quantity * OrderDetail.price) as spent'
            ),
            'joins' => array(
                array(
                    'table' => 'item',
                    'alias' => 'Item',
                    'conditions' => array(
                        'Item.item_id = OrderDetail.item_id'
                    )
                )
            ),
            'group' => 'Item.name',
            'order' => 'spent desc'
        );

        if (!empty($since)) {
            $query['joins'][] = array(
                'table' => 'order',
                'alias' => 'Order',
                'conditions' => array(
                    'Order.order_id = OrderDetail.order_id'
                )
            );
            $query['conditions']['Order.date >'] = $this->formatTimestamp($since);
        }

        $data = $this->find('all', $query);

        return Hash::map($data, '{n}', function($arr) {
            return array(
                Hash::get($arr, 'Item.name'),
                Hash::get($arr, '0.spent')
            );
        });
    }

    /**
     * Gets the total amount of each item bought and puts it in a format friendly to HighCharts.
     *
     * @param int $since how far back to get data
     * @return array
     */
    public function getTotalsBought($since = 0) {

        $query = array(
            'fields' => array(
                'Item.name as name', 'sum(OrderDetail.quantity) as bought'
            ),
            'joins' => array(
                array(
                    'table' => 'item',
                    'alias' => 'Item',
                    'conditions' => array(
                        'Item.item_id = OrderDetail.item_id'
                    )
                )
            ),
            'group' => 'Item.name',
            'order' => 'bought desc'
        );

        if (!empty($since)) {
            $query['joins'][] = array(
                'table' => 'order',
                'alias' => 'Order',
                'conditions' => array(
                    'Order.order_id = OrderDetail.order_id'
                )
            );
            $query['conditions']['Order.date >'] = $this->formatTimestamp($since);
        }

        $data = $this->find('all', $query);

        return Hash::map($data, '{n}', function($arr) {
            return array(
                Hash::get($arr, 'Item.name'),
                Hash::get($arr, '0.bought')
            );
        });
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'order_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
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
        'price' => array(
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
