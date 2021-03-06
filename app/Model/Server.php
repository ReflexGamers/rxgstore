<?php
App::uses('AppModel', 'Model');
/**
 * Server Model
 *
 * @property ServerItem $ServerItem
 *
 * Magic Methods (for inspection):
 * @method findByServerIp
 * @method findByShortName
 */
class Server extends AppModel {

    public $actsAs = array('Containable');

    public $useTable = 'server';
    public $primaryKey = 'server_id';

    public $hasMany = array(
        'ServerItem', 'UserPreference'
    );

    /**
     * Returns a list of servers or server groups that support the item. If the item is allowed by a server group, that
     * group's children will not be in the list unless it is directly supported by the child servers as well, but that
     * should not currently be possible since the logic should remove the child servers if it sees the parent.
     *
     * @param int $item_id the id of the item for which to find the servers
     * @return array
     */
    public function getAllByItemId($item_id) {

        return Hash::extract($this->find('all', array(
            'joins' => array(
                array(
                    'table' => 'server_item',
                    'conditions' => array(
                        'server_item.server_id = Server.server_id',
                        'server_item.item_id' => $item_id
                    )
                )
            )
        )), '{n}.Server');
    }

    /**
     * Returns a list item ids usable in the server specified by server_ip.
     *
     * @param string $server_ip the server ip for which to find usable items
     * @return array list of item ids usable in the server
     */
    public function getUsableItems($server_ip) {

        return Hash::extract($this->find('all', array(
            'fields' => 'server_item.item_id',
            'conditions' => array(
                'server_ip' => $server_ip
            ),
            'joins' => array(
                array(
                    'table' => 'server_item',
                    'conditions' => array(
                        'OR' => array(
                            'server_item.server_id = Server.server_id',
                            'server_item.server_id = Server.parent_id'
                        )
                    )
                )
            ),
        )), '{n}.server_item.item_id');
    }

    /**
     * TODO: Appears to be unused
     */
    public function getTree() {

        $servers = Hash::extract($this->find('all', array(
            'fields' => array(
                'server_id', 'name', 'short_name', 'parent_id'
            )
        )), '{n}.Server');

        $tree = array();

        foreach ($servers as $server) {

            $parent_id = $server['parent_id'];

            if (empty($parent_id)) {

                $server_id = $server['server_id'];

                if (empty($tree[$server_id])) {
                    $tree[$server_id] = $server;
                } else {
                    $tree[$server_id] = array_merge($tree[$server_id], $server);
                }

            } else {

                if (empty($tree[$parent_id])) {
                    $tree[$parent_id] = array(
                        'children' => array($server)
                    );
                } else if (empty($tree[$parent_id]['children'])) {
                    $tree[$parent_id]['children'] = array($server);
                } else {
                    $tree[$parent_id]['children'][] = $server;
                }
            }
        }

        return $tree;
    }

    /**
     * Returns a list of all servers sorted with each parent preceding its list of children. In the output, you can
     * identify the parents because they will have a null parent_id.
     *
     * @return array of all servers
     */
    public function getAll() {

        $servers = Hash::extract($this->find('all', array(
            'fields' => array(
                'server_id', 'name', 'short_name', 'parent_id'
            )
        )), '{n}.Server');

        foreach ($servers as &$server) {

            $parent_id = $server['parent_id'];

            if (empty($parent_id)) {
                $server['sort_index'] = $server['server_id'] * 2;
            } else {
                $server['sort_index'] = $parent_id * 2 + 1;
            }
        }

        $servers = Hash::sort($servers, '{n}.sort_index');

        return $servers;
    }

/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'short_name' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
