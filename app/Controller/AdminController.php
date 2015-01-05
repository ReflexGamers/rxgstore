<?php
App::uses('AppController', 'Controller');

/**
 * Class AdminController
 *
 * @property ConversionComponent $Conversion
 * @property PermissionsComponent $Permissions
 */
class AdminController extends AppController {
    public $components = array('Conversion', 'Permissions', 'RequestHandler');
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
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $this->loadShoutbox();
    }

    /**
     * Admin stats page
     */
    public function stats() {

        if (!$this->Access->check('Stock', 'update')) {
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $this->loadShoutbox();
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

    /**
     * Copies the old database to the new one with the necessary changes to data format.
     */
    public function convert() {

//        set_time_limit(300);
//
//        $this->Permissions->dumpAll();
//        $this->Permissions->initAll();
//        $this->Permissions->syncAll();
//
//        $this->Conversion->convertUsers();
//        $this->Conversion->convertInventories();
//        $this->Conversion->convertOrders();
//
//        $this->autoRender = false;
    }
}