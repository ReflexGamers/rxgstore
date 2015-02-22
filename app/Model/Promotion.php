<?php
App::uses('AppModel', 'Model');
/**
 * Promotion Model
 *
 * @property PromotionDetail $PromotionDetail
 * @property PromotionClaim $PromotionClaim
 */
class Promotion extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion';
    public $primaryKey = 'promotion_id';

    public $hasMany = array(
        'PromotionDetail', 'PromotionClaim'
    );

    public $order = 'Promotion.promotion_id DESC';

    /**
     * Returns a promotion with its corresponding details.
     *
     * @param int $promotion_id
     * @return array
     */
    public function getWithDetails($promotion_id) {

        $data = $this->find('first', array(
            'conditions' => array(
                'promotion_id' => $promotion_id
            ),
            'contain' => 'PromotionDetail'
        ));

        // index details by item_id
        $data['PromotionDetail'] = Hash::combine($data['PromotionDetail'], '{n}.item_id', '{n}');

        return $data;
    }

    /**
     * Saves a promotion with its corresponding details and returns the result of the save call.
     *
     * @param array $data the promotion data
     * @return bool whether the save was successful
     */
    public function saveWithDetails($data) {

        $isUpdate = !empty($data['Promotion']['promotion_id']);
        $promotion_id = ($isUpdate) ? $data['Promotion']['promotion_id'] : null;

        // remove items with 0 or unset quantity
        foreach ($data['PromotionDetail'] as $i => $detail) {
            if (empty($detail['quantity'])) {
                unset($data['PromotionDetail'][$i]);
            }
        }

        if ($isUpdate) {

            $previousDetails = Hash::combine($this->PromotionDetail->find('all', array(
                'fields' => array(
                    'promotion_detail_id', 'item_id'
                ),
                'conditions' => array(
                    'promotion_id' => $promotion_id
                )
            )), '{n}.PromotionDetail.item_id', '{n}.PromotionDetail');

            /*
             * Check for details in the request without primary keys that were previously saved, and
             * use the existing primary key from before to avoid a database constraint error.
             */
            foreach ($data['PromotionDetail'] as $i => &$detail) {
                if (empty($detail['promotion_detail_id'])) {
                    $previousDetailId = Hash::get($previousDetails, $detail['item_id'] . '.promotion_detail_id', null);
                    if (!empty($previousDetailId)) {
                        $detail['promotion_detail_id'] = $previousDetailId;
                    }
                }
            }
        }

        $saveResult = $this->saveAssociated($data, array('atomic' => false));
        $saveSuccessful = $this->wasSaveSuccessful($saveResult);

        if ($isUpdate && $saveSuccessful) {

            // delete items that were removed
            $savedItemIds = Hash::extract($data, 'PromotionDetail.{n}.item_id');
            $this->PromotionDetail->deleteAll(array(
                'PromotionDetail.promotion_id' => $promotion_id,
                'NOT' => array(
                    'PromotionDetail.item_id' => $savedItemIds
                )
            ));
        }

        return $saveSuccessful;
    }

    /**
     * Deletes a promotion with its corresponding details. This will not delete records of players
     * claiming the promotion. If a promotion is accidentally deleted, a new one can be created and
     * the id can be changed to make it look like the old one.
     *
     * @param int $promotion_id
     */
    public function deleteWithDetails($promotion_id) {

        $this->PromotionDetail->deleteAll(array(
            'promotion_id' => $promotion_id
        ));

        $this->delete($promotion_id, false);
    }

    /**
     * Gets a list of remaining items in a promotion that the user has yet to claim. If the user has
     * not claimed the promotion at all, this simply returns the whole list of items.
     *
     * @param int $promotion_id
     * @param int $user_id
     * @param bool $isMember whether the user is a member
     * @return array of unclaimed items
     */
    public function getRemainingItems($promotion_id, $user_id, $isMember = false) {

        $options = array(
            'fields' => array(
                'PromotionDetail.item_id', 'PromotionDetail.quantity - COALESCE(PromotionClaimDetail.quantity, 0) as quantity'
            ),
            'conditions' => array(
                array(
                    'OR' => array(
                        'PromotionClaimDetail.quantity IS NULL',
                        'PromotionDetail.quantity - PromotionClaimDetail.quantity > 0'
                    )
                ),
                array(
                    'OR' => array(
                        'Promotion.start_date IS NULL',
                        'Promotion.start_date <= CURRENT_TIMESTAMP'
                    )
                ),
                array(
                    'OR' => array(
                        'Promotion.end_date IS NULL',
                        'Promotion.end_date >= CURRENT_TIMESTAMP'
                    )
                )
            ),
            'joins' => array(
                array(
                    'table' => 'promotion_detail',
                    'alias' => 'PromotionDetail',
                    'conditions' => array(
                        'PromotionDetail.promotion_id' => $promotion_id
                    )
                ),
                array(
                    'type' => 'left',
                    'table' => 'promotion_claim',
                    'alias' => 'PromotionClaim',
                    'conditions' => array(
                        'PromotionClaim.promotion_id' => $promotion_id,
                        'PromotionClaim.user_id' => $user_id
                    )
                ),
                array(
                    'type' => 'left',
                    'table' => 'promotion_claim_detail',
                    'alias' => 'PromotionClaimDetail',
                    'conditions' => array(
                        'PromotionClaimDetail.promotion_claim_id = PromotionClaim.promotion_claim_id',
                        'PromotionClaimDetail.item_id = PromotionDetail.item_id'
                    )
                )
            )
        );

        // if not a member, lookup non-member promotions only
        if (!$isMember) {
            $options['conditions']['is_member_only'] = false;
        }

        return Hash::combine($this->find('all', $options), '{n}.PromotionDetail.item_id', '{n}.0.quantity');
    }

    /**
     * Claims a specific promotion for a specific user.
     *
     * @param int $promotion_id
     * @param int $user_id
     * @return bool whether the claim was still valid (false if already fully claimed)
     */
    public function claim($promotion_id, $user_id) {

        $remainingItems = $this->getRemainingItems($promotion_id, $user_id);

        // check if already fully claimed
        if (empty($remainingItems)) {
            return false;
        }

        // add the remaining items
        $this->PromotionClaim->User->addItems($user_id, $remainingItems);

        // register that the claim happened
        $this->PromotionClaim->complete($promotion_id, $user_id);

        return true;
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
