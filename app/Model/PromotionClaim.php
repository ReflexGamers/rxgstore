<?php
App::uses('AppModel', 'Model');
/**
 * PromotionClaim Model
 *
 * @property Activity $Activity
 * @property Promotion $Promotion
 * @property PromotionClaimDetail $PromotionClaimDetail
 * @property User $User
 */
class PromotionClaim extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'promotion_claim';
    public $primaryKey = 'promotion_claim_id';

    public $belongsTo = array(
        'Activity', 'Promotion', 'User'
    );

    public $hasMany = 'PromotionClaimDetail';

    public $order = 'PromotionClaim.promotion_claim_id DESC';


    /**
     * Creates or updates a promotion claim for a user with the full quantity of each item in the
     * promotion detail, effectively rendering the claim as fully completed.
     *
     * @param int $promotion_id
     * @param int $user_id
     */
    public function complete($promotion_id, $user_id) {

        $claim_id = $this->field('promotion_claim_id', array(
            'promotion_id' => $promotion_id,
            'user_id' => $user_id
        ));

        // get details of promotion
        $details = Hash::combine($this->Promotion->PromotionDetail->find('all', array(
            'fields' => array(
                'item_id', 'quantity'
            ),
            'conditions' => array(
                'promotion_id' => $promotion_id
            )
        )), '{n}.PromotionDetail.item_id', '{n}.PromotionDetail');

        // only add claim record if it does not exist already
        if (empty($claim_id)) {

            // create new claim
            $this->saveAssociated(array(
                'PromotionClaim' => array(
                    'promotion_claim_id' => $this->Activity->getNewId('PromotionClaim'),
                    'promotion_id' => $promotion_id,
                    'user_id' => $user_id
                ),
                'PromotionClaimDetail' => $details
            ), array('atomic' => false));

        } else {

            // get existing claim details
            $claimedDetails = Hash::combine($this->PromotionClaimDetail->find('all', array(
                'fields' => array(
                    'promotion_claim_detail_id', 'item_id', 'quantity'
                ),
                'conditions' => array(
                    'promotion_claim_id' => $claim_id
                )
            )), '{n}.PromotionClaimDetail.item_id', '{n}.PromotionClaimDetail');

            // overwrite claimed values with ones from promotion detail definition
            $this->PromotionClaimDetail->save(array_merge($claimedDetails, $details));
        }
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'promotion_id' => array(
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
