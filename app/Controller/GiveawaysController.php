<?php
App::uses('AppController', 'Controller');

/**
 * Giveaways Controller
 *
 * @property Giveaway $Giveaway
 * @property ServerUtilityComponent $ServerUtility
 */
class GiveawaysController extends AppController {
    public $components = array('Paginator', 'RequestHandler', 'ServerUtility');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Shows the Giveaways index page.
     */
    public function index() {

        if (!$this->Access->check('Giveaways', 'read')) {
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $giveaways = $this->Giveaway->getList();

        $this->loadItems();
        $this->loadDivisions();

        $this->set(array(
            'giveaways' => $giveaways
        ));

        $this->recent(false);
    }

    /**
     * Global activity for all giveaways (activity = claims), called from the index action or via
     * ajax directly.
     *
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function recent($forceRender = true) {

        $this->Paginator->settings = $this->Giveaway->GiveawayClaim->getActivityQuery(5);
        $claims = $this->Paginator->paginate('GiveawayClaim');

        // flatten details
        foreach ($claims as &$claim) {
            $claim['GiveawayClaimDetail'] = Hash::combine(
                $claim['GiveawayClaimDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        $this->addPlayers($claims, '{n}.GiveawayClaim.user_id');

        $this->loadItems();

        $this->set(array(
            'pageModel' => 'GiveawayClaim',
            'activities' => $claims,
            'activityPageLocation' => array('controller' => 'Giveaways', 'action' => 'recent')
        ));

        if ($forceRender) {
            $this->set(array(
                'standalone' => true,
                'title' => 'Recently Claimed Giveaways'
            ));
            $this->render('/Activity/list');
        }
    }

    /**
     * Activity for a single giveaway (activity = claims), called from the index action or via ajax
     * directly.
     *
     * @param array|int $giveaway giveaway data passed from another action or giveaway_id if ajax
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function activity($giveaway, $forceRender = true) {

        if ($forceRender) {

            // probably called standalone, so fetch giveaway data using $giveaway as giveaway_id
            $giveaway_id = $giveaway;
            $giveaway = $this->Giveaway->find('first', array(
                'conditions' => array(
                    'giveaway_id' => $giveaway_id
                )
            ));

            if (empty($giveaway)) {
                throw new NotFoundException(__('Invalid giveaway'));
            }

            $this->set('giveaway', $giveaway);
        } else {
            $giveaway_id = $giveaway['Giveaway']['giveaway_id'];
        }

        $this->loadModel('GiveawayClaim');
        $this->Paginator->settings = array(
            'GiveawayClaim' => array(
                'conditions' => array(
                    'Giveaway.giveaway_id' => $giveaway_id
                ),
                'contain' => array(
                    'Giveaway',
                    'GiveawayClaimDetail'
                ),
                'limit' => 5
            )
        );

        $claims = $this->Paginator->paginate('GiveawayClaim');

        // flatten details
        foreach ($claims as &$claim) {
            $claim['GiveawayClaimDetail'] = Hash::combine(
                $claim['GiveawayClaimDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        $this->addPlayers($claims, '{n}.GiveawayClaim.user_id');

        $this->loadItems();

        $this->set(array(
            'pageModel' => 'GiveawayClaim',
            'activities' => $claims,
            'activityPageLocation' => array('controller' => 'Giveaways', 'action' => 'activity', 'id' => $giveaway_id)
        ));

        if ($forceRender) {
            $this->set(array(
                'standalone' => true,
                'title' => "{$giveaway['Giveaway']['name']} Claims"
            ));
            $this->render('/Activity/list');
        }
    }

    /**
     * Claims a giveaway.
     *
     * @param int $giveaway_id
     */
    public function claim($giveaway_id) {

        $user_id = $this->Auth->user('user_id');

        $memberInfo = $this->Access->getMemberInfo(array($user_id));
        $isMember = isset($memberInfo[$user_id]);
        $game = $this->Auth->user('ingame');

        if (empty($game) && !empty($memberInfo[$user_id])) {
            // use the member's division id as the game
            $game = $memberInfo[$user_id]['division'];
        }

        $acceptedItems = $this->Giveaway->claim($giveaway_id, $user_id, $game, $isMember);

        if (!empty($acceptedItems)) {

            // broadcast & refresh user's inventory
            $this->loadModel('User');
            $server = $this->User->getCurrentServer($user_id);

            if ($server) {
                $giveaway = $this->Giveaway->find('first', array(
                    'fields' => array('name'),
                    'conditions' => array('giveaway_id' => $giveaway_id)
                ));
                $giveaway['GiveawayDetail'] = array_map(function($item_id, $quantity) {
                    return array(
                        'item_id' => $item_id,
                        'quantity' => $quantity
                    );
                }, array_keys($acceptedItems), $acceptedItems);

                $this->ServerUtility->broadcastGiveawayClaim($server, $user_id, $giveaway);
            }
        }

        $this->loadItems();
        $this->loadModel('User');

        $this->User->id = $user_id;
        $credit = $this->User->field('credit');

        $this->set(array(
            'credit' => $credit,
            'userItems' => $this->User->getItems($user_id)
        ));

        $this->render('/Items/browse_inventory.inc');
    }

    /**
     * View a single giveaway.
     *
     * @param int $giveaway_id
     */
    public function view($giveaway_id) {

        if (!$this->Access->check('Giveaways', 'read')) {
            $this->redirect($this->referer());
            return;
        }

        if (empty($giveaway_id)) {
            throw new NotFoundException(__('Invalid giveaway'));
        }

        $giveawayData = $this->Giveaway->getWithDetails($giveaway_id);

        if (empty($giveawayData)) {
            throw new NotFoundException(__('Invalid giveaway'));
        }

        $this->loadItems();

        $giveaway = $giveawayData['Giveaway'];
        $giveaway['GiveawayDetail'] = $giveawayData['GiveawayDetail'];

        $this->set(array(
            'giveaway' => $giveaway
        ));

        $this->activity($giveawayData, false);
    }

    /**
     * Create a giveaway.
     */
    public function add() {

        if (!$this->Access->check('Giveaways', 'create')) {
            $this->redirect($this->referer());
            return;
        }

        if ($this->request->is('post')) {

            $this->Giveaway->create();
            $data = $this->request->data;

            $saveSuccessful = $this->Giveaway->saveWithDetails($data);

            $admin_steamid = $this->Auth->user('steamid');

            if ($saveSuccessful) {
                $this->Session->setFlash('Giveaway created successfully.', 'flash', array('class' => 'success'));
                CakeLog::write('giveaways', "$admin_steamid created giveaway '{$data['Giveaway']['name']}'.");
//                $this->redirect(array('action' => 'view', 'id' => $this->Giveaway->id));
                $this->redirect(array('action' => 'index'));
                return;
            } else {
                $this->Session->setFlash('There was an error creating the giveaway.', 'flash', array('class' => 'error'));
                CakeLog::write('giveaways', "$admin_steamid failed to create giveaway '{$data['Giveaway']['name']}'.");
                $this->redirect(array('action' => 'index'));
                return;
            }
        }

        $this->loadItems();

        // format like 'tf2' => 'Team Fortress 2'
        $divisions = Hash::combine(array_filter(Configure::read('Store.Divisions'), function($division) {
            // divisions are supported unless 'supported' is false
            return !isset($division['supported']) || $division['supported'] !== false;
        }), '{n}.division_id', '{n}.name');

        $this->set(array(
            'divisionOptions' => $divisions
        ));
    }

    /**
     * Create or edit a giveaway (create if no id).
     *
     * @param int $giveaway_id
     */
    public function edit($giveaway_id) {

        if (!$this->Access->check('Giveaways', 'update')) {
            $this->redirect($this->referer());
            return;
        }

        if (empty($giveaway_id) || !$this->Giveaway->exists($giveaway_id)) {
            throw new NotFoundException(__('Invalid giveaway'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

            $data = $this->request->data;
            $saveSuccessful = $this->Giveaway->saveWithDetails($data);

            $admin_steamid = $this->Auth->user('steamid');

            if ($saveSuccessful) {
                $this->Session->setFlash('Giveaway updated successfully.', 'flash', array('class' => 'success'));
                CakeLog::write('giveaways', "$admin_steamid updated giveaway #$giveaway_id ('{$data['Giveaway']['name']}').");
            } else {
                $this->Session->setFlash('There was an error updating the giveaway.', 'flash', array('class' => 'error'));
                CakeLog::write('giveaways', "$admin_steamid failed to update giveaway #$giveaway_id ('{$data['Giveaway']['name']}').");
            }
        }

        $this->loadItems();

        // fetch it all again since saving it could have modified it slightly
        $giveaway = $this->Giveaway->getWithDetails($giveaway_id);

        // format like 'tf2' => 'Team Fortress 2'
        $divisions = Hash::combine(array_filter(Configure::read('Store.Divisions'), function($division) {
            // divisions are supported unless 'supported' is false
            return !isset($division['supported']) || $division['supported'] !== false;
        }), '{n}.division_id', '{n}.name');

        $this->set(array(
            'data' => $giveaway,
            'divisionOptions' => $divisions
        ));

        $this->activity($giveaway, false);
    }

    /**
     * Deletes a giveaway.
     *
     * @param int $giveaway_id
     */
    public function delete($giveaway_id) {

        $this->request->allowMethod('post');

        if (!$this->Access->check('Giveaways', 'delete')) {
            $this->redirect($this->referer());
            return;
        }

        if (empty($giveaway_id) || !$this->Giveaway->exists($giveaway_id)) {
            throw new NotFoundException(__('Invalid giveaway'));
        }

        $admin_steamid = $this->Auth->user('steamid');

        $this->Giveaway->deleteWithDetails($giveaway_id);
        $this->Session->setFlash('The Giveaway was deleted.', 'flash', array('class' => 'success'));
        CakeLog::write('giveaways', "$admin_steamid deleted giveaway #$giveaway_id.");

        $this->redirect(array('action' => 'index'));
    }
}