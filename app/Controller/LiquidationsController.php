<?php
App::uses('AppController', 'Controller');

/**
 * Liquidations Controller
 *
 * @property Liquidation $Liquidation
 * @property ServerUtilityComponent $ServerUtility
 *
 * Magic Properties (for inspection):
 * @property Activity $Activity
 * @property UserItem $UserItem
 */
class LiquidationsController extends AppController {
    public $components = array('Paginator', 'RequestHandler', 'ServerUtility');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Shows the compose liquidation page.
     */
    public function compose() {

        $user_id = $this->Auth->user('user_id');

        // check for existing details if returning to compose page
        $details = $this->Session->read('liquidationDetails');

        if (!empty($details)) {
            $this->set(array(
                'details' => $details
            ));
        }

        $this->loadItems();

        $this->set(array(
            'userItems' => $this->User->getItems($user_id),
            'composing' => true
        ));

        $this->loadShoutbox();
        $this->activity(false);
    }

    /**
     * Shows the preview liquidation page.
     */
    public function preview() {

        $this->request->allowMethod('post');

        $liquidationDetails = array_filter(Hash::combine(
            $this->request->data['LiquidationDetail'],
            '{n}.item_id', '{n}.quantity'
        ), function($quantity) {
            return !empty($quantity);
        });

        if (empty($liquidationDetails)) {
            $this->redirect(array('action' => 'compose'));
            return;
        }

        $this->Session->write('liquidationDetails', $liquidationDetails);

        $user_id = $this->Auth->user('user_id');
        $userItems = $this->User->getItems($user_id);

        if (!$this->Liquidation->User->hasItemQuantities($userItems, $liquidationDetails)) {
            $this->Flash->set('You no longer have enough items for this return.', ['params' => ['class' => 'error']]);
            $this->redirect(array('action' => 'compose'));
            return;
        }

        $this->loadItems();

        $this->set(array(
            'userItems' => $userItems,
            'details' => $liquidationDetails
        ));

        $this->Flash->set('Please check these details and then click confirm.');
        $this->render('compose');
    }

    /**
     * Completes the liquidation and shows a receipt. The liquidation data should be set in the session by the preview action.
     */
    public function submit() {

        $this->request->allowMethod('post');

        $liquidationDetails = $this->Session->read('liquidationDetails');

        if (empty($liquidationDetails)) {
            $this->Flash->set('Oops! You do not have any items selected.', ['params' => ['class' => 'error']]);
            $this->redirect(array('action' => 'compose'));
            return;
        }

        $user_id = $this->Auth->user('user_id');

        try {
            $result = $this->Liquidation->performLiquidation($user_id, $liquidationDetails);
        } catch (InsufficientItemsException $e) {
            $this->Flash->set('You no longer have enough items for this return.', ['params' => ['class' => 'error']]);
            $this->redirect(array('action' => 'compose'));
            return;
        }

        if (!$result['Liquidation'] || in_array(false, $result['LiquidationDetail'])) {
            $this->Flash->set('There was an error performing the return. Please contact an administrator', ['params' => ['class' => 'error']]);
            $this->redirect(array('action' => 'compose'));
            return;
        }

        $this->Session->delete('liquidationDetails');

        $this->Flash->set('Your items were successfully returned.', ['params' => ['class' => 'success']]);
        $this->redirect(array('action' => 'compose'));
    }

    /**
     * Shows the activity data for liquidations. This is either included in the compose page or called via ajax for paging.
     *
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function activity($forceRender = true) {

        $this->Paginator->settings = $this->Liquidation->getActivityQuery(5);
        $liquidations = $this->Paginator->paginate('Liquidation');

        foreach ($liquidations as &$liquidation) {
            $total = 0;

            foreach ($liquidation['LiquidationDetail'] as $detail) {
                $total += $detail['price'] * $detail['quantity'];
            }

            $liquidation['Liquidation']['total'] = $total;

            $liquidation['LiquidationDetail'] = Hash::combine(
                $liquidation['LiquidationDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        $this->addPlayers($liquidations, '{n}.Liquidation.user_id');

        $this->loadItems();

        $this->set(array(
            'pageModel' => 'Liquidation',
            'activities' => $liquidations,
            'activityPageLocation' => array('controller' => 'Liquidations', 'action' => 'activity')
        ));

        if ($forceRender) {
            $this->set(array(
                'standalone' => true,
                'title' => 'Return Activity'
            ));
            $this->render('/Activity/list');
        }
    }
}
