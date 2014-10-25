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

    public function getItemByReviewId($review_id = null) {

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
