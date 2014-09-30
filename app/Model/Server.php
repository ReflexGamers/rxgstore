<?php
App::uses('AppModel', 'Model');
/**
 * Server Model
 *
 * @property ServerItem $ServerItem
 */
class Server extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'server';
	public $primaryKey = 'server_id';

	public $hasMany = array(
		'ServerItem', 'UserServer'
	);

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

		/*return $this->find('list', array(
			'fields' => array('short_name', 'name')
		));*/
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
		'short_name' => array(
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
