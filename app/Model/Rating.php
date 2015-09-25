<?php
App::uses('AppModel', 'Model');
/**
 * Rating Model
 *
 * @property Item $Item
 * @property User $User
 *
 * Magic Methods (for inspection):
 * @method findByItemIdAndUserId($item_id, $user_id)
 */
class Rating extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'rating';
    public $primaryKey = 'rating_id';

    public $hasOne = 'Review';

    public $belongsTo = array(
        'Item', 'User'
    );

    public $order = 'Rating.rating desc';

    /**
     * Gets a user's Rating and associated Review for an item.
     *
     * @param int $item_id
     * @param int $user_id
     *
     * @return array the rating/review info merged into a single hash
     */
    public function getByItemAndUser($item_id, $user_id) {

        $rating = $this->find('first', array(
            'fields' => array(
                'rating_id', 'user_id', 'rating'
            ),
            'conditions' => array(
                'item_id' => $item_id,
                'user_id' => $user_id
            ),
            'contain' => array(
                'Review' => array(
                    'fields' => array(
                        'review_id', 'created', 'modified', 'content'
                    )
                )
            )
        ));

        return empty($rating) ? array() : array_merge($rating['Rating'], $rating['Review']);
    }

    /**
     * Saves a user's rating for an item.
     *
     * @param int $item_id
     * @param int $user_id
     * @param int $rating
     *
     * @return boolean whether the save was successful
     */
    public function rateItem($item_id, $user_id, $rating) {

        // get current rating if one exists
        $oldRating = $this->findByItemIdAndUserId($item_id, $user_id);

        // coerce to range 1-10 (0.5 to 5)
        $rating = min(max($rating, 1), 10);

        if (empty($oldRating)) {
            return $this->save(array(
                'item_id' => $item_id,
                'user_id' => $user_id,
                'rating' => $rating
            ));
        } else if ($rating != $oldRating['Rating']['rating']) {
            $oldRating['Rating']['rating'] = $rating;
            return $this->save($oldRating);
        } else {
            // new rating matched old one
            return true;
        }
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'item_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'user_id' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'rating' => array(
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
