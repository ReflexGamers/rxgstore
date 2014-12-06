<?php
App::uses('AppModel', 'Model');
/**
 * Gift Model
 *
 * @property Activity $Activity
 * @property GiftDetail $GiftDetail
 * @property User $Sender
 * @property User $Recipient
 */
class Gift extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'gift';
    public $primaryKey = 'gift_id';

    public $hasMany = 'GiftDetail';

    public $belongsTo = array(
        'Activity',
        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id'
        ),
        'Recipient' => array(
            'className' => 'User',
            'foreignKey' => 'recipient_id',
        )
    );

    public $order = 'Gift.gift_id DESC';


    /**
     * Accepts a pending gift specified by $gift_id for the provided $recipient_id if one exists. This will add the
     * items from the gift details to the user's inventory. If the $gift_id does not match the $user_id or if it was
     * already accepted (or not found), this will return false instead of the gift data.
     *
     * @param int $gift_id the id of the gift to accept
     * @param int $recipient_id the user_id of the recipient for double-checking if that person was accepting it
     * @return array|false the gift that was accepted, or false if no pending gift was found with the specified ids
     */
    public function acceptPendingGift($gift_id, $recipient_id) {

        $UserItem = $this->Recipient->UserItem;

        $gift = $this->find('first', array(
            'conditions' => array(
                'gift_id' => $gift_id,
                'recipient_id' => $recipient_id,
                'accepted = 0'
            ),
            'contain' => 'GiftDetail'
        ));

        // make sure valid gift exists
        if (!empty($gift)) {

            // lock user inventory
            $UserItem->query('LOCK TABLES user_item WRITE, user_item as UserItem WRITE');

            // get user inventory
            $inventory = Hash::combine(
                $UserItem->findAllByUserId($recipient_id),
                '{n}.UserItem.item_id', '{n}.UserItem'
            );

            // add gift details to user inventory
            foreach ($gift['GiftDetail'] as $detail) {

                $item_id = $detail['item_id'];
                $quantity = $detail['quantity'];

                if (empty($inventory[$item_id])) {
                    $inventory[$item_id] = array(
                        'user_id' => $recipient_id,
                        'item_id' => $item_id,
                        'quantity' => $quantity
                    );
                } else {
                    $inventory[$item_id]['quantity'] += $quantity;
                }
            }

            // save and unlock inventory
            $UserItem->saveMany($inventory, array('atomic' => false));
            $UserItem->query('UNLOCK TABLES');

            // set gift as accepted
            $this->id = $gift_id;
            $this->saveField('accepted', 1);

            // return gift data so controller can use it
            return $gift;

        } else {

            // no pending gift found that matched $gift_id and $recipient_id
            return false;
        }
    }

    /**
     * Returns a query that can be used to fetch a page of gift activity.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param int $limit optional limit for number of gifts to return
     * @return array
     */
    public function getActivityQuery($limit = 5) {

        return array(
            'Gift' => array(
                'contain' => 'GiftDetail',
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
        'sender_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'recipient_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'accepted' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'anonymous' => array(
            'boolean' => array(
                'rule' => array('boolean'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
