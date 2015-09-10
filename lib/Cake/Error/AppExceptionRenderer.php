<?php
App::uses('ExceptionRenderer', 'Error');

class AppExceptionRenderer extends ExceptionRenderer {

    public function notFound($error) {
        $this->controller->helpers = array('Html', 'Form', 'Js');
        $this->controller->set('error', '404 Not Found');
        $this->controller->render('/Errors/notfound');
        $this->controller->response->send();
    }

    public function missingController($error) {
        $this->notFound($error);
    }

    public function missingAction($error) {
        $this->notFound($error);
    }
}