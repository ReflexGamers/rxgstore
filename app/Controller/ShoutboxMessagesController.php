<?php
App::uses('AppController', 'Controller');

/**
 * ShoutboxMessages Controller
 *
 * @property ShoutboxMessage $ShoutboxMessage
 * @property PaginatorComponent $Paginator
 * @property RequestHandlerComponent $RequestHandler
 */
class ShoutboxMessagesController extends AppController {
    public $components = array('RequestHandler');
    public $helpers = array('Html', 'Form', 'Time');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
        $this->Auth->deny('add', 'delete');
    }

    /**
     * Checks to see if there are new shoutbox messages since $time then either re-renders the shoutbox content in the
     * response or sends back 304 Not Modified.
     *
     * @param int $time the last time the user got a new message
     */
    public function view($time = null) {

        if (!empty($time)) {
            $message = $this->ShoutboxMessage->find('first', array('fields' => array('date')));

            if (empty($message) || strtotime($message['ShoutboxMessage']['date']) <= $time) {
                $this->autoRender = false;
                $this->response->notModified();
                return;
            }
        }

        $this->loadShoutbox();
        $this->render('view.inc');
    }

    /**
     * Adds a new shoutbox message and renders the entire shoutbox content in the response. This happens when the user
     * submits their desired chat message. If the user recently posted a message, it will respond an inline error.
     * Configure the allowed time between messages in Store.Shoutbox.PostCooldown in rxgstore.php.
     */
    public function add() {

        $this->request->allowMethod('post');
        $user = $this->Auth->user();

        if (isset($user) && !empty($this->request->data['ShoutboxMessage'])) {

            if ($this->ShoutboxMessage->canUserPost($user['user_id'])) {

                $this->ShoutboxMessage->save(array(
                    'user_id' => $user['user_id'],
                    'content' => $this->request->data['ShoutboxMessage']['content']
                ));

            } else {

                $this->set('userCantPost', true);
                $this->response->statusCode(405);
            }
        }

        $this->view();
    }

    /**
     * Deletes a shoutbox message by id if the current user has sufficient permissions to do so.
     *
     * @param int $id the id of the message to delete.
     */
    public function delete($id) {

        $this->request->allowMethod('post');
        $this->autoRender = false;

        if (!$this->Access->check('Chats', 'delete')) {
            return;
        }

        $this->ShoutboxMessage->id = $id;
        $message = Hash::extract($this->ShoutboxMessage->read(), 'ShoutboxMessage');

        if (empty($message) || $message['removed'] == 1) {
            return;
        }

        $admin_steamid = $this->AccountUtility->SteamID64FromAccountID($this->Auth->user('user_id'));
        $poster_steamid = $this->AccountUtility->SteamID64FromAccountID($message['user_id']);

        $this->ShoutboxMessage->saveField('removed', 1);
        CakeLog::write('admin', "$admin_steamid removed chat message #$id '{$message['content']}' from $poster_steamid");
    }
}