<?php
App::uses('Component', 'Controller');

/**
 * Class AccessComponent
 *
 * This class is used for making queries about a user's access level, typically for the current user but other users can
 * also be queried.
 *
 * @property AuthComponent $Auth
 * @property AclComponent $Acl
 */
class AccessComponent extends Component {
    public $components = array('Auth', 'Acl');
    public $cache = array();
    public $user;

    /**
     * Sets up the component.
     *
     * @param Controller $controller
     */
    public function initialize(Controller $controller) {
        $this->setUser();
    }

    /**
     * Sets the current user for the duration of the request. This should be called if the user needs to be logged in
     * after the component initializes.
     *
     * @param array $user optional user to set. if empty, the authentication object will be checked
     */
    public function setUser($user = null) {
        if (empty($user)) {
            $this->user = $this->Auth->user();
        } else {
            $this->user = $user;
        }
    }

    /**
     * Checks whether the current user can access a specific Access Control Object (ACO) and returns true/false.
     *
     * @param string $aco the ACO for which to check access
     * @param string $action (optional) action on the ACO to check (default '*')
     * @return bool whether the user can access the specified ACO
     */
    public function check($aco, $action = '*') {

        // no user means no special access
        if (empty($this->user)) return false;

        // return cached perm if available in this request
        if (isset($this->cache[$aco][$action])) {
            return $this->cache[$aco][$action];
        }

        $user_id = $this->user['user_id'];
        $canAccess = $this->checkUser($user_id, $aco, $action);

        // cache perm for later in this request
        if (empty($this->cache[$aco])) {
            $this->cache[$aco] = array($action => $canAccess);
        } else {
            $this->cache[$aco][$action] = $canAccess;
        }

        return $canAccess;
    }

    /**
     * Finds the specified user in the ARO table and returns their id. This is usually used to see if the player is a
     * member since that is the lowest level of access in the tree. Non-members should not be in the ARO table at all.
     *
     * @param int $user_id the user for which to search
     * @return array the found user or an empty array
     */
    public function findUser($user_id) {
        return $this->Acl->Aro->find('first', array(
            'fields' => 'id',
            'conditions' => array(
                'foreign_key' => $user_id
            ),
            'recursive' => -1
        ));
    }

    /**
     * Checks if the specified user is a member and returns true/false.
     *
     * @param int $user_id the user to check
     * @return bool whether the user is a member
     */
    public function checkIsMember($user_id) {

        // all members and up have records in the aro table
        $aro = $this->findUser($user_id);
        return !empty($aro);
    }

    /**
     * Returns member info for a list of user ids. The result is a list of divisions indexed by user_id, but the
     * presence of any particular users in the results also speaks to whether the user is a member or not.
     *
     * @param array $ids list of users for which to get member info
     * @return array list of divisions indexed by user_id
     */
    public function getMemberInfo($ids) {

        return Hash::combine($this->Acl->Aro->find('all', array(
            'fields' => array(
                'foreign_key', 'division'
            ),
            'conditions' => array(
                'foreign_key' => $ids
            ),
            'recursive' => -1
        )), '{n}.Aro.foreign_key', '{n}.Aro');
    }

    /**
     * Checks the access level for a specific user to a specific Access Control Object (ACO) and optional action.
     *
     * @param int $user_id the user for which to check access
     * @param string $aco the ACO for which to check access
     * @param string $action (optional) action on the ACO to check (default '*')
     * @return bool whether the user can access the specified ACO
     */
    public function checkUser($user_id, $aco, $action = '*') {

        return $this->checkIsMember($user_id) ? $this->Acl->check(array('model' => 'User', 'foreign_key' => $user_id), $aco, $action) : false;
    }
}