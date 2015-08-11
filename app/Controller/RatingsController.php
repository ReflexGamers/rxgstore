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

    /**
     * Rates the item specified by item_id, then returns partial view of summed ratings for that item. If the user is
     * unable to rate the item for some reason, it will simply render the view.
     *
     * @param int $item_id the item to rate
     */
    public function rate($item_id = null) {

        $this->request->allowMethod('post');
        $this->loadModel('Item');

        if (!$this->Rating->Item->exists($item_id)) {
            throw new NotFoundException(__('Invalid item'));
        }

        if (!$this->request->is('ajax')) {
            $this->redirect($this->referer());
            return;
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
                'ratings' => $this->Rating->Item->getRating($item_id),
                'userRating' => $this->Rating->Item->getUserRating($item_id, $user_id)
            ));
        }

        $this->render('rate.inc');
    }
}