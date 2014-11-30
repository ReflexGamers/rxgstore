<?php
App::uses('AppController', 'Controller');

/**
 * Class CartController
 *
 * Handles cart actions such as viewing the cart, adding or removing items, etc.
 *
 * Magic Properties (for inspection):
 * @property Stock $Stock
 */
class CartController extends AppController {
    public $components = array('RequestHandler');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Shows the cart view page.
     */
    public function view() {

        $this->loadModel('Item');
        $this->loadModel('Stock');
        $this->loadModel('User');

        $cart = $this->Session->read('cart');
        $this->User->id = $this->Auth->user('user_id');
        $stock = $this->Stock->find('list');

        $this->loadItems();

        $config = Configure::Read('Store.Shipping');

        $this->set(array(
            'cart' => $cart,
            'stock' => $stock,
            'credit' => $this->User->field('credit'),
            'shippingCost' => $config['Cost'],
            'shippingFreeThreshold' => $config['FreeThreshold']
        ));
    }

    /**
     * Processes actions for the entire cart such as 'empty', 'update' and 'checkout' depending on what 'ProcessAction'
     * is set to in the request data.
     *
     * Empty: empties the cart completely and sends to index
     * Update: saves new quantities of items in the cart
     * Checkout: compiles cart items into a session object called 'order' and shows the confirmation page
     */
    public function process() {

        $this->request->allowMethod('post');

        if (empty($this->request->data['ProcessAction'])) {
            $this->Session->setFlash('Oops! There was an error processing your cart.', 'default', array('class' => 'error'));
            $this->redirect(array('controller' => 'Cart', 'action' => 'view'));
            return;
        }

        $processAction = $this->request->data['ProcessAction'];

        if ($processAction == 'empty') {
            $this->Session->delete('cart');
            $this->Session->setFlash('Your cart has been emptied.', 'default', array('class' => 'success'));
            $this->redirect(array('controller' => 'Items', 'action' => 'index'));
            return;
        }

        if (empty($this->request->data['OrderDetail'])) {
            $this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
            $this->redirect(array('controller' => 'Cart', 'action' => 'view'));
            return;
        }

        $orderDetails = $this->request->data['OrderDetail'];
        $cart = $this->Session->read('cart');
        $user_id = $this->Auth->user('user_id');

        $this->loadModel('Stock');
        $this->loadModel('Item');
        $this->loadModel('User');

        $stock = $this->Stock->find('list');
        $items = $this->loadItems();
        $userCredit = $this->User->read('credit', $user_id)['User']['credit'];

        $subTotal = 0;

        foreach ($orderDetails as $key => &$detail) {

            $item_id = $detail['item_id'];
            $quantity = $detail['quantity'];

            if ($quantity < 1) {
                unset($orderDetails[$key]);
                unset($cart[$item_id]);
                continue;
            }

            if (isset($cart[$item_id])) {
                $cart[$item_id]['quantity'] = $quantity;
            } else {
                $cart[$item_id] = array(
                    'quantity' => $quantity,
                    'price' => $items[$item_id]['price']
                );
            }

            $detail['price'] = $cart[$item_id]['price'];

            if ($stock[$item_id] < $quantity) {
                if ($processAction == 'checkout') {
                    $this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in stock to complete your purchase.", 'default', array('class' => 'error'));
                    $this->render('checkout');
                    return;
                } else {
                    //place max items available in cart
                    $cart[$item_id] = $stock[$item_id];
                }
            }

            if ($processAction == 'checkout') {
                $subTotal += $detail['price'] * $quantity;
            }
        }

        if ($processAction == 'checkoout' && empty($orderDetails)) {
            $this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
            $this->redirect(array('controller' => 'Cart', 'action' => 'view'));
            return;
        }

        $this->Session->write('cart', $cart);

        if ($processAction == 'update') {
            $this->Session->setFlash('Your cart has been updated.', 'default', array('class' => 'success'));
            $this->redirect(array('controller' => 'Cart', 'action' => 'view'));
            return;
        }

        //Checkout stuff
        $config = Configure::Read('Store.Shipping');

        $shipping = $subTotal >= $config['FreeThreshold'] ? 0 : $config['Cost'];
        $total = $subTotal + $shipping;

        if ($total > $userCredit) {
            $this->Session->setFlash('You do not have enough CASH to complete this purchase!', 'default', array('class' => 'error'));
            return;
        }

        $this->Session->write('order', array(
            'total' => $total,
            'subtotal' => $subTotal,
            'models' =>  array(
                'Order' => array(
                    'user_id' => $user_id,
                    'shipping' => $shipping
                ),
                'OrderDetail' => $orderDetails
            )
        ));

        $this->set(array(
            'cart' => $orderDetails,
            'subTotal' => $subTotal,
            'shipping' => $shipping,
            'total' => $total,
            'credit' => $userCredit
        ));

        $this->render('checkout');
    }

    /**
     * Adds an item to the cart. The quantity added is based on the content of the request.
     *
     * @param int $item_id the item to add
     */
    public function add($item_id = null) {

        $this->request->allowMethod('post');
        $this->autoRender = false;

        $this->loadModel('Item');
        $item = $this->Item->read(array('name', 'plural', 'buyable', 'price'), $item_id);

        if (empty($item)) {
            throw new NotFoundException(__('Invalid item'));
        }

        $item = $item['Item'];

        if ($item['buyable']) {

            $quantity = $this->request->data['Cart']['quantity'];
            $cart = $this->Session->read('cart');

            if (isset($cart[$item_id])) {
                $cart[$item_id]['quantity'] += $quantity;
            } else {
                $cart[$item_id] = array(
                    'quantity' => $quantity,
                    'price' => $item['price']
                );
            }

            $this->Session->write('cart', $cart);

            $name = $quantity > 1 ? $item['plural'] : $item['name'];
            $this->Session->setFlash("$quantity $name added to cart!", 'flash_cart', array('class' => 'success'));

        } else {

            $this->Session->setFlash('Oops! That item is no longer for sale.', 'default', array('class' => 'error'));
        }

        if ($this->request->is('ajax')) {
            $this->render('/Common/flash');
        } else {
            $this->redirect($this->referer());
        }
    }

    /**
     * Updates the cart link in the navigation bar that shows the current number of items in the cart.
     */
    public function link() {
        $this->render('link.inc');
    }

    /**
     * Removes all of a specific item from the cart.
     *
     * @param int $item_id the item to remove
     */
    public function remove($item_id = null) {

        $this->request->allowMethod('post');
        $this->autoRender = false;

        $this->loadModel('Item');

        if (!$this->Item->exists($item_id)) {
            throw new NotFoundException(__('Invalid item'));
        }

        $cart = $this->Session->read('cart');

        if (isset($cart[$item_id])) {
            unset($cart[$item_id]);
        }

        $this->Session->write('cart', $cart);
    }
}