<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Gift $Gift
 * @property Order $Order
 * @property PaypalOrder $PaypalOrder
 * @property Rating $Rating
 * @property RewardRecipient $RewardRecipient
 * @property QuickAuth $QuickAuth
 * @property SavedLogin $SavedLogin
 * @property ShoutboxMessage $ShoutboxMessage
 * @property UserItem $UserItem
 * @property UserServer $UserServer
 */
class User extends AppModel {

    public $useTable = 'user';
    public $primaryKey = 'user_id';

    public $hasOne = 'UserServer';

    public $hasMany = array(
        'Gift', 'Order', 'PaypalOrder', 'QuickAuth', 'Rating', 'RewardRecipient', 'SavedLogin', 'ShoutboxMessage', 'UserItem'
    );

    /**
     * Deletes the preferred server for the specified user.
     *
     * @param int $user_id
     */
    public function deletePreferredServer($user_id) {
        $this->UserServer->delete($user_id);
    }

    /**
     * Saves the preferred server to the specified user.
     *
     * @param int $user_id
     * @param string $server_name the short_name of the server to save
     */
    public function setPreferredServer($user_id, $server_name) {

        $server = Hash::extract($this->UserServer->Server->findByShortName($server_name, 'server_id'), 'Server');
        if (!empty($server)) {
            $this->UserServer->save(array(
                'user_id' => $user_id,
                'server_id' => $server['server_id']
            ));
        }
    }

    /**
     * Returns the short_name of the preferred server for the specified user.
     *
     * @param int $user_id
     * @return string the short_name of the preferred server
     */
    public function getPreferredServer($user_id) {

        $server = Hash::extract($this->UserServer->find('first', array(
            'fields' => 'Server.short_name',
            'conditions' => array(
                'user_id' => $user_id
            ),
            'joins' => array(
                array(
                    'table' => 'server',
                    'alias' => 'Server',
                    'conditions' => array(
                        'Server.server_id = UserServer.server_Id'
                    )
                )
            )
        )), 'Server');

        return !empty($server) ? $server['short_name'] : '';
    }

    /**
     * Returns the IP address of the server to which the specified user is currently connected, if applicable. For this
     * to return the player's server, that person must have the 'server' field set, and his/her 'ingame' field should be
     * less than Store.MaxTimeToConsiderInGame, or it is assumed the server crashed or something.
     *
     * @param int $user_id
     * @return string|false the ip address of the server, or false if they are not considered in-game
     */
    public function getCurrentServer($user_id) {

        $gameData = Hash::extract($this->read(array('server', 'ingame'), $user_id), 'User');

        if (!empty($gameData['server']) && $gameData['ingame'] + Configure::read('Store.MaxTimeToConsiderInGame') >= time()) {
            return $gameData['server'];
        } else {
            return false;
        }
    }

