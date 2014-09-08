<?php
App::uses('Component', 'Controller');

class AccessComponent extends Component {
	public $components = array('Auth', 'Acl');
	public $cache = array();
	public $user;

	public function initialize(Controller $controller) {
		$this->user = $this->Auth->user();
	}

	public function check($aco, $action = '*') {
		if (empty($this->user)) return false;

		//Check for cached perm in this request
		if (isset($this->cache[$aco][$action])) {
			return $this->cache[$aco][$action];
		}

		$user_id = $this->user['user_id'];
		$canAccess = $this->checkUser($user_id, $aco, $action);

		//Cache perms for later in this request
		if (empty($this->cache[$aco])) {
			$this->cache[$aco] = array($action => $canAccess);
		} else {
			$this->cache[$aco][$action] = $canAccess;
		}

		return $canAccess;
	}

	public function findUser($user_id) {
		return $this->Acl->Aro->find('first', array(
			'fields' => 'id',
			'conditions' => array(
				'foreign_key' => $user_id
			),
			'recursive' => -1
		));
	}

	public function checkIsMember($user_id) {

		//All members and up have records in the aro table
		$aro = $this->findUser($user_id);
		return !empty($aro);
	}

	public function getMemberStatus($ids) {

		$members = array();

		$data = Hash::combine($this->Acl->Aro->find('all', array(
			'fields' => 'foreign_key',
			'conditions' => array(
				'foreign_key' => $ids
			),
			'recursive' => -1
		)), '{n}.Aro.foreign_key', '{n}.Aro.foreign_key');

		foreach ($ids as $id) {
			$members[$id] = isset($data[$id]);
		}

		return $members;
	}

	public function checkUser($user_id, $aco, $action = '*') {

		return $this->checkIsMember($user_id) ? $this->Acl->check(array('model' => 'User', 'foreign_key' => $user_id), $aco, $action) : false;
	}
}