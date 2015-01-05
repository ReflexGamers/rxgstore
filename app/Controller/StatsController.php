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
            'dayAgo' => time() - 86400,
            'weekAgo' => time() - 604800,
            'monthAgo' => time() - 2592000
        ));

        $this->loadShoutbox();
    }
}