<?php
/**
 * heavily inspired & adapted with thanks from
 * https://github.com/TheDeveloper/redis-session-php
 */
class RedisSessionStorage {
    private $redis;
    private $prefix;

    public static function start($redis_conf = array(), $namespace) {
        $obj = new self($redis_conf, $namespace);

        session_set_save_handler(
            array($obj, "open"),
            array($obj, "close"),
            array($obj, "read"),
            array($obj, "write"),
            array($obj, "destroy"),
            array($obj, "gc")
        );

        session_start(); // Because we start the session here, any other modifications to the session must be done before this class is started
        return $obj;
    }

    public function __construct($redis_conf, $namespace){
        $this->redis = new \Predis\Client($redis_conf); 
        $this->prefix = $namespace.":";
    }

    public function read($id) {
        $d = json_decode(
            $this->redis->get($this->prefix . $id),
            true
        );

        // Revive $_SESSION from our array
        $_SESSION = $d;
    }

    public function write($id, $data) {
        $data = $_SESSION;
        $ttl  = ini_get("session.gc_maxlifetime");

        $this->redis->setex($this->prefix . $id, $ttl, json_encode($data));
    }

    public function destroy($id) {
        $this->redis->del($this->prefix . $id);
    }

    // These functions are all noops for various reasons... opening has no practical meaning in
    // terms of non-shared Redis connections, the same for closing. Garbage collection is handled by
    // Redis anyway.
    public function open($path, $name) {}
    public function close() {}
    public function gc($age) {}
}

// the following prevents unexpected effects when using objects as save handlers
register_shutdown_function('session_write_close');
