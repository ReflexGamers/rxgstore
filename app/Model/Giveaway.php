<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');

/**
 * Giveaway Model
 *
 * @property GiveawayDetail $GiveawayDetail
 * @property GiveawayClaim $GiveawayClaim
 */
class Giveaway extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'giveaway';
    public $primaryKey = 'giveaway_id';

    public $hasMany = array(
        'GiveawayDetail', 'GiveawayClaim'
    );

    public $order = 'Giveaway.giveaway_id DESC';


    /**
     * Returns a list of giveaways and adds a status field for whether each one is active, upcoming
     * or expired.
     *
     * @return array
     */
    public function getList() {

        $data = Hash::extract($this->find('all'), '{n}.Giveaway');

        foreach ($data as &$giveaway) {
            if (CakeTime::isFuture($giveaway['start_date'])) {
                $giveaway['status'] = 1;
            } else if (CakeTime::isPast($giveaway['end_date'])) {
                $giveaway['status'] = -1;
            } else {
                $giveaway['status'] = 0;
            }
        }

        return $data;
    }

    /**
     * Returns a giveaway with its corresponding details.
     *
     * @param int $giveaway_id
     * @return array
     */
    public function getWithDetails($giveaway_id) {

        $data = $this->find('first', array(
            'conditions' => array(
                'giveaway_id' => $giveaway_id
            ),
            'contain' => 'GiveawayDetail'
        ));

        // index details by item_id
        $data['GiveawayDetail'] = Hash::combine($data['GiveawayDetail'], '{n}.item_id', '{n}');

        return $data;
    }

    /**
     * Saves a giveaway with its corresponding details and returns the result of the save call.
     *
     * @param array $data the giveaway data
     * @return bool whether the save was successful
     */
    public function saveWithDetails($data) {

        // set end date active through the end of the day
        $data['Giveaway']['end_date'] = array_merge($data['Giveaway']['end_date'], array(
            'hour' => 23,
            'min' => 59,
            'sec' => 59
        ));

        $isUpdate = !empty($data['Giveaway']['giveaway_id']);
        $giveaway_id = ($isUpdate) ? $data['Giveaway']['giveaway_id'] : null;

        // remove items with 0 or unset quantity
        foreach ($data['GiveawayDetail'] as $i => $detail) {
            if (empty($detail['quantity'])) {
                unset($data['GiveawayDetail'][$i]);
            }
        }

        if ($isUpdate) {

            $previousDetails = Hash::combine($this->GiveawayDetail->find('all', array(
                'fields' => array(
                    'giveaway_detail_id', 'item_id'
                ),
                'conditions' => array(
                    'giveaway_id' => $giveaway_id
                )
            )), '{n}.GiveawayDetail.item_id', '{n}.GiveawayDetail');

            /*
             * Check for details in the request without primary keys that were previously saved, and
             * use the existing primary key from before to avoid a database constraint error.
             */
            foreach ($data['GiveawayDetail'] as $i => &$detail) {
                if (empty($detail['giveaway_detail_id'])) {
                    $previousDetailId = Hash::get($previousDetails, $detail['item_id'] . '.giveaway_detail_id', null);
                    if (!empty($previousDetailId)) {
                        $detail['giveaway_detail_id'] = $previousDetailId;
                    }
                }
            }
        }

        $saveResult = $this->saveAssociated($data, array('atomic' => false));
        $saveSuccessful = $this->wasSaveSuccessful($saveResult);

        if ($isUpdate && $saveSuccessful) {

            // delete items that were removed
            $savedItemIds = Hash::extract($data, 'GiveawayDetail.{n}.item_id');
            $this->GiveawayDetail->deleteAll(array(
                'GiveawayDetail.giveaway_id' => $giveaway_id,
                'NOT' => array(
                    'GiveawayDetail.item_id' => $savedItemIds
                )
            ));
        }

        return $saveSuccessful;
    }

    /**
     * Deletes a giveaway with its corresponding details. This will not delete records of players
     * claiming the giveaway. If a giveaway is accidentally deleted, a new one can be created and
     * the id can be changed to make it look like the old one.
     *
     * @param int $giveaway_id
     */
    public function deleteWithDetails($giveaway_id) {

        $this->GiveawayDetail->deleteAll(array(
            'giveaway_id' => $giveaway_id
        ));

        $this->delete($giveaway_id, false);
    }

    /**
     * Returns an array of all giveaways that the specified user is eligible to claim.
     *
     * @param int $user_id
     * @param bool $isMember whether the user is a member
     * @return array
     */
    public function getEligibleForUser($user_id, $isMember = false) {

        $options = $this->getRemainingItemsBaseQuery($user_id, $isMember);

        $options['fields'] = array(
            'Giveaway.giveaway_id', 'Giveaway.name', 'Giveaway.is_member_only', 'GiveawayDetail.item_id',
            'GiveawayDetail.quantity - COALESCE(GiveawayClaimDetail.quantity, 0) as quantity'
        );

        $results = $this->find('all', $options);

        // make base array of giveaways
        $giveaways = Hash::combine($results, '{n}.Giveaway.giveaway_id', '{n}');

        foreach ($results as $row) {
            $giveaway = $row['Giveaway'];
            $giveaway_id = $giveaway['giveaway_id'];
            $giveaways[$giveaway_id] = array(
                'Giveaway' => $giveaway
            );
        }

        // organize detail records under each giveaway
        foreach ($results as $row) {
            $giveaway_id = Hash::get($row, 'Giveaway.giveaway_id');
            $item_id = Hash::get($row, 'GiveawayDetail.item_id');
            $giveaway = &$giveaways[$giveaway_id];

            if (empty($giveaway['GiveawayDetail'])) {
                $giveaway['GiveawayDetail'] = array();
            }

            $giveaway['GiveawayDetail'][$item_id] = Hash::get($row, '0.quantity');
        }

        return $giveaways;
    }

    /**
     * Gets a list of remaining items in a giveaway that the user has yet to claim. If the user has
     * not claimed the giveaway at all, this simply returns the whole list of items.
     *
     * @param int $giveaway_id
     * @param int $user_id
     * @param bool $isMember whether the user is a member
     * @return array of unclaimed items
     */
    public function getRemainingItems($giveaway_id, $user_id, $isMember = false) {

        $options = $this->getRemainingItemsBaseQuery($user_id, $isMember);

        $options['conditions']['Giveaway.giveaway_id'] = $giveaway_id;
        $options['fields'] = array(
            'GiveawayDetail.item_id', 'GiveawayDetail.quantity - COALESCE(GiveawayClaimDetail.quantity, 0) as quantity'
        );

        return Hash::combine($this->find('all', $options), '{n}.GiveawayDetail.item_id', '{n}.0.quantity');
    }

    /**
     * Returns base query for getting the remaining items of one or multiple giveaways for a user.
     *
     * @param int $user_id
     * @param bool $isMember whether the user is a member
     * @return array
     */
    private function getRemainingItemsBaseQuery($user_id, $isMember = false) {

        $options = array(
            'conditions' => array(
                array(
                    'OR' => array(
                        'GiveawayClaimDetail.quantity IS NULL',
                        'GiveawayDetail.quantity - GiveawayClaimDetail.quantity > 0'
                    )
                ),
                array(
                    'OR' => array(
                        'Giveaway.start_date IS NULL',
                        'Giveaway.start_date <= CURRENT_TIMESTAMP'
                    )
                ),
                array(
                    'OR' => array(
                        'Giveaway.end_date IS NULL',
                        'Giveaway.end_date >= CURRENT_TIMESTAMP'
                    )
                )
            ),
            'joins' => array(
                array(
                    'table' => 'giveaway_detail',
                    'alias' => 'GiveawayDetail',
                    'conditions' => array(
                        'GiveawayDetail.giveaway_id = Giveaway.giveaway_id'
                    )
                ),
                array(
                    'type' => 'left',
                    'table' => 'giveaway_claim',
                    'alias' => 'GiveawayClaim',
                    'conditions' => array(
                        'GiveawayClaim.giveaway_id = Giveaway.giveaway_id',
                        'GiveawayClaim.user_id' => $user_id
                    )
                ),
                array(
                    'type' => 'left',
                    'table' => 'giveaway_claim_detail',
                    'alias' => 'GiveawayClaimDetail',
                    'conditions' => array(
                        'GiveawayClaimDetail.giveaway_claim_id = GiveawayClaim.giveaway_claim_id',
                        'GiveawayClaimDetail.item_id = GiveawayDetail.item_id'
                    )
                )
            )
        );

        // if not a member, lookup non-member giveaways only
        if (!$isMember) {
            $options['conditions']['Giveaway.is_member_only'] = false;
        }

        return $options;
    }

    /**
     * Claims a specific giveaway for a specific user.
     *
     * @param int $giveaway_id
     * @param int $user_id
     * @param bool $isMember whether the user is a member
     * @return mixed false if the giveaway was already claimed, or an array of claimed items
     */
    public function claim($giveaway_id, $user_id, $isMember = false) {

        $remainingItems = $this->getRemainingItems($giveaway_id, $user_id, $isMember);

        // check if already fully claimed
        if (empty($remainingItems)) {
            return false;
        }

        // add the remaining items
        $this->GiveawayClaim->User->addItems($user_id, $remainingItems);

        // register that the claim happened
        $this->GiveawayClaim->saveClaim($giveaway_id, $user_id);

        return $remainingItems;
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
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
    );
}
