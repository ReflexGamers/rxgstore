<?php
App::uses('Component', 'Controller');

/**
 * Class PermissionsComponent
 *
 * This class handles the initialization and synchronization of all permissions. It does not handle checking user
 * permissions; for that, refer to the AccessComponent.
 *
 * @property AclComponent $Acl
 * @property AccountUtilityComponent $AccountUtility
 */
class PermissionsComponent extends Component {
    public $components = array('Acl', 'AccountUtility');

    public function initialize(Controller $controller) {

    }

    /**
     * Initializes permissions. Empties the acos, aros and acos_aros tables before running.
     */
    public function initAll() {

        $this->createACOs();
        $this->createAROs();
        $this->createAssociations();
    }

    /**
     * Dumps all permissions by emptying the tables for ACOs, AROs and the relationship between the two.
     */
    public function dumpAll() {

        $this->Acl->Aco->query('TRUNCATE aros; TRUNCATE acos; TRUNCATE aros_acos');
    }

    /**
     * Creates all Access Control Objects (ACOs) - objects that are requested which need access control.
     */
    private function createACOs() {

        $Aco = $this->Acl->Aco;

        $objects = array(
            array('alias' => 'Cache'),
            array('alias' => 'Chats'),
            array('alias' => 'Debug'),
            array('alias' => 'Items'),
            array('alias' => 'Logs'),
            array('alias' => 'Permissions'),
            array('alias' => 'QuickAuth'),
            array('alias' => 'Receipts'),
            array('alias' => 'Reviews'),
            array('alias' => 'Rewards'),
            array('alias' => 'Stock'),
            array('alias' => 'Users')
        );

        foreach ($objects as $object) {
            $Aco->create();
            $Aco->save($object);
        }
    }

    /**
     * Creates all Access Request Objects (AROs) - objects that request ACOs (i.e., users).
     */
    private function createAROs() {

        $Aro = $this->Acl->Aro;

        $groups = array(
            array(
                'alias' => 'Member',
            ),
            array(
                'alias' => 'Basic Admin',
                'parent_id' => 1
            ),
            array(
                'alias' => 'Full Admin',
                'parent_id' => 2
            ),
            array(
                'alias' => 'Advisor',
                'parent_id' => 3
            ),
            array(
                'alias' => 'Captain',
                'parent_id' => 4
            ),
            array(
                'alias' => 'Cabinet',
                'parent_id' => 5
            ),
            array(
                'alias' => 'Director',
                'parent_id' => 6
            ),
        );

        foreach ($groups as $group) {
            $Aro->create();
            $Aro->save(array_merge($group, array('model' => null)));
        }
    }

    /**
     * Assigns permissions to users and user groups by associating ACOs and AROs.
     */
    private function createAssociations() {

        $this->Acl->allow('Captain', 'Debug');
        $this->Acl->allow('Advisor', 'QuickAuth');
        $this->Acl->allow('Advisor', 'Rewards');
        $this->Acl->allow('Advisor', 'Permissions', 'read');
        $this->Acl->allow('Advisor', 'Permissions', 'update');
        $this->Acl->allow('Advisor', 'Cache', 'read');
        $this->Acl->allow('Advisor', 'Cache', 'update');
        $this->Acl->allow('Advisor', 'Cache', 'delete');
        $this->Acl->allow('Advisor', 'Chats', 'delete');
        $this->Acl->allow('Advisor', 'Items', 'create');
        $this->Acl->allow('Advisor', 'Items', 'update');
        $this->Acl->allow('Advisor', 'Logs', 'read');
        $this->Acl->allow('Advisor', 'Reviews', 'update');
        $this->Acl->allow('Advisor', 'Reviews', 'delete');
        $this->Acl->allow('Advisor', 'Receipts', 'read');

        $this->Acl->allow('Advisor', 'Stock', 'update');
        $this->Acl->allow('Advisor', 'Users', 'update');
    }

