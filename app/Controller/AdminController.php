<?php
App::uses('AppController', 'Controller');

/**
 * Class AdminController
 */
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
	public function viewlog($name = null) {

		if (!$this->Access->check('Logs', 'read')) {
			$this->redirect($this->referer());
			return;
		}

		if (!empty($name)) {

			$logFile = new File("../tmp/logs/$name.log", false);
			$log = $logFile->read();
			$logFile->close();

			$this->response->type('text/plain');
			$this->response->body($log);
			$this->autoRender = false;
		}
	}
}