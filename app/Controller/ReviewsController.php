<?php
App::uses('AppController', 'Controller');

/**
 * Reviews Controller
 *
 * @property Review $Review
 * @property ServerUtilityComponent $ServerUtility
 */
class ReviewsController extends AppController {
	public $components = array('RequestHandler', 'ServerUtility');
	public $helpers = array('Html', 'Time', 'Js', 'Form');


	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function save($type, $item_id) {

		$this->request->allowMethod('post');
		$data = $this->request->data['Review'];

		$item = $this->Review->Rating->Item->read(array('item_id', 'name', 'short_name'), $item_id);

		if (empty($item)) {
			throw new NotFoundException(__('Invalid item'));
		}

		if (!$this->request->is('ajax')) {
			$this->redirect($this->referer());
		}

		$user_id = $this->Auth->user('user_id');

		if (!empty($data['review_id'])) {
			//updating
			$reviewData = $this->Review->find('first', array(
				'conditions' => array(
					'review_id' => $data['review_id']
				),
				'contain' => 'Rating'
			));
			$oldReview = array_merge($reviewData['Review'], $reviewData['Rating']);
			$user_id = $oldReview['user_id'];
		} else {
			//1st time submit
			$oldReview = $this->Review->Rating->getByItemAndUser($item_id, $user_id);
		}

		if (!empty($oldReview) && !empty($data)) {

			if ($oldReview['content'] != $data['content']) {

				//TODO make this code neater
				$review = array(
					'content' => $data['content']
				);

				if (empty($oldReview['review_id'])) {

					//New review
					$this->loadModel('Activity');
					$review['review_id'] = $this->Activity->getNewId('Review');
					$review['rating_id'] = $oldReview['rating_id'];
					$review['modified'] = false;

					//Broadcast & refresh user's inventory
					//TODO prevent spamming new reviews via deletion/creation
					$this->loadModel('User');
					$server = $this->User->getCurrentServer($user_id);

					if (!empty($server)) {
						$this->ServerUtility->broadcastReview($server, $user_id, $item_id);
					}

				} else {

					//Updating old review
					$review['review_id'] = $oldReview['review_id'];
					$review['created'] = $oldReview['created'];
				}

				$this->Review->save($review);
			}

			$review = $this->Review->Rating->getByItemAndUser($item_id, $user_id);
			$review['quantity'] = $this->Review->Rating->User->getTotalBoughtByItem($user_id, $item_id);
		}

		$this->addPlayers($user_id);

		$this->set(array(
			'item' => $item['Item'],
			'review' => isset($review) ? $review : $oldReview,
			'displayType' => $type
		));

		$this->render('single.inc');
	}

	public function delete($type, $review_id) {

		$this->request->allowMethod('post');

		$reviewData = $this->Review->find('first', array(
			'conditions' => array(
				'review_id' => $review_id,
			),
			'contain' => 'Rating'
		));

		if (empty($reviewData['Review'])) {
			throw new NotFoundException(__('Invalid review'));
		}

		if (!$this->request->is('ajax')) {
			$this->redirect($this->referer());
		}

		$item = $this->Review->getItemByReviewId($review_id);

		if ($this->Access->check('Reviews', 'delete')) {
			$this->Review->delete($review_id, false);

			$this->loadModel('Activity');
			$this->Activity->delete($review_id, false);
		}

		$user_id = $this->Auth->user('user_id');

		if ($type == 'item' && $reviewData['Rating']['user_id'] == $user_id) {

			$this->addPlayers($user_id);

			$this->set(array(
				'item' => $item,
				'review' => $reviewData['Rating'],
				'displayType' => $type
			));

			$this->render('compose.inc');
		} else {
			$this->autoRender = false;
		}
	}


	public function view($type, $review_id) {
		$this->_single($type, $review_id);
	}

	public function edit($type, $review_id) {
		$this->_single($type, $review_id, true);
	}

	protected function _single($type, $review_id, $edit = false) {

		$item = $this->Review->getItemByReviewId($review_id);

		if (empty($item)) {
			throw new NotFoundException(__('Invalid item'));
		}

		if (!$this->request->is('ajax')) {
			$this->redirect($this->referer());
		}

		$reviewData = $this->Review->find('first', array(
			'conditions' => array(
				'review_id' => $review_id
			),
			'contain' => 'Rating'
		));

		$review = array_merge($reviewData['Review'], $reviewData['Rating']);
		$user_id = $review['user_id'];

		if (!$edit) {
			$review['quantity'] = $this->Review->Rating->User->getTotalBoughtByItem($user_id, $item['item_id']);
		}

		$this->addPlayers($user_id);

		$this->set(array(
			'item' => $item,
			'review' => $review,
			'isEditMode' => $edit,
			'displayType' => $type
		));

		$this->render('single.inc');
	}
}
