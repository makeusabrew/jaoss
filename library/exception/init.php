<?php
class InitException extends Exception {
    protected $response;

    public function __construct($response, $message = 'Init Exception', $code = 0) {
        parent::__construct($message, $code);

        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }
}
