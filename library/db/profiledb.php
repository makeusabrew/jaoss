<?php

class ProfileDb extends PDO {
    protected static $queries = array();

    public function prepare($statement, $options = array()) {
        $sth = new ProfileStatement(parent::prepare($statement, $options));
        return $sth;
    }

    public static function addQuery($query, $duration) {
        self::$queries[] = array(
            "query"  => $query,
            "exTime" => $duration,
        );
    }

    public static function getQueryCount() {
        return count(self::$queries);
    }

    public static function getTotalQueryTimeMs($roundTo = 2) {
        $time = 0;
        foreach (self::$queries as $query) {
            $time += $query['exTime'];
        }
        return round($time*1000, $roundTo);
    }

    public static function getQueries() {
        return self::$queries;
    }
}

class ProfileStatement {
    protected $pdoStatement;

    public function __construct(PDOStatement $statement) {
        $this->pdoStatement = $statement;
    }

    public function execute($input_parameters) {
        // time the actual method call
        $start = microtime(true);
        $result = $this->pdoStatement->execute($input_parameters);
        $duration = microtime(true) - $start;

        $query = $this->pdoStatement->queryString;

        if (is_array($input_parameters)) {
            // substitute as best we can bound params for actual variables
            foreach ($input_parameters as $key => $param) {
                if (is_string($key)) {
                    $keys[] = "/:".$key."/";
                } else {
                    $keys[] = "/[?]/";
                }
            }
            $query = preg_replace($keys, $input_parameters, $query, 1);
        }

        $qc = ProfileDb::getQueryCount() + 1;
        Log::db($qc.") Execute [".$query."] => ".round($duration*1000, 3)."ms");

        ProfileDb::addQuery($query, $duration);
        return $result;
    }

    /**
     * simple pass through proxy for all statement methods other than execute
     */
    public function __call($method, $args) {
        return call_user_func_array(array($this->pdoStatement, $method), $args);
    }

}