    /**
     * Returns an array of all pending gifts for the specified user. Each gift will be an array of quantities indexed
     * by item_id, which represents how many of each item is in the gift.
     *
     * @param int $user_id
     * @return array of gifts
     */
    public function getPendingGifts($user_id) {

        $gifts = $this->Gift->find('all', array(
            'conditions' => array(
                'recipient_id' => $user_id,
                'accepted = 0'
            ),
            'contain' => array(
                'GiftDetail' => array(
                    'fields' => array(
                        'item_id', 'quantity'
                    )
                )
            )
        ));

        // flatten gift details
        foreach ($gifts as &$gift) {
            $gift['GiftDetail'] = Hash::combine(
                $gift['GiftDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        return $gifts;
    }

    /**
     * Returns an array of all pending rewards for the specified user. Each reward will be an array of quantities
     * indexed by item_id, which represents how many of each item is in the reward.
     *
     * @param int $user_id
     * @return array of rewards
     */
    public function getPendingRewards($user_id) {

        $rewards = $this->RewardRecipient->find('all', array(
            'conditions' => array(
                'recipient_id' => $user_id,
                'accepted = 0'
            ),
            'contain' => 'Reward.RewardDetail'
        ));

        // flatten reward details
        foreach ($rewards as &$reward) {
            $reward['Reward']['RewardDetail'] = Hash::combine(
                $reward['Reward']['RewardDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        return $rewards;
    }

    /**
     * Returns true or false depending on whether the specified user can rate the item.
     *
     * @param int $user_id
     * @param int $item_id
     * @return bool
     */
    public function canRateItem($user_id, $item_id) {

        $order = $this->Order->find('first', array(
            'conditions' => array(
                'user_id' => $user_id
            ),
            'joins' => array(
                array(
                    'table' => 'order_detail',
                    'conditions' => array(
                        'Order.order_id = order_detail.order_id',
                        'item_id' => $item_id
                    )
                )
            )
        ));

        return !empty($order);
    }

    /**
     * Returns the total amount of CASH the specified user has spent on items.
     *
     * @param int $user_id
     * @return int the total amount spent
     */
    public function getTotalSpent($user_id) {

        $itemTotal = $this->Order->find('all', array(
            'fields' => array(
                'SUM(order_detail.quantity * order_detail.price) as total'
            ),
            'conditions' => array(
                'user_id' => $user_id
            ),
            'joins' => array(
                array(
                    'table' => 'order_detail',
                    'conditions' => array(
                        'Order.order_id = order_detail.order_id'
                    )
                )
            )
        ));

        if (empty($itemTotal)) return 0;
        $itemTotal = $itemTotal[0][0]['total'];

        $shippingTotal = $this->Order->findAllByUserId($user_id, array('SUM(shipping) as total'));

        return empty($shippingTotal) ? $itemTotal : $itemTotal + $shippingTotal[0][0]['total'];
    }

    /**
     * Returns the total quantity of a specific item that a user purchased.
     *
     * @param int $user_id
     * @param int $item_id
     * @return int the number of items the user purchased
     */
    public function getTotalBoughtOfItem($user_id, $item_id) {

        $quantity = $this->Order->find('first', array(
            'fields' => array(
                'SUM(order_detail.quantity) as quantity'
            ),
            'conditions' => array(
                'user_id' => $user_id
            ),
            'joins' => array(
                array(
                    'table' => 'order_detail',
                    'conditions' => array(
                        'Order.order_id = order_detail.order_id',
                        'order_detail.item_id' => $item_id
                    )
                )
            )
        ));

        return empty($quantity) ? 0 : $quantity[0]['quantity'];
    }

    /**
     * Returns a query that can be used to fetch a page of reviews for a specific user.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param $user_id
     * @param int $limit
     * @return array
     */
    public function getReviewPageQuery($user_id, $limit = 3){

        return array(
            'Rating' => array(
                'fields'  => array(
                    'Rating.user_id', 'Rating.item_id', 'rating', 'review.review_id', 'review.created', 'review.modified', 'review.content', 'SUM(quantity) as quantity'
                ),
                'conditions' => array(
                    'Rating.user_id' => $user_id
                ),
                'joins' => array(
                    array(
                        'table' => 'review',
                        'conditions' => array(
                            'Rating.rating_id = review.rating_id'
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
                'group' => 'order_detail.item_id',
                'limit' => $limit
            )
        );
    }

    /**
     * Returns the inventory of a specific user. If the user has any items that are below 0 quantity, those will not be
     * included, but that should not happen anyway.
     *
     * @param int $user_id
     * @return array of quantities indexed by item_id
     */
    public function getItems($user_id) {

        return $this->UserItem->find('list', array(
            'fields' => array('item_id', 'quantity'),
            'conditions' => array(
                'user_id' => $user_id,
                'quantity > 0'
            )
        ));
    }

    /**
     * Returns the current and past items for the specified user. The result array consists of two keys, 'current' for
     * a list of current items, and 'past' for a list of past items. Each child array is a list of item quantities
     * indexed by item_id.
     *
     * @param int $user_id
     * @return array
     */
    public function getCurrentAndPastItems($user_id) {

        $currentItems = $this->getItems($user_id);

        $totals = Hash::combine($this->Order->find('all', array(
            'conditions' => array(
                'user_id' => $user_id
            ),
            'fields' => array(
                'order_detail.item_id', 'SUM(quantity) as quantity'
            ),
            'joins' => array(
                array(
                    'table' => 'order_detail',
                    'conditions' => array(
                        'Order.order_id = order_detail.order_id'
                    )
                )
            ),
            'group' => array(
                'order_detail.item_id'
            )
        )), '{n}.order_detail.item_id', '{n}.{n}.quantity' );

        $pastItems = array();

        foreach ($totals as $item_id => $quantity) {
            if (isset($currentItems[$item_id])) {
                $diff = $totals[$item_id] - $currentItems[$item_id];

                if ($diff > 0) {
                    $pastItems[$item_id] = $diff;
                }
            } else {
                $pastItems[$item_id] = $totals[$item_id];
            }
        }

        return array(
            'current' => $currentItems,
            'past' => $pastItems
        );
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
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
        'ingame' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'last_activity' => array(
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
