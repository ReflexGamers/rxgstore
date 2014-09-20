<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');

class PermissionsController extends AppController {
	public $components = array('RequestHandler');
	public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function view($view = null) {

		if (!$this->Access->check('Permissions', 'read')) {
			$this->redirect($this->referer());
			return;
		}

		$aro = $this->Acl->Aro;

		$admins = Hash::map($aro->find('all', array(
			'fields' => array(
				'Aro.foreign_key as user_id', 'Aro.alias as name', 'AroParent.alias as rank'
			),
			'conditions' => array(
				'Aro.foreign_key is not null'
			),
			'joins' => array(
				array(
					'table' => 'aros',
					'alias' => 'AroParent',
					'conditions' => array(
						'Aro.parent_id = AroParent.id'
					)
				)
			),
			'order' => 'AroParent.id desc',
			'recursive' => -1
		)), '{n}', function($admin){
			return array_merge($admin['Aro'], $admin['AroParent']);
		});

		$this->addPlayers(Hash::extract($admins, '{n}.user_id'));

		$this->set(array(
			'members' => $admins
		));

		if (!empty($view)) {
			$this->render($view);
		}
	}

	public function synchronize() {

		$this->request->allowMethod('post');

		if (!$this->Access->check('Permissions', 'update')) {
			$this->redirect($this->referer());
			return;
		}

		$syncResult = $this->AccountUtility->syncSourcebans();

		$syncResult['added'] = Hash::extract($syncResult, 'added.{n}.alias');
		$syncResult['updated'] = Hash::extract($syncResult, 'updated.{n}.alias');
		$syncResult['removed'] = Hash::extract($syncResult, 'removed.{n}.alias');

		$this->set('syncResult', $syncResult);
		$this->view('list.inc');
	}

	public function viewlog() {

		if (!$this->Access->check('Permissions', 'read')) {
			$this->redirect($this->referer());
			return;
		}

		$this->renderLog('permsync');
	}
}