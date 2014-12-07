<?php
App::uses('AppModel', 'Model');
/**
 * Review Model
 *
 * @property Activity $Activity
 * @property Rating $Rating
 * @property User $User
 */
class Review extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'review';
    public $primaryKey = 'review_id';

    public $belongsTo = array('Activity', 'Rating', 'User');

    public $order = 'Review.review_id DESC';


    /**
     * Returns a review specified by $review_id with the associated rating.
     *
     * @param int $review_id
     * @return array
     */
    public function getWithRating($review_id) {

        return $this->find('first', array(
            'conditions' => array(
                'review_id' => $review_id,
            ),
            'contain' => 'Rating'
        ));
    }

    /**
     * Returns the item associated with a given review. The association will look at the review's rating to get to the
     * item.
     *
     * @param int $review_id
     * @return array the basic item info, including id, name and short_name
     */
    public function getItemByReviewId($review_id) {

        $item = $this->find('first', array(
            'fields' => array(
                'item.item_id', 'item.name', 'item.short_name'
            ),
            'conditions' => array(
                'review_id' => $review_id
            ),
            'joins' => array(
                array(
                    'table' => 'rating',
                    'conditions' => array(
                        'Review.rating_id = rating.rating_id'
                    )
                ),
                array(
                    'table' => 'item',
                    'conditions' => array(
                        'rating.item_id = item.item_id'
                    )
                )
            )
        ));

        return empty($item) ? array() : $item['item'];
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'rating_id' => array(
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
