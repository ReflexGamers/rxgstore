<?php
App::uses('AppModel', 'Model');
/**
 * Item Model
 *
 * @property GiftDetail $GiftDetail
 * @property Rating $Rating
 * @property Stock $Stock
 * @property OrderDetail $OrderDetail
 * @property ServerItem $ServerItem
 * @property UserItem $UserItem
 */
class Item extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'item';
    public $primaryKey = 'item_id';

    public $hasOne = 'Stock';

    public $hasMany = array(
        'GiftDetail', 'Feature', 'Rating', 'OrderDetail', 'ServerItem', 'UserItem'
    );

    public $order = 'display_index';


    /**
     * Returns a array of all items sorted by display_index.
     *
     * @return array
     */
    public function getAllSorted() {
        return Hash::extract($this->find('all'), '{n}.Item');
    }

    /**
     * Returns an array of all items usable in a specific server, sorted by display_index. The result will include items
     * directly associated with the server as well as those associated with the server's parent.
     *
     * @param string $server the short_name or ip address of the server
     * @return array
     */
    public function getByServer($server) {

        if ($server == 'all') {
            return $this->getBuyable(array(
                'contain' => array(
                    'Feature' => array(
                        'fields' => array('description')
                    )
                )
            ));
        }

        return $this->getBuyable(array(
            'joins' => array(
                array(
                    'table' => 'server_item',
                    'conditions' => array(
                        'Item.item_id = server_item.item_id'
                    )
                ),
                array(
                    'table' => 'server',
                    'conditions' => array(
                        'server.short_name' => $server,
                        'server.server_ip' => $server,
                        'OR' => array(
                            'server_item.server_id = server.server_id',
                            'server_item.server_id = server.parent_id'
                        )
                    )
                ),
            ),
            'contain' => array(
                'Feature' => array(
                    'fields' => array('description')
                )
            )
        ));
    }

    /**
     * Returns all buyable items sorted by display index.
     *
     * @param array $opt options to pass for filtering the query
     * @return array
     */
    public function getBuyable($opt = null) {

        $options = array(
            'conditions' => array('Item.buyable' => '1'),
            'fields' => array(
                'item_id', 'name', 'plural', 'short_name', 'price'
            )
        );

        if (isset($opt) && is_array($opt)) {
            $options = array_merge_recursive($options, $opt);
        }

        $data = $this->find('all', $options);

        $items = Hash::map($data, '{n}', function($item){
            if (!empty($item['Feature'])) {
                $item['Item']['features'] = Hash::extract($item['Feature'], '{n}.description');
            }
            return $item['Item'];
        });

        return $items;
    }

    /**
     * Returns the quantity in stock for a specific item.
     *
     * @param int $item_id
     * @return int|false the quantity or false if no record found
     */
    public function getStock($item_id = null) {

        $stock = $this->Stock->findByItemId($item_id);

        if (empty($stock)) {
            return false;
        }

        return $stock['Stock'];
    }

    /**
     * Returns the top buyers for a specific item. Top buyers are determined by the quantity bought, not the amount
     * spent.
     *
     * @param $item_id
     * @param int $limit optional limit for number of top buyers to return
     * @return array
     */
    public function getTopBuyers($item_id, $limit = 5) {

        return $this->OrderDetail->find('all', array(
            'fields' => array(
                'Order.user_id', 'SUM(OrderDetail.quantity) as quantity', 'SUM(OrderDetail.quantity * price) as total'
            ),
            'joins' => array(
                array(
                    'table' => 'order',
                    'alias' => 'Order',
                    'conditions' => array(
                        'OrderDetail.order_id = Order.order_id',
                        'OrderDetail.item_id' => $item_id
                    )
                )
            ),
            'group' => 'user_id',
            'order' => 'quantity desc',
            'limit' => $limit
        ));
    }

    /**
     * Returns a query that can be used to fetch a page of reviews for a specific item.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param int $item_id
     * @param int $limit optional limit for number of reviews to return
     * @return array query to be passed into paginator
     */
    public function getReviews($item_id, $limit = 3) {

        return array(
            'Rating' => array(
                'fields'  => array(
                    'Rating.user_id', 'Rating.item_id', 'rating', 'review.review_id', 'review.created', 'review.modified', 'review.content', 'SUM(quantity) as quantity'
                ),
                'joins' => array(
                    array(
                        'table' => 'review',
                        'conditions' => array(
                            'Rating.rating_id = review.rating_id',
                            'Rating.item_id' => $item_id
                        )
                    ),
                    array(
                        'table' => 'order_detail',
                        'conditions' => array(
                            'Rating.item_id = order_detail.item_id'
                        )
                    ),
                    array(
                        'table' => 'order',
                        'conditions' => array(
                            'order_detail.order_id = order.order_id',
                            'Rating.user_id = order.user_id'
                        )
                    )
                ),
                'order' => 'quantity desc',
                'group' => 'order.user_id',
                'limit' => $limit
            )
        );
    }

    /**
     * Returns the average ratings for a specific item.
     *
     * @param int $item_id
     * @return array with two keys, 'count' for the number of ratings and 'average' for the average of them all
     */
    public function getRating($item_id) {

        $rating = $this->Rating->find('first', array(
            'fields' => array(
                'COUNT(*) as count, AVG(rating) as average'
            ),
            'conditions' => array(
                'item_id' => $item_id
            )
        ));

        return empty($rating) ? array('count' => 0, 'average' => 0) : $rating['0'];
    }

    /**
     * Returns an array of the total ratings for all items indexed by item_id.
     *
     * @return array of ratings (decimals) indexed by item_id
     */
    public function getAllRatings() {

        return Hash::combine(Hash::map(
            $this->Rating->find('all', array(
                'fields' => array(
                    'item_id, COUNT(*) as count, AVG(rating) as average'
                ),
                'group' => 'item_id',
                'order' => 'item_id'
            )),
            '{n}', function($arr){
                return array_merge($arr['Rating'], $arr[0]);
            }
        ), '{n}.item_id', '{n}');
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'display_index' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'name' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'plural' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'short_name' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'buyable' => array(
            'boolean' => array(
                'rule' => array('boolean'),
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
