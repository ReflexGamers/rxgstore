<?php

/**
 * Class StoreController
 *
 * @property ConversionComponent $Conversion
 * @property PermissionsComponent $Permissions
 */
class StoreController extends AppController {
    public $components = array('Conversion', 'Permissions');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function index() {
    }

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