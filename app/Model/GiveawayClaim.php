<?php
App::uses('AppModel', 'Model');
/**
 * GiveawayClaim Model
 *
 * @property Activity $Activity
 * @property Giveaway $Giveaway
 * @property GiveawayClaimDetail $GiveawayClaimDetail
 * @property User $User
 */
class GiveawayClaim extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'giveaway_claim';
    public $primaryKey = 'giveaway_claim_id';

    public $belongsTo = array(
        'Activity', 'Giveaway', 'User'
    );

    public $hasMany = 'GiveawayClaimDetail';

    public $order = 'GiveawayClaim.giveaway_claim_id DESC';


    /**
     * Creates or updates a giveaway claim for a user with the full quantity of each item in the
     * giveaway detail, effectively rendering the claim as fully completed.
     *
     * @param int $giveaway_id
     * @param int $user_id
     */
    public function saveClaim($giveaway_id, $user_id) {

        // look for existing claim for this giveaway
        $claim_id = $this->field('giveaway_claim_id', array(
            'giveaway_id' => $giveaway_id,
            'user_id' => $user_id
        ));

        // get details of giveaway
        $details = Hash::combine($this->Giveaway->GiveawayDetail->find('all', array(
            'fields' => array(
                'item_id', 'quantity'
            ),
            'conditions' => array(
                'giveaway_id' => $giveaway_id
            )
        )), '{n}.GiveawayDetail.item_id', '{n}.GiveawayDetail');

        // create or update claim
        if (empty($claim_id)) {
            $this->createClaim($giveaway_id, $user_id, $details);
        } else {
            $this->updateClaim($claim_id, $details);
        }
    }

    /**
     * Creates a new claim for a given giveaway and user.
     *
     * @param int $giveaway_id
     * @param int $user_id
     * @param array $details master giveaway details indexed by item_id
     * @return array the save result
     */
    protected function createClaim($giveaway_id, $user_id, $details) {

        return $this->saveAssociated(array(
            'GiveawayClaim' => array(
                'giveaway_claim_id' => $this->Activity->getNewId('GiveawayClaim'),
                'giveaway_id' => $giveaway_id,
                'user_id' => $user_id
            ),
            'GiveawayClaimDetail' => $details
        ), array('atomic' => false));
    }

    /**
     * Updates a giveaway that has already been claimed.
     *
     * @param int $claim_id the claim to update
     * @param array $details master giveaway details indexed by item_id
     */
    protected function updateClaim($claim_id, $details) {

        $this->query('LOCK TABLES giveaway_claim_detail WRITE, giveaway_claim_detail as GiveawayClaimDetail WRITE');

        // get past claim details
        $claimDetails = Hash::combine($this->GiveawayClaimDetail->find('all', array(
            'fields' => array(
                'giveaway_claim_detail_id', 'item_id', 'quantity'
            ),
            'conditions' => array(
                'giveaway_claim_id' => $claim_id
            )
        )), '{n}.GiveawayClaimDetail.item_id', '{n}.GiveawayClaimDetail');

        // create new details or update old ones
        foreach ($details as $item_id => $detail) {

            $quantity = $detail['quantity'];

            if (empty($claimDetails[$item_id])) {

                // create new detail
                $claimDetails[$item_id] = array(
                    'giveaway_claim_id' => $claim_id,
                    'item_id' => $item_id,
                    'quantity' => $quantity
                );
            } else {
                $claimDetail = &$claimDetails[$item_id];

                // update old detail or ignore if unchanged/decreased
                if ($quantity > $claimDetail['quantity']) {
                    $claimDetail['quantity'] = $quantity;
                } else {
                    unset($claimDetail[$item_id]);
                }
            }
        }

        // update claim details (insert and/or update)
        $this->GiveawayClaimDetail->saveMany($claimDetails, array('atomic' => false));

        $this->query('UNLOCK TABLES');
    }

    /**
     * Returns a query that can be used to fetch a page of Giveaway Claim activity.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param int $limit optional limit for number of Giveaway Claims to return
     * @return array
     */
    public function getActivityQuery($limit = 5) {

        return array(
            'GiveawayClaim' => array(
                'contain' => array(
                    'GiveawayClaimDetail',
                    'Giveaway'
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
        'giveaway_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'user_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
