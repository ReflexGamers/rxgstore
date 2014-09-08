<?php
App::uses('AppController', 'Controller');

/**
 * Ratings Controller
 *
 * @property Rating $Rating
 */
class RatingsController extends AppController {
	public $components = array('RequestHandler');
	public $helpers = array('Html');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function rate($item_id = null) {

		$this->request->allowMethod('post');
		$this->loadModel('Item');

		if (!$this->Rating->Item->exists($item_id)) {
			throw new NotFoundException(__('Invalid item'));
		}

		if (!$this->request->is('ajax')) {
			$this->redirect($this->referer());
		}

		$user_id = $this->Auth->user('user_id');
		$userCanRate = $this->Rating->User->canRateItem($user_id, $item_id);

		if ($userCanRate) {

			$newValue = $this->request->data['rating'];
			$oldRating = $this->Rating->findByItemIdAndUserId($item_id, $user_id);

			if (empty($oldRating)) {
				$this->Rating->save(array(
					'item_id' => $item_id,
					'user_id' => $user_id,
					'rating' => $newValue
				));
			} else if ($newValue != $oldRating['Rating']['rating']) {
				$oldRating['Rating']['rating'] = $newValue;
				$this->Rating->save($oldRating);
			}

			$this->set(array(
				'item' => array('item_id' => $item_id),
				'userCanRate' => $userCanRate,
				'ratings' => $this->Rating->Item->getTotalRatings($item_id)
			));
		}

		$this->render('rate.inc');
	}
}