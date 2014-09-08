<?php
App::uses('AppController', 'Controller');

/**
 * ShoutboxMessages Controller
 *
 * @property ShoutboxMessage $ShoutboxMessage
 * @property PaginatorComponent $Paginator
 * @property RequestHandlerComponent $RequestHandler
 */
class ShoutboxMessagesController extends AppController {
	public $components = array('RequestHandler');
	public $helpers = array('Html', 'Form', 'Time');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow();
		$this->Auth->deny('add');
	}

	public function view($time = null) {

		if (!empty($time)) {
			$message = $this->ShoutboxMessage->find('first', array('fields' => array('date')));

			if (empty($message) || strtotime($message['ShoutboxMessage']['date']) <= $time) {
				$this->autoRender = false;
				$this->response->notModified();
				return;
			}
		}

		$this->loadShoutboxData();
		$this->render('view.inc');
	}

	public function add() {

		$this->request->allowMethod('post');
		$user = $this->Auth->user();

		if (isset($user) && !empty($this->request->data['ShoutboxMessage'])) {

			if ($this->ShoutboxMessage->canUserPost($user['user_id'])) {

				$this->ShoutboxMessage->save(array(
					'user_id' => $user['user_id'],
					'content' => $this->request->data['ShoutboxMessage']['content']
				));

			} else {

				$this->set('userCantPost', true);

			}
		}

		$this->view();
	}

	public function delete($id) {

		$this->request->allowMethod('post');

		if (!$this->Access->check('Chats', 'delete')) {
			$this->redirect($this->referer());
			return;
		}

		$this->ShoutboxMessage->delete($id);
		$this->autoRender = false;
	}
}