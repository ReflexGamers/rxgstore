<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		https://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 *
 * @property AccessComponent $Access
 * @property AccountUtilityComponent $AccountUtility
 * @property AuthComponent $Auth
 * @property FlashComponent $Flash
 * @property SessionComponent $Session
 *
 * Magic Properties (for inspection):
 * @property CakeRequest $request
 * @property Item $Item
 * @property ShoutboxMessage $ShoutboxMessage
 * @property User $User
 *
 * Magic Methods (for inspection):
 * @method loadModel(string $modelName)
 * @method set(array $data)
 * @method redirect(array $route)
 */
class AppController extends Controller {
    public $viewClass = 'TwigView.Twig';
    public $ext = '.tpl';
    public $layout = '';
    public $components = array(
        'Session',
        'Flash',
        'Cookie',
        'AccountUtility',
        'Acl',
        'Access',
        'Auth' => array(
            'loginAction' => array(
                'controller' => 'Users',
                'action' => 'login'
            ),
            'logoutRedirect' => array(
                'controller' => 'Items',
                'action' => 'index'
            )
        )
    );
    public $helpers = array(
        'Flash',
        'ItemFuncs'
    );

    protected $players = array();
    protected $items = null;
    protected $divisions = null;

    /**
     * Adds a single player or list of players to the player buffer. The beforeRender() method will pull all players out
     * of the buffer and fetch their Steam data to pass to the view. This prevents multiple calls to the Steam API or
     * multiple queries to the Steam cache since all the data will be fetched at once as late as possible to ensure all
     * the needed players have been added to the buffer.
     *
     * Make sure to only pass signed 32-bit SteamIDs. If you have SteamIDs in another format, convert them first.
     *
     * @param array $users list of account ids (aka user_id)
     * @param string [$extractPath=''] optional path uses to extract ids from a nested array (2nd param of Hash::extract())
     */
    public function addPlayers($users, $extractPath = '') {

        if (!empty($extractPath)) {
            $users = Hash::extract($users, $extractPath);
        } else if (!is_array($users)) {
            // add single player
            $this->players[] = $users;
            return;
        }

        $this->players = array_merge($this->players, $users);
    }

    /**
     * Loads division data from the config, passes it to the view and also returns the result to
     * the controller that called this method.
     *
     * @return array the division data
     */
    public function loadDivisions() {
        if (empty($this->divisions)) {
            $this->divisions = Hash::combine(Configure::read('Store.Divisions'), '{n}.division_id', '{n}');
            $this->set('divisions', $this->divisions);
        }
        return $this->divisions;
    }

    /**
     * Loads basic Item Data for all items, passes it to the view and also returns the result to the controller that
     * called this method. Should be used in most instances where Item Data is needed unless additional data is needed.
     *
     * Once called, the items are cached in the $items property in case they are called later in the same request which
     * can happen when controller actions call each other.
     *
     * @return array the item data
     */
    public function loadItems() {

        if (empty($this->items)) {

            $this->loadModel('Item');
            $sortedItems = $this->Item->getAllSorted();
            $items = Hash::combine($sortedItems, '{n}.item_id', '{n}');

            // add cash at the beginning as item_id 0
            array_unshift($sortedItems, array(
                'item_id' => 0,
                'short_name' => 'cash',
                'name' => 'RXG Cash',
                'price' => 1
            ));

            $this->set(array(
                'sortedItems' => $sortedItems,
                'items' => $items
            ));

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * Loads constants relating to stacks of CASH which are used anywhere CASH stacks may be visible such as activity
     * feeds on the home page, user pages and the PayPal page. Call this from a controller action if you need to display
     * stack of CASH.
     */
    public function loadCashData() {
        $this->set(array(
            'currencyMult' => Configure::read('Store.CurrencyMultiplier'),
            'cashStackSize' => Configure::read('Store.CashStackSize')
        ));
    }

    /**
     * Loads Shoutbox data and passes it to the view. Call this from any controller action that includes the shoutbox
     * partial view which expects this data.
     */
    public function loadShoutbox() {

        $shoutConfig = Configure::read('Store.Shoutbox');

        if (!$shoutConfig['Enabled']) {
            return;
        }

        $this->loadModel('ShoutboxMessage');
        $messages = $this->ShoutboxMessage->getRecent();
        $this->addPlayers($messages, '{n}.user_id');

        $this->set(array(
            'showShoutbox' => true,
            'messages' => $messages,
            'theTime' => time(),
            'shoutPostCooldown' => $shoutConfig['PostCooldown'],
            'shoutUpdateInterval' => $shoutConfig['UpdateInterval']
        ));
    }

    /**
     * Runs before controller-specific actions.
     *
     * 1) Logs the user in if they are not logged in but have a saved login that has not expired yet.
     * 2) Updates the user's saved login record and cookie.
     * 3) Updates the user's 'last_activity' field to the current time.
     * 4) Sets a 'user' variable for the current user.
     * 5) Sets an 'access' variable that references the AccessComponent for checking permissions in the view.
     */
    public function beforeFilter() {

        // allow all visitors by default. override this with deny in child controllers to deny unauthenticated users
        $this->Auth->allow();

        $user = $this->Auth->user();
        $loggedIn = !empty($user['user_id']);
        $savedLoginFound = $this->AccountUtility->trySavedLogin($loggedIn);

        if ($savedLoginFound && !$loggedIn) {

            // fetch user again after logging in
            $user = $this->Auth->user();
            $loggedIn = true;

            // access component init already occurred, so user must be set manually
            $this->Access->setUser($user);
        }

        if ($loggedIn) {
            $this->loadModel('User');
            $this->User->id = $user['user_id'];
            $this->User->saveField('last_activity', time());
        }

        $this->set(array(
            'user' => $user,
            'access' => $this->Access
        ));
    }

    /**
     * Called after controller-specific methods are complete but before rendering the view.
     *
     * 1) Checks for the existence of a cart and passed the corresponding data to the view.
     * 2) Checks whether the current request was called by ajax and sets the isAjax variable.
     * 3) Fetches player data for all players in $this->players and passes it to the view.
     */
    public function beforeRender() {

        $cart = $this->Session->read('cart');
        if (!empty($cart)) {
            $cartItems = 0;
            foreach ($cart as $item) {
                $cartItems += $item['quantity'];
            }
            $this->set('cartItems', $cartItems);
        }

        $this->set(array(
            'isAjax' => $this->request->is('ajax'),
            'players' => $this->AccountUtility->getIndexedSteamInfo($this->players)
        ));
    }
}
