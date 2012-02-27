<?php

class CookieJar {
    protected static $instance = null;
    protected $storage = null;

    public static function getInstance($namespace = NULL) {
        if (self::$instance === NULL) {
            Log::verbose("Instantiating Cookie Jar");
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // not implementing custom settings yet, don't need to
        $this->storage = CookieJarStorage::factory('autodetect');
        if ($this->storage == null) {
            throw new CoreException(
                "Could not attach cookie jar storage",
                CoreException::COULD_NOT_ATTACH_COOKIE_JAR,
                array(
                    "class" => "DefaultCookieJarStorage",
                )
            );
        }
        $this->storage->init();
    }

    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        $this->storage->setCookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function getCookie($name) {
        return $this->storage->getCookie($name);
    }

    public function destroy() {
        $this->storage->destroy();
    }
}

abstract class CookieJarStorage {
    protected $namsepace = null;

    abstract public function init();
    abstract public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null);
    abstract public function getCookie($name);
    abstract public function destroy();

    public static function factory($mode) {
        if ($mode == "autodetect") {
            if (php_sapi_name() == "cli") {
                $mode = "test";
            } else {
                $mode = "default";
            }
        }
        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."CookieJarStorage")) {
            $class = $prefix."CookieJarStorage";
            return new $class;
        }
        return null;
    }
}

class DefaultCookieJarStorage extends CookieJarStorage {
    public function init() {
    }

    public function destroy() {
        //
    }

    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function getCookie($var) {
        if (isset($_COOKIE[$var])) {
            return $_COOKIE[$var];
        }
        return null;
    }
}

class TestCookieJarStorage extends CookieJarStorage {
    private $cookieJar = null;

    public function init() {
        $this->cookieJar = array();
    }

    public function destroy() {
        $this->init();
    }

    public function setCookie($key, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        $this->cookieJar[$key] = $value;
    }

    public function getCookie($key) {
        return isset($this->cookieJar[$key]) ? $this->cookieJar[$key] : null;
    }
}
