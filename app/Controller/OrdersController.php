<?php
App::uses('AppController', 'Controller');
/**
 * Orders Controller
 *
 * @property Order $Order
 * @property ServerUtilityComponent $ServerUtility
 */
class OrdersController extends AppController {
    public $components = array('Paginator', 'RequestHandler', 'ServerUtility');
    public $helpers = array('Html', 'Form', 'Session', 'Js', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
        $this->Auth->deny('receipt', 'checkout', 'buy');
    }

    /**
     * Shows a receipt for a past order.
     *
     * @param int $order_id
     */
    public function receipt($order_id) {

        $this->loadModel('Item');
        $this->loadModel('Order');

        $order = $this->Order->find('first', array(
            'conditions' => array(
                'order_id' => $order_id,
            ),
            'contain' => array(
                'OrderDetail' => array(
                    'fields' => array('item_id', 'quantity', 'price')
                )
            )
        ));

        $user_id = $order['Order']['user_id'];

        if ($user_id != $this->Auth->user('user_id')) {
            $this->Session->setFlash('You do not have permission to view this receipt.', 'default', array('class' => 'error'));
            return;
        }

        $steamid = $this->AccountUtility->SteamID64FromAccountID($user_id);

        $this->loadItems();

        $this->set(array(
            'data' => $order,
            'steamid' => $steamid
        ));
    }

    /**
     * TODO: Remove this since it is being replaced the cart checkout method action
     */
    public function checkout()  {

        $this->request->allowMethod('post');

        if (empty($this->request->data['OrderDetail'])) {
            $this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
            return;
        }

        $orderDetails = $this->request->data['OrderDetail'];

        $user_id = $this->Auth->user('user_id');

        //Get cart for original prices
        $cart = $this->Session->read('cart');

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
                //Update cart quantity in case they go back
                $cart[$item_id]['quantity'] = $quantity;
            } else {
                //Add cart entry if not there (unlikely)
                $cart[$item_id] = array(
                    'quantity' => $quantity,
                    'price' => $items[$item_id]['price']
                );
            }

            $detail['price'] = $cart[$item_id]['price'];

            if ($stock[$item_id] < $quantity) {
                $this->Session->setFlash("There are no longer enough {$items[$item_id]['plural']} in stock to complete your purchase.", 'default', array('class' => 'error'));
                return;
            }

            $subTotal += $detail['price'] * $quantity;
        }

        if (empty($orderDetails)) {
            $this->Session->setFlash('Oops! You do not have any items in your shopping cart.', 'default', array('class' => 'error'));
            $this->redirect(array('controller' => 'Cart', 'action' => 'view'));
            return;
        }

        //Save cart quantities in case they return
        $this->Session->write('cart', $cart);

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
    }

    /**
     * Completes a purchase and shows a receipt. The order data should be set in the session by a checkout process
     * before this is called.
     *
     * @broadcast order contents
     */
    public function buy() {

        $this->request->allowMethod('post');

        $order = $this->Session->read('order');

        if (empty($order)) {
            $this->Session->setFlash('Oops! Your cart appears to be empty.', 'default', array('class' => 'error'));
            return;
        }

        $user_id = $this->Auth->user('user_id');

        $this->loadModel('Item');
        $this->loadModel('Stock');
        $this->loadModel('User');
        $this->loadModel('UserItem');

        $items = $this->loadItems();

        $this->Stock->query('LOCK TABLES stock WRITE, user WRITE, user_item WRITE, user_item as UserItem WRITE');

        $stock = Hash::combine(
            $this->Stock->find('all'),
            '{n}.Stock.item_id', '{n}.Stock'
        );

        $userItem = Hash::combine(
            $this->UserItem->findAllByUserId($user_id),
            '{n}.UserItem.item_id', '{n}.UserItem'
        );

        $total = $order['total'];
        $order = $order['models'];

        foreach ($order['OrderDetail'] as $item) {

            $item_id = $item['item_id'];
            $quantity = $item['quantity'];

            if ($stock[$item_id]['quantity'] < $quantity) {

                $this->Session->setFlash("Oops! There are no longer sufficient {$items[$item_id]['plural']} in stock to complete your purchase.", 'default', array('class' => 'error'));
                $this->Session->delete('order');

                $this->Stock->query('UNLOCK TABLES');

                return;
            }

            if (isset($userItem[$item_id])) {
                $userItem[$item_id]['quantity'] += $quantity;
            } else {
                $userItem[] = array(
                    'user_id' => $user_id,
                    'item_id' => $item_id,
                    'quantity' => $quantity
                );
            }

            $stock[$item_id]['quantity'] -= $quantity;
        }

        $credit = $this->User->read('credit', $user_id)['User']['credit'];

        if ($total > $credit) {
            $this->Session->setFlash('You no longer have sufficient CASH to complete this purchase!', 'default', array('class' => 'error'));
            return;
        }

        //Commit
        $this->loadModel('Activity');

        $this->Stock->saveMany($stock, array('atomic' => false));
        $this->UserItem->saveMany($userItem, array('atomic' => false));
        $this->User->saveField('credit', $credit - $total);
        $this->Stock->query('UNLOCK TABLES');

        $order['Order']['order_id'] = $this->Activity->getNewId('Order');

        $this->Order->saveAssociated($order, array('atomic' => false));
        $this->Session->delete('order');
        $this->Session->delete('cart');

        //Broadcast to server if player is in-game
        $server = $this->User->getCurrentServer($user_id);

        if (!empty($server)) {
            $this->ServerUtility->broadcastPurchase($server, $user_id, $order);
        }

        $this->set('order', $order);
        $this->set('steamid', $this->Auth->user('steamid'));

        $this->Session->setFlash('Your purchase is complete. Here is your receipt.', 'default', array('class' => 'success'));
    }
}
