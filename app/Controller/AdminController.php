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

	/**
	 * View log by name
	 *
	 * @param string $name name of log to view
	 */
	public function viewlog($name) {

		if (!$this->Access->check('Stock', 'update')) {
			$this->redirect($this->referer());
			return;
		}

		$this->renderLog($name);
	}
}