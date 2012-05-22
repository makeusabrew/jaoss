<?php

class ProfileDb extends Db {
    protected static $queryCount = 0;

    public function prepare($statement, $options = array()) {
        self::$queryCount ++;

        Log::db("Preparing query [".$statement."]");
        return parent::prepare($statement, $options);
    }

    public static function getQueryCount() {
        return self::$queryCount;
    }
}