    /**
     * Returns all ARO group names in a list indexed by group id.
     *
     * @return array list of group names indexed by group id
     */
    private function getAroGroupNames() {

        return $this->Acl->Aro->find('list', array(
            'fields' => array(
                'id', 'alias'
            ),
            'conditions' => array(
                'foreign_key is null'
            ),
            'recursive' => -1
        ));
    }

    /**
     * Returns a list of all current members (including admins) in an array indexed by user_id.
     *
     * @return array list of members (including admins) indexed by user_id
     */
    private function getSavedMembers() {

         return Hash::combine($this->Acl->Aro->find('all', array(
            'fields' => array(
                'id', 'foreign_key', 'parent_id', 'alias', 'division'
            ),
            'conditions' => array(
                'foreign_key is not null'
            ),
            'recursive' => -1
        )), '{n}.Aro.foreign_key', '{n}.Aro');
    }

    /**
     * Synchronizes the permission tables with Sourcebans and the forums.
     *
     * @return array result of sync with keys 'added', 'updated' and 'removed' as sub-arrays with the changed admin data
     */
    public function syncAll() {

        $Aro = $this->Acl->Aro;

        // group names indexed by id
        $groupNames = $this->getAroGroupNames();

        // group ids indexed by name
        $groupIds = array_flip($groupNames);

        $memberGroupId = $groupIds['Member'];

        // currently saved admins indexed by user_id
        $savedMembers = $this->getSavedMembers();

        // get sourcebans data
        $db = ConnectionManager::getDataSource('sourcebans');
        $result = $db->rawQuery("SELECT authid, user, srv_group FROM rxg__admins where srv_group is not null");

        // sourcebans admins indexed by user_id
        $sbAdmins = array();

        while ($row = $result->fetch()) {

            $user_id = $this->AccountUtility->AccountIDFromSteamID32($row['authid']);
            $groupName = $row['srv_group'];

            if (empty($groupName) || empty($groupIds[$groupName])) {
                $groupName = 'Member';
            }

            $sbAdmins[$user_id] = array(
                'alias' => $row['user'],
                'parent_id' => $groupIds[$groupName]
            );
        }


        // get forum data
        $db = ConnectionManager::getDataSource('forums');
        $config = Configure::read('Store.Forums');

        $groups = implode(',', $config['MemberGroups']);
        $divisions = $config['Divisions'];

        $result = $db->rawQuery("SELECT steamid, user.username, steamuser.steamid, userfield.field5 FROM steamuser JOIN user ON steamuser.userid = user.userid JOIN userfield on userfield.userid = user.userid WHERE user.usergroupid IN ($groups)");

        // linked members/admins indexed by user_id
        $forumMembers = array();

        while ($row = $result->fetch()) {
            $user_id = $this->AccountUtility->AccountIDFromSteamID64($row['steamid']);
            $division = empty($divisions[$row['field5']]) ? '' : $divisions[$row['field5']];
            $forumMembers[$user_id] = array(
                'alias' => $row['username'],
                'division' => $division
            );
        }

        $insertAdmins = array_diff_key($sbAdmins, $savedMembers);
        $insertMembers = array_diff_key($forumMembers, $savedMembers, $insertAdmins);

        $results = array(
            'added' => array(),
            'updated' => array(),
            'removed' => array()
        );

        CakeLog::write('permsync', 'Performed Sync.');

        // update/remove existing records
        foreach ($savedMembers as $user_id => $data) {

            $forumData = !empty($forumMembers[$user_id]) ? $forumMembers[$user_id] : '';
            $division = !empty($forumData['division']) ? $forumData['division'] : '';

            if (empty($sbAdmins[$user_id])) {

                // not in sourcebans db
                if (empty($forumData)) {

                    // not a linked member either so remove
                    $steamid = $this->AccountUtility->SteamID64FromAccountID($data['foreign_key']);
                    $division = !empty($data['division']) ? $data['division'] : 'No Division';
                    CakeLog::write('permsync', " - deleted {$groupNames[$data['parent_id']]}: '{$data['alias']}' / $steamid / $division");

                    $Aro->clear();
                    $Aro->delete($data['id']);
                    $results['removed'][] = $data;

                } else if ($data['parent_id'] != $memberGroupId || $data['alias'] != $forumData['alias'] || $data['division'] != $division) {

                    // linked member, not admin, needs updating/demoting
                    $steamid = $this->AccountUtility->SteamID64FromAccountID($data['foreign_key']);
                    CakeLog::write('permsync', " - updated '{$data['alias']}' / $steamid");

                    if ($data['parent_id'] != $memberGroupId) {
                        CakeLog::write('permsync', "   - updated rank: {$groupNames[$data['parent_id']]} -> $groupNames[$memberGroupId]");
                    }

                    if ($data['alias'] != $forumData['alias']) {
                        CakeLog::write('permsync', "   - updated alias: '{$data['alias']}' -> '{$forumData['alias']}'");
                    }

                    if ($data['division'] != $division) {
                        $oldDivision = !empty($data['division']) ? $data['division'] : 'none';
                        CakeLog::write('permsync', "   - updated division: $oldDivision -> $division");
                    }

                    $data['parent_id'] = $memberGroupId;
                    $data['alias'] = $forumData['alias'];
                    $data['division'] = $division;
                    $Aro->clear();
                    $Aro->save($data);
                    $results['updated'][] = $data;
                }

            } else {

                // admin is in sourcebans db
                $adminData = $sbAdmins[$user_id];

                if ($data['parent_id'] != $adminData['parent_id'] || $data['alias'] != $adminData['alias'] || $data['division'] != $division) {

                    // needs updating
                    $steamid = $this->AccountUtility->SteamID64FromAccountID($data['foreign_key']);
                    CakeLog::write('permsync', " - updated '{$data['alias']}' / $steamid");

                    if ($data['parent_id'] != $adminData['parent_id']) {
                        CakeLog::write('permsync', "   - updated rank: {$groupNames[$data['parent_id']]} -> {$groupNames[$adminData['parent_id']]}");
                    }

                    if ($data['alias'] != $adminData['alias']) {
                        CakeLog::write('permsync', "   - updated alias: '{$data['alias']}' -> '{$forumData['alias']}'");
                    }

                    if ($data['division'] != $division) {
                        $oldDivision = !empty($data['division']) ? $data['division'] : 'none';
                        CakeLog::write('permsync', "   - updated division: $oldDivision -> $division");
                    }

                    $data['alias'] = $adminData['alias'];
                    $data['parent_id'] = $adminData['parent_id'];
                    $data['division'] = $division;
                    $Aro->clear();
                    $Aro->save($data);
                    $results['updated'][] = $data;
                }
            }
        }

        // insert new admins
        foreach ($insertAdmins as $user_id => $data) {

            if (!empty($forumMembers[$user_id])) {
                $data['division'] = $forumMembers[$user_id]['division'];
            }

            $division = !empty($data['division']) ? $data['division'] : 'No Division';
            $rank = $groupNames[$data['parent_id']];

            $steamid = $this->AccountUtility->SteamID64FromAccountID($user_id);
            CakeLog::write('permsync', " - added $rank: '{$data['alias']}' / $steamid / $division");

            $data['model'] = 'User';
            $data['foreign_key'] = $user_id;
            $Aro->clear();
            $Aro->save($data);
            $results['added'][] = $data;
        }

        // insert new members
        foreach ($insertMembers as $user_id => $data) {

            $division = !empty($data['division']) ? $data['division'] : 'No Division';

            $steamid = $this->AccountUtility->SteamID64FromAccountID($user_id);
            CakeLog::write('permsync', " - added Member: '{$data['alias']}' / $steamid / $division");

            $data['model'] = 'User';
            $data['foreign_key'] = $user_id;
            $data['parent_id'] = $memberGroupId;
            $Aro->clear();
            $Aro->save($data);
            $results['added'][] = $data;
        }

        return $results;
    }
}