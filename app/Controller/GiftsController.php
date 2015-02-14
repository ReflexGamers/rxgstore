<?php
App::uses('AppController', 'Controller');

/**
 * Gifts Controller
 *
 * @property ServerUtilityComponent $ServerUtility
 *
 * Magic Properties (for inspection):
 * @property Activity $Activity
 * @property Gift $Gift
 * @property UserItem $UserItem
 */
class GiftsController extends AppController {
    public $components = array('Paginator', 'RequestHandler', 'ServerUtility');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Accepts a gift and re-renders the inventory in the response.
     *
     * Only the valid recipient of the gift may accept the gift, and only once. If the user tries to accept it again, it
     * will re-render their inventory but not update their items in the database.
     *
     * @param int $gift_id the id of the gift to accept
     * @broadcast gift contents
     */
    public function accept($gift_id) {

        $user_id = $this->Auth->user('user_id');
        $gift = $this->Gift->acceptPendingGift($gift_id, $user_id);

        if (!empty($gift)) {

            // broadcast & refresh user's inventory
            $this->loadModel('User');
            $server = $this->User->getCurrentServer($user_id);

            if ($server) {
                $this->ServerUtility->broadcastGiftReceive($server, $user_id, $gift);
            }
        }

        $this->loadItems();
        $this->loadModel('User');

        $this->set(array(
            'quantity' => $this->User->getItems($user_id)
        ));

        $this->render('/Items/list.inc');
    }

    /**
     * Shows the compose gift page.
     *
     * @param int $steamid the steamid of the gift recipient
     */
    public function compose($steamid) {

        if (empty($steamid)) {
            $this->redirect(array('controller' => 'SteamPlayerCache', 'action' => 'search'));
            return;
        }

        $player = $this->AccountUtility->getSteamInfo($steamid, true);

        if (empty($player)) {
            throw new NotFoundException(__('Invalid recipient'));
        }

        $user_id = $this->Auth->user('user_id');
        $recipient_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

        if ($user_id == $recipient_id) {
            $this->Session->setFlash('You cannot send a gift to yourself.', 'flash', array('class' => 'error'));
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $this->loadModel('Item');
        $this->loadModel('User');

        $this->set(array(
            'player' => $player,
            'userItems' => $this->User->getItems($user_id),
            'composing' => true
        ));

        // check session for gift in case returning to compose page
        $gift = $this->Session->read("gift-$steamid");

        if (!empty($gift)) {
            $this->set(array(
                'details' => Hash::combine($gift['GiftDetail'], '{n}.item_id', '{n}.quantity'),
                'message' => $gift['Gift']['message'],
                'anonymous' => $gift['Gift']['anonymous']
            ));
        }

        $this->loadShoutbox();
        $this->activity(false);
    }

    /**
     * Shows the activity data for gifts. This is either included in the compose page or called via ajax for paging.
     *
     * @param bool $forceRender whether to force render. set to false if calling from another action
     */
    public function activity($forceRender = true) {

        $this->Paginator->settings = $this->Gift->getActivityQuery(5);
        $gifts = $this->Paginator->paginate('Gift');

        foreach ($gifts as &$gift) {

            $gift['GiftDetail'] = Hash::combine(
                $gift['GiftDetail'],
                '{n}.item_id', '{n}.quantity'
            );
        }

        $this->addPlayers($gifts, '{n}.{s}.sender_id');
        $this->addPlayers($gifts, '{n}.{s}.recipient_id');

        $this->loadItems();

        $this->set(array(
            'pageModel' => 'Gift',
            'activities' => $gifts,
            'activityPageLocation' => array('controller' => 'Gifts', 'action' => 'activity')
        ));

        if ($forceRender) {
            $this->set(array(
                'standalone' => true,
                'title' => 'Gift Activity'
            ));
            $this->render('/Activity/list');
        }
    }

    /**
     * Packages the gift and shows the confirmation page.
     *
     * @param int $steamid of the recipient
     */
    public function package($steamid) {

        $this->request->allowMethod('post');

        if (empty($steamid)) {
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $player = $this->AccountUtility->getSteamInfo($steamid, true);
        if (empty($player)) {
            throw new NotFoundException(__('Invalid recipient'));
        }

        $giftDetails = $this->request->data['GiftDetail'];
        $message = empty($this->request->data['Gift']['message']) ? '' : $this->request->data['Gift']['message'];
        $anonymous = empty($this->request->data['Gift']['anonymous']) ? false : $this->request->data['Gift']['anonymous'];

        $user_id = $this->Auth->user('user_id');
        $recipient_id = $this->AccountUtility->AccountIDFromSteamID64($steamid);

        if ($user_id == $recipient_id) {
            $this->Session->setFlash('You cannot send a gift to yourself.', 'flash', array('class' => 'error'));
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        $this->loadModel('Item');
        $this->loadModel('User');

        $userItems = $this->User->getItems($user_id);
        $items = $this->loadItems();
        $totalValue = 0;

        foreach ($giftDetails as $key => $detail) {

            $item_id = $detail['item_id'];
            $quantity = $detail['quantity'];

            if (empty($quantity) || $quantity < 1) {
                unset($giftDetails[$key]);
                continue;
            }

            if (empty($userItems[$item_id]) || $userItems[$item_id] < $quantity) {
                $this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in your inventory to package this gift.", 'flash', array('class' => 'error'));
                $this->redirect(array('action' => 'compose', 'id' => $steamid));
                return;
            }

            //Current price for estimated value
            $totalValue += $items[$item_id]['price'] * $quantity;
        }

        if(empty($giftDetails)) {
            $this->redirect(array('action' => 'compose', 'id' => $steamid));
            return;
        }

        $this->Session->write("gift-$steamid", array(
            'Gift' => array(
                'sender_id' => $user_id,
                'recipient_id' => $this->AccountUtility->AccountIDFromSteamID64($steamid),
                'anonymous' => $anonymous,
                'message' => $message
            ),
            'GiftDetail' => $giftDetails
        ));

        $this->set(array(
            'player' => $player,
            'userItems' => $userItems,
            'details' => Hash::combine($giftDetails, '{n}.item_id', '{n}.quantity'),
            'message' => $message,
            'anonymous' => $anonymous,
            'totalValue' => $totalValue
        ));

        $this->Session->setFlash('Please confirm the contents of your gift below and then click send.');
        $this->render('compose');
    }

    /**
     * Sends the gift to the specified recipient.
     *
     * @param int $steamid of the recipient
     * @broadcast gift contents
     */
    public function send($steamid) {

        $this->request->allowMethod('post');

        $player = $this->AccountUtility->getSteamInfo($steamid, true);
        if (empty($player)) {
            throw new NotFoundException(__('Invalid recipient'));
        }

        $gift = $this->Session->read("gift-$steamid");

        if (empty($gift)) {
            $this->Session->setFlash('Oops! It appears you have not prepared a gift for this recipient.', 'flash', array('class' => 'error'));
            $this->redirect(array('action' => 'compose', 'id' => $steamid));
            return;
        }

        $this->loadModel('Item');
        $this->loadModel('UserItem');
        $this->loadModel('User');

        $user_id = $this->Auth->user('user_id');
        $this->User->id = $user_id;
        $this->User->saveField('locked', time());

        $server = $this->User->getCurrentServer($user_id);

        if ($server) {
            if (!$this->ServerUtility->unloadUserInventory($server, $user_id)) {
                $this->Session->setFlash('Oops! Our records show you are connected to a server but we are unable to contact it. You will not be able to send a gift until we can contact your server.', 'flash', array('class' => 'error'));
                $this->User->saveField('locked', 0);
                $this->redirect(array('action' => 'compose', 'id' => $steamid));
            };
        }

        $items = $this->loadItems();

        $this->UserItem->query('LOCK TABLES user_item WRITE, user_item as UserItem WRITE, activity as Activity WRITE, gift as Gift WRITE, gift_detail WRITE, gift_detail as GiftDetail WRITE');

        $userItems = Hash::combine(
            $this->UserItem->findAllByUserId($user_id),
            '{n}.UserItem.item_id', '{n}.UserItem'
        );

        foreach ($gift['GiftDetail'] as $detail) {

            $item_id = $detail['item_id'];
            $quantity = $detail['quantity'];

            if (empty($userItems[$item_id]) || $userItems[$item_id]['quantity'] < $quantity) {
                $this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in your inventory to package this gift.", 'flash', array('class' => 'error'));
                $this->Session->delete("gift-$steamid");
                $this->UserItem->query('UNLOCK TABLES');
                $this->User->saveField('locked', 0);
                $this->redirect(array('action' => 'compose', 'id' => $steamid));
                return;
            }

            $userItems[$item_id]['quantity'] -= $quantity;
        }

        //Commit
        $this->loadModel('Activity');

        $gift['Gift']['gift_id'] = $this->Activity->getNewId('Gift');
        $result = $this->Gift->saveAssociated($gift, array('atomic' => false));

        if (!$result['Gift'] || in_array(false, $result['GiftDetail'])) {
            $this->Session->setFlash('There was an error sending the gift. Please contact an administrator', 'flash', array('class' => 'error'));
            $this->UserItem->query('UNLOCK TABLES');
            $this->User->saveField('locked', 0);
            $this->redirect(array('action' => 'compose', 'id' => $steamid));
            return;
        }

        $this->UserItem->saveMany($userItems, array('atomic' => false));
        $this->UserItem->query('UNLOCK TABLES');
        $this->User->saveField('locked', 0);

        //Broadcast gift & refresh user's inventory
        if (!empty($server)) {
            $this->ServerUtility->broadcastGiftSend($server, $user_id, $gift);
        }

        $this->Session->delete("gift-$steamid");

        $this->Session->setFlash("Your gift has been sent! Gift number - #{$this->Gift->id}", 'flash', array('class' => 'success'));
        $this->redirect(array('action' => 'compose', 'id' => $steamid));
    }
}