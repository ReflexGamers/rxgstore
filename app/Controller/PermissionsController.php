<?php
App::uses('AppController', 'Controller');
App::uses('File', 'Utility');

/**
 * Class PermissionsController
 *
 * @property PermissionsComponent $Permissions
 */
class PermissionsController extends AppController {
    public $components = array('RequestHandler', 'Permissions');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * The permissions view page that shows all the admins and members.
     *
     * @param string $view optional name of view to render instead of the default
     */
    public function view($view = null) {

        if (!$this->Access->check('Permissions', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        $aro = $this->Acl->Aro;

        $admins = Hash::map($aro->find('all', array(
            'fields' => array(
                'Aro.foreign_key as user_id', 'Aro.alias as name', 'Aro.division as division_id', 'AroParent.alias as rank'
            ),
            'conditions' => array(
                'Aro.foreign_key is not null'
            ),
            'joins' => array(
                array(
                    'table' => 'aros',
                    'alias' => 'AroParent',
                    'conditions' => array(
                        'Aro.parent_id = AroParent.id'
                    )
                )
            ),
            'order' => 'AroParent.id desc, Aro.alias asc',
            'recursive' => -1
        )), '{n}', function($admin){
            return array_merge($admin['Aro'], $admin['AroParent']);
        });

        $this->addPlayers($admins, '{n}.user_id');

        $this->set(array(
            'members' => $admins
        ));

        $this->loadShoutbox();
        $this->loadDivisions();

        if (!empty($view)) {
            $this->render($view);
        }
    }

    /**
     * Used for manual synchronization of permissions. Should be called by ajax and will return the render the player
     * list as the response.
     */
    public function synchronize() {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Permissions', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        $syncResult = $this->Permissions->syncAll();

        $syncResult['added'] = Hash::extract($syncResult, 'added.{n}.alias');
        $syncResult['updated'] = Hash::extract($syncResult, 'updated.{n}.alias');
        $syncResult['removed'] = Hash::extract($syncResult, 'removed.{n}.alias');

        $this->set('syncResult', $syncResult);
        $this->view('list.inc');
    }

    /**
     * Rebuilds all the access control tables.
     */
    public function rebuild() {

        if (!$this->Access->check('Debug')) {
            $this->redirect($this->referer());
            return;
        }

        $this->Permissions->dumpAll();
        $this->Permissions->initAll();
        $this->Permissions->syncAll();

        $this->view('list.inc');
    }
}