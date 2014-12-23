<?php
App::uses('AppModel', 'Model');
/**
 * ShoutboxMessage Model
 *
 * @property User $User
 */
class ShoutboxMessage extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'shoutbox_message';
    public $primaryKey = 'shoutbox_message_id';

    public $belongsTo = 'User';

    public $order = 'ShoutboxMessage.date desc';


    /**
     * Returns a list of the most recent Shoutbox Messages.
     *
     * @return array of messages
     */
    public function getRecent() {
        return Hash::extract($this->find('all', array(
            'limit' => 10,
            'conditions' => array(
                'removed = 0'
            )
        )), '{n}.ShoutboxMessage');
    }

    /**
     * Returns a boolean representing whether the user is allowed to post based on when they last posted and the config
     * value set by Store.Shoutbox.PostCooldown.
     *
     * @param int $user_id the user to check
     * @return bool whether the user can post or not
     */
    public function canUserPost($user_id) {

        $message = $this->find('first', array(
            'conditions' => array(
                'user_id' => $user_id
            )
        ));

        $interval = Configure::read('Store.Shoutbox.PostCooldown');

        return empty($message) || strtotime($message['ShoutboxMessage']['date']) + $interval <= time();
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
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
    );
}
