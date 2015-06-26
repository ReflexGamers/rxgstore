<?php
App::uses('AppController', 'Controller');

/**
 * Class StatsController
 */
class StatsController extends AppController {
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

        $this->set(array(
            'dayAgo' => strtotime('-1 day'),
            'weekAgo' => strtotime('-1 week'),
            'monthAgo' => strtotime('-1 month')
        ));

        $this->loadShoutbox();
    }
}