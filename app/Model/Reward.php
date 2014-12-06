<?php
App::uses('AppModel', 'Model');
/**
 * Reward Model
 *
 * @property Activity $Activity
 * @property User $Sender
 * @property RewardDetail $RewardDetail
 * @property RewardRecipient $RewardRecipient
 */
class Reward extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'reward';
    public $primaryKey = 'reward_id';

    public $hasMany = array(
        'RewardDetail', 'RewardRecipient'
    );

    public $belongsTo = array(
        'Activity',
        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id'
        )
    );

    public $order = 'Reward.reward_id DESC';


    /**
     * Accepts a pending reward specified by $reward_id for the provided $recipient_id if one exists. This will add the
     * items from the reward details to the user's inventory. If the $reward_id does not match the $user_id or if it was
     * already accepted (or not found), this will return false instead of the reward data.
     *
     * @param int $reward_id
     * @param int $recipient_id
     * @return array|false the reward that was accepted, or false if no pending reward was found with the specified ids
     */
    public function acceptPendingReward($reward_id, $recipient_id) {

        $UserItem = $this->RewardRecipient->Recipient->UserItem;

        $reward = $this->RewardRecipient->find('first', array(
            'conditions' => array(
                'RewardRecipient.reward_id' => $reward_id,
                'recipient_id' => $recipient_id,
                'accepted = 0'
            ),
            'contain' => 'Reward.RewardDetail'
        ));

        // make sure valid reward exists
        if (!empty($reward)) {

            // lock user inventory
            $UserItem->query('LOCK TABLES user_item WRITE, user_item as UserItem WRITE');

            // get user inventory
            $inventory = Hash::combine(
                $UserItem->findAllByUserId($recipient_id),
                '{n}.UserItem.item_id', '{n}.UserItem'
            );

            // add reward details to user inventory
            foreach ($reward['Reward']['RewardDetail'] as $detail) {

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
            $this->RewardRecipient->id = $reward['RewardRecipient']['reward_recipient_id'];
            $this->RewardRecipient->saveField('accepted', 1);

            // return reward data so controller can use it
            return $reward;

        } else {

            // no pending reward found that matched $gift_id and $recipient_id
            return false;
        }
    }

    /**
     * Returns a query that can be used to fetch a page of reward activity.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param int $limit optional limit for number of rewards to return
     * @return array
     */
    public function getActivityQuery($limit = 5){

        return array(
            'Reward' => array(
                'contain' => array(
                    'RewardDetail',
                    'RewardRecipient'
                ),
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
    );
}
