<?php
App::uses('AppController', 'Controller');

class AdminController extends AppController {
	public $components = array('RequestHandler');
	public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	/**
	 * Admin index page
	 */
	public function index() {

		if (!$this->Access->check('Stock', 'update')) {
			$this->redirect($this->referer());
			return;
		}
	}

	public function viewlog() {

		if (!$this->Access->check('Stock', 'update')) {
			$this->redirect($this->referer());
			return;
		}

		$this->renderLog('admin');
	}
}