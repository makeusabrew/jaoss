<?php
class JaossPath {
    protected $pattern;
    protected $location;
    protected $app;
    protected $controller;
    protected $action;
    protected $matches = array();
    protected $discarded;
    protected $cacheable = false;
    protected $cacheTtl = null;

    public function run($request = NULL) {
        $controller = Controller::factory($this->controller, $this->app, $request);
        if (method_exists($controller, $this->action)) {
            if (is_callable(array($controller, $this->action))) {
                $controller->setPath($this);

                try {
                    Log::debug("Init [".$this->controller."Controller->".$this->action."]");
                    $controller->init();
                } catch (CoreException $e) {
                    Log::debug($this->controller."Controller->init() failed with message [".$e->getMessage()."]");
                    if ($e->getCode() == CoreException::PATH_REJECTED) {
                        throw $e;
                    }
                    return $controller->getResponse();
                }

                Log::debug("Start [".$this->controller."Controller->".$this->action."]");
                $result = call_user_func(array($controller, $this->action));
                if ($result === NULL) {
                    $controller->render($this->action);
                }
                Log::debug("End   [".$this->controller."Controller->".$this->action."] - status code [".$controller->getResponse()->getResponseCode()."]");
                return $controller->getResponse();
            } else {
                throw new CoreException("Controller action is not callable");
            }
        } else {
            throw new CoreException(
                "Controller action '".$this->action."' does not exist",
                CoreException::ACTION_NOT_FOUND,
                array(
                    "controller" => get_class($controller),
                    "action" => $this->action,
                    "path" => $this->location."/controllers",
                )
            );
        }
    }

    public function setPattern($pattern) {
        $this->pattern = $pattern;
    }

    public function setLocation($location) { 
        $this->location = $location;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setController($controller) {
        $this->controller = $controller;
    }

    public function setApp($app) {
        $this->app = $app;
    }

    public function getPattern() {
        return $this->pattern;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getAction() {
        return $this->action;
    }

    public function getController() {
        return $this->controller;
    }

    public function getApp() {
        return $this->app;
    }

    public function setMatches($matches) {
        $this->matches = $matches;
    }

    public function hasMatch($match) {
        return isset($this->matches[$match]);
    }

    public function getMatch($match) {
        return isset($this->matches[$match]) ? $this->matches[$match] : null;
    }
    
    public function setDiscarded($discarded) {
        $this->discarded = $discarded;
    }

    public function setCacheable($cacheable) {
        $this->cacheable = $cacheable;
    }

    public function setCacheTtl($ttl) {
        $this->cacheTtl = $ttl;
    }

    public function isCacheable() {
        return $this->cacheable;
    }

    public function getCacheTtl() {
        return $this->cacheTtl;
    }
    
    public function isDiscarded() {
        return $this->discarded;
    }
}
