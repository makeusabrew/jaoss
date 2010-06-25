<?php

abstract class Object {
	protected $id = NULL;
	protected $created = NULL;
	protected $updated = NULL;
    protected $table_name = NULL;
	
	protected $values = array();
	protected $externals = array();

    private $pk = "id";
	
	public function __set($var, $val) {
		if (property_exists($this, $var)) {
			$this->$var = $val;
		} else {
			$this->values[$var] = $val;
		}
	}

	public function __get($var) {
		if (isset($this->$var)) {
			return $this->$var;
		} else if (isset($this->values[$var])) {
			return $this->values[$var];
		} else if (isset($this->externals[$var])) {
			return $this->externals[$var];
		} else if (isset($this->values[$var."_id"])) {
			$col = $this->getColumnInfo($var."_id");
			$this->externals[$var] = Table::factory($col["table"])->read($this->values[$var."_id"]);
			return $this->externals[$var];
		}
		return null;
	}
	
	public function setValues($values) {
		$this->values = $values;
	}
	
	public function getTableName() {
		if (!isset($this->table_name)) {
			$this->table_name = get_class($this)."s";
		}
		return $this->table_name;
	}
	
	public function getColumnInfo($column) {
		$table = Table::factory($this->getTableName());
		return $table->getColumnInfo($column);
	}

    public function getId() {
        $pk = $this->pk;
        return $this->$pk;
    }
}
