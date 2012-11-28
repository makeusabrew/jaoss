<?php
class JaossPath {
    protected $pattern;
    protected $app;
    protected $controller;
    protected $action;
    protected $name;
    protected $matches = array();
    protected $discarded;
    protected $cacheable = false;
    protected $cacheTtl = null;
    protected $requestMethods = array();

    public function run($request = NULL) {
        $controller = Controller::factory($this->controller, $this->app, $request);
        if (method_exists($controller, $this->action)) {
            if (is_callable(array($controller, $this->action))) {
                $controller->setPath($this);

                try {
                    Log::debug("Init   [".$this->controller."Controller->".$this->action."]");
                    $controller->init();
                } catch (InitException $e) {
                    Log::debug($this->controller."Controller->init() failed with message [".$e->getMessage()."]");
                    return $e->getResponse();
                }

                Log::debug("Start  [".$this->controller."Controller->".$this->action."]");
                $response = call_user_func(array($controller, $this->action));

                if ($response === null) {

                    $response = $controller->getResponse();

                    if (!$response->isInitialised()) {
                        $response = $controller->render($this->action);
                    }
                }
                Log::debug("End    [".$this->controller."Controller->".$this->action."] - status code [".$response->getResponseCode()."]");
                return $response;
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
                    "path" => "apps/".$this->app."/controllers",
                )
            );
        }
    }

    public function setPattern($pattern) {
        $this->pattern = $pattern;
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

    public function setName($name) {
        $this->name = $name;
    }

    public function getPattern() {
        return $this->pattern;
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

    public function getName() {
        return $this->name;
    }

    public function setMatches($matches) {
        $this->matches = $matches;
    }

    public function getMatches() {
        return $this->matches;
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

    public function setRequestMethods($methods = array()) {
        foreach ($methods as $key => $val) {
            $methods[$key] = strtoupper($val);
        }
        $this->requestMethods = $methods;
    }

    public function getRequestMethods() {
        return $this->requestMethods;
    }

    public function supportsMethod($method) {
        // if we've got nothing defined at all, then it's all good
        if (count($this->getRequestMethods()) == 0) {
            return true;
        }

        // if we've got an 'ALL' entry in the array anywhere, it's all good
        if (in_array("ALL", $this->getRequestMethods())) {
            return true;
        }

        // otherwise, actually check the method against our array 
        return in_array($method, $this->getRequestMethods());
    }

    public function toArray() {
        return array(
            "pattern"        => $this->pattern,
            "app"            => $this->app,
            "controller"     => $this->controller,
            "action"         => $this->action,
            "name"           => $this->name,
            "cacheTtl"       => $this->cacheTtl,
            "requestMethods" => $this->requestMethods,
        );
    }
}
