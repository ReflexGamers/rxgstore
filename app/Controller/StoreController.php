<?php

class StoreController extends AppController {
    public $components = array('Conversion', 'AccountUtility');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function index() {
    }

    public function store($server = '') {

        if ($this->request->is('post') && isset($this->request->data['server']['short_name'])) {
            $server = $this->request->data['server']['short_name'];
        }

        $this->loadModel('Item');
        $this->loadModel('Stock');

        if (empty($server)) {
            $serverItems = $this->Item->getBuyable();
        } else {
            $serverItems = $this->Item->getByServer($server);

            //Redirect on unrecognized game
            if (empty($serverItems) && !$this->request->is('ajax')) {
                $this->redirect(array('controller' => 'Store', 'action' => 'store'));
            }

            $this->set('game', $server);
        }

        if (!$this->request->is('ajax')) {

            $this->loadModel('User');
            $this->loadModel('UserItem');
            $this->loadModel('Server');

            if ($this->Auth->user()) {

                $user = $this->User->findByUserId($this->Auth->user('user_id'))['User'];
                $useritem = $this->UserItem->getByUser($user['user_id']);

                $this->set('useritem', $useritem);
                $this->set('credit', $user['credit']);
            }

            //$this->set('servers', $this->Server->getList());
        }

        $this->set('stock', $this->Stock->find('list'));
        $this->set('items', $this->Item->getBuyable());
        $this->set('gameItems', $serverItems);
    }


    public function convert() {
        $this->Conversion->convertAll();
        $this->render('index');
    }

    public function setup_permissions() {

        $this->autoRender = false;

        $this->AccountUtility->initPermissions();
        $this->AccountUtility->syncPermissions();

    }
}