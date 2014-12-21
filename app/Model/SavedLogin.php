<?php
App::uses('AppModel', 'Model');
/**
 * SavedLogin Model
 *
 * @property User $User
 */
class SavedLogin extends AppModel {

    public $useTable = 'saved_login';
    public $primaryKey = 'saved_login_id';

    public $belongsTo = 'User';


    /**
     * Finds a redeemable saved login given the id, code and ip of the user. All must match for it to find a record.
     *
     * @param int $id the id of the saved login record
     * @param int $code the randomly generated code saved
     * @param string $ip
     * @return array login info
     */
    public function findActive($id, $code, $ip) {

        return $this->find('first', array(
            'conditions' => array(
                'saved_login_id' => $id,
                'code' => $code,
                'remoteip' => $ip,
                'expires >' => time()
            )
        ));
    }

    /**
     * Updates provided record by setting the expire time as if you logged in right now (e.g., a month from now if the
     * Store.SavedLoginDuration is set to a month in seconds).
     *
     * @param array $loginInfo the login record to save, obtained from findRedeemable()
     */
    public function updateRecord($loginInfo) {

        $loginInfo['SavedLogin']['expires'] = time() + Configure::read('Store.SavedLoginDuration');
        $this->save($loginInfo, false);
    }

    /**
     * Deletes all expired records in the saved_login table.
     */
    public function deleteAllExpired() {

        $this->deleteAll(array(
            'expires <= ' => time()
        ), false);
    }

    /**
     * Deletes all saved login records for the specified user.
     *
     * @param int $user_id the user for which to delete all saved login records
     */
    public function deleteForUser($user_id) {

        $this->deleteAll(array(
            'SavedLogin.user_id' => $user_id
        ), false);
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
        'code' => array(
            'numeric' => array(
                'rule' => array('numeric'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'expires' => array(
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
