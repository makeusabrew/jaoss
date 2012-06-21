<?php
// I'd much rather use a mock here but for some reason code coverage doesn't
// seem to work properly. Look at fixing this later.
class ConcreteController extends Controller {
    public function __construct($properties = array(), $params = array()) {
        // don't want abstract controller construct firing cos it throws an exception
        // could look at moving stuff out of construct?
        $this->request = new TestRequest();

        $this->setRequestProperties($properties);
        $this->request->setParams($params);

        $this->response = new JaossResponse();
        $this->session = Session::getInstance();
    }

    public function getAssignVar($key) {
        return $this->var_stack[$key];
    }

    public function getVarStack() {
        return $this->var_stack;
    }

    public function setRequestProperties($properties) {
        $this->request->setProperties($properties);
    }

    public function addHeader($key, $val) {
        return $this->response->addHeader($key, $val);
    }
}
