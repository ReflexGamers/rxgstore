<?php
App::uses('AppController', 'Controller');

/**
 * Promotions Controller
 *
 * @property Promotion $Promotion
 */
class PromotionsController extends AppController {
    public $components = array('Paginator', 'RequestHandler');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Shows the Promotions index page.
     */
    public function index() {

        if (!$this->Access->check('Promotions', 'read')) {
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $promotions = $this->Promotion->find('all', array(
            'contain' => 'PromotionDetail'
        ));

        $this->loadItems();

        $this->set(array(
            'promotions' => $promotions
        ));
    }

    /**
     * View a single promotion.
     *
     * @param null $promotion_id
     */
    public function view($promotion_id = null) {

        if (!$this->Access->check('Promotions', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        if (!$promotion_id) {
            throw new NotFoundException(__('Invalid promotion'));
        }

        $promotion = $this->Promotion->getWithDetails($promotion_id);

        if (empty($promotion)) {
            throw new NotFoundException(__('Invalid promotion'));
        }

        $this->loadItems();

        $this->set(array(
            'promotion' => $promotion
        ));
    }

    /**
     * Add a promotion.
     */
    public function add() {

        if (!$this->Access->check('Promotions', 'create')) {
            $this->redirect($this->referer());
            return;
        }

        if ($this->request->is('post')) {

            $this->Promotion->create();
            $data = $this->request->data;

            $saveSuccessful = $this->Promotion->saveWithDetails($data);

            if ($saveSuccessful) {
                $this->Session->setFlash('Promotion created successfully.', 'flash', array('class' => 'success'));
                $this->redirect(array('action' => 'view', 'id' => $this->Promotion->id));
            } else {
                $this->Session->setFlash('There was an error creating the promotion.', 'flash', array('class' => 'error'));
                $this->redirect(array('action' => 'index'));
            }
        }

        $this->loadItems();
    }

    /**
     * Create or edit a promotion (create if no id).
     *
     * @param null $promotion_id
     */
    public function edit($promotion_id = null) {

        if (!$this->Access->check('Promotions', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        if (!$promotion_id) {
            throw new NotFoundException(__('Invalid promotion'));
        }

        if (!$this->Promotion->exists($promotion_id)) {
            throw new NotFoundException(__('Invalid promotion'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            $data = $this->request->data;
            $saveSuccessful = $this->Promotion->saveWithDetails($data);

            if ($saveSuccessful) {
                $this->Session->setFlash('Promotion updated successfully.', 'flash', array('class' => 'success'));
            } else {
                $this->Session->setFlash('There was an error updating the promotion.', 'flash', array('class' => 'error'));
            }
        }

        $this->loadItems();

        // fetch it all again since saving it could have modified it slightly
        $data = $this->Promotion->getWithDetails($promotion_id);

        $this->set(array(
            'data' => $data
        ));
    }

    /**
     * Delete a promotion.
     *
     * @param null $promotion_id
     */
    public function delete($promotion_id = null) {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Promotions', 'delete')) {
            $this->redirect($this->referer());
            return;
        }

        if (!$promotion_id || !$this->Promotion->exists($promotion_id)) {
            throw new NotFoundException(__('Invalid promotion'));
        }

        $this->Promotion->deleteWithDetails($promotion_id);
        $this->Session->setFlash('The Promotion was deleted.', 'flash', array('class' => 'success'));

        $this->redirect(array('action' => 'index'));
    }
}