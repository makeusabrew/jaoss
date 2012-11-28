<?php

abstract class AbstractMigration {

    protected $queries = array();
    protected $hasSet = false;

    abstract public function getTitle();
    abstract public function getDescription();

    abstract protected function setQueries();

    public final function getQueries() {
        if (!$this->hasSet) {
            $this->setQueries();
            $this->hasSet = true;
        }

        return $this->queries;
    }

    protected function query($sql, $params = array()) {
        $this->queries[] = array(
            "sql"    => $sql,
            "params" => $params,
        );
    }
}
