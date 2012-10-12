<?php
class RejectedException extends CoreException {
    public function __construct($msg) {
        return parent::__construct($msg, CoreException::PATH_REJECTED);
    }
}
