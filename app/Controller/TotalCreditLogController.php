<?php
App::uses('AppController', 'Controller');

/**
 * Class TotalCreditLogController
 *
 * @property TotalCreditLog $TotalCreditLog
 */
class TotalCreditLogController extends AppController {
    public $components = array('Conversion', 'RequestHandler');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Admin index page
     */
    public function index() {

        if (!$this->Access->check('Stats', 'read')) {
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $this->loadShoutbox();
    }

    /**
     * Returns json data for total cash over time.
     */
    public function totals() {

        if (!$this->Access->check('Stats', 'read')) {
            $this->autoRender = false;
            return;
        }

        $this->set(array(
            'allTime' => $this->TotalCreditLog->getAllTime(),
            '_serialize' => array('allTime')
        ));
    }

}