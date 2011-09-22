<?php

abstract class Object {
	protected $id = NULL;
	protected $created = NULL;
	protected $updated = NULL;
    protected $table = NULL;
    protected $table_name = NULL;
	
	protected $values = array();
	protected $externals = array();
    protected $errors = array();

    private $pk = "id";
	
	public function __set($var, $val) {
		if (property_exists($this, $var)) {
			$this->$var = $val;
		} else {
			$this->values[$var] = $val;
		}
	}
	
	public function getTable() {
		if (!isset($this->table)) {
			$table = Utils::fromCamelCase(get_class($this));
			$this->table = "{$table}s";
		}
		return $this->table;
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
		} else if (substr($var, -1) == "s" && ($table = $this->getHasManyInfo($var))) {
			// one -> many
			$foreign_id = $this->getFkName();
			$this->externals[$var] = Table::factory($table)->findAll("`{$foreign_id}` = ?", array($this->getId()));
			return $this->externals[$var];
		}
		return null;
	}
	
	public function getValues() {
		return array_merge(array($this->pk => $this->getId()), $this->values);
	}

    /**
     * override this if you want more specific JSON behaviour
     */
    public function toJson() {
        return $this->getValues();
    }
	
	public function setValues($values, $subset = null) {
        $this->errors = array();

        $columns = $this->getColumns();
        foreach($columns as $field => $settings) {
            if ($subset !== null && !in_array($field, $subset)) {
                continue;
            }
            $value = isset($values[$field]) ? $values[$field] : null;
            if (!isset($settings["title"])) {
                $settings["title"] = $field;
            }
            $result = $this->validate($field, $value, $settings);
            if ($result !== true) {
                $this->errors[$field] = $result;
            } else {
                if (isset($settings["confirm"])) {
                    $confirm = isset($values["confirm_{$field}"]) ? $values["confirm_{$field}"] : "";
                    $result = Validate::match($value, array("confirm" => $confirm));
                    if ($result !== true) {
                        $this->errors["confirm_{$field}"] = Validate::getMessage("match", $settings);
                    }
                }
            }
            // always set, regardless of validation problems etc
            $this->values[$field] = $this->process($field, $value, $settings["type"]);

        }
        return (count($this->errors) == 0) ? true : false;
	}
	
	public function updateValues($values, $partial = false) {
        if ($partial === true) {
            return $this->setValues($values, array_keys($values));
        } else {
            return $this->setValues(array_merge($this->getValues(), $values));
        }
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
	
	public function getHasManyInfo($column) {
		$table = Table::factory($this->getTableName());
		return $table->getHasManyInfo($column);
	}

    public function getId() {
        $pk = $this->pk;
        return $this->$pk;
    }

    public function getUrl() {
        return $this->getId();
    }
    
    public function getFkName() {
    	return strtolower(get_class($this))."_id";
    }
    
    public function getColumns() {
    	return Table::factory($this->getTableName())->getColumns();
    }
    
    public function save() {
    	$sql = "";
    	$values = array();
    	if ($this->getId()) {
    		// unset PK, just in case
    		unset($this->values[$this->pk]);
    		$sql = "UPDATE `".$this->getTable()."` SET `updated` = NOW(),";
    		foreach ($this->getColumns() as $key => $val) {
    			if (isset($this->values[$key])) {
	    			$sql .= "`{$key}` = ?,";
	    			$values[] = $this->values[$key];
	    		}
    		}
    		$sql = substr($sql, 0, -1);
    		$sql .= " WHERE `{$this->pk}` = ?";
    		$values[] = $this->getId();
    	} else {
    		$sql = "INSERT INTO `".$this->getTable()."` (`created`, `updated`,";
    		$params = "";
    		foreach ($this->getColumns() as $key => $val) {
    			if (isset($this->values[$key])) {
    				$sql .= "`{$key}`,";
    				$params .= "?,";
    				$values[] = $this->values[$key];
    			}
    		}
    		$sql = substr($sql, 0, -1);
    		$params = substr($params, 0, -1);
    		$sql .= ") VALUES (NOW(),NOW(),".$params.")";
    	}

   		$dbh = Db::getInstance();
		$sth = $dbh->prepare($sql);
        $sth->execute($values);
		$id = $dbh->lastInsertId();
		if (!$this->getId()) {
			$pk = $this->pk;
			$this->$pk = $id;
		}
		return TRUE;
    }

    public function delete() {
        foreach ($this->getChildren() as $child) {
            $child->delete();
        }
        if (!$this->getId()) {
            Log::debug("trying to delete unsaved object");
            return false;
        }
        $sql = "DELETE FROM `".$this->getTable()."` WHERE `".$this->pk."` = ?";
        $dbh = Db::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute(array($this->getId()));
        return true;
    }
        
    
    public function owns($object) {
    	if (!is_object($object)) {
    		return false;
    	}
    	$fk = $this->getFkName();
    	return $object->$fk == $this->getId();
    }

    public function getErrors() {
        return $this->errors;
    }

    protected function validate($field, $value, $settings) {
        $validation = array();
        if (isset($settings["required"]) && $settings["required"]) {
            $validation[] = "required";
        }

        if ($settings["type"] == "email") {
            $validation[] = "email";
        }

        if ($settings["type"] == "date") {
            $validation[] = "date";
        }

        if ($settings["type"] == "select" && isset($settings["options"]) && is_array($settings["options"])) {
            $validation[] = "matchOption";
        }

        if (isset($settings["validation"])) {
            if (!is_array($settings["validation"])) {
                $settings["validation"] = array($settings["validation"]);
            }
            $validation = array_merge($validation, $settings["validation"]);
        }

        foreach ($validation as $func) {
            if ($func == "unique") {
                // stuff in some extra bits
                $settings["model"] = Table::factory($this->getTableName());
                $settings["method"] = "find";
                $settings["field"] = $field;
            }
            if ($func != "required" && $value == "") {
                // don't try and validate empty non-requireds
                Log::debug("not validating empty value against [".$func."]");
                continue;
            }
            Log::debug("Validate::$func($value) [$field]");
            $result = Validate::$func($value, $settings);
            if ($result !== true) {
                return Validate::getMessage($func, $settings, $value);
            }
        }
        // all good
        return true;
    }
    
    protected function process($field, $value, $type) {
        switch ($type) {
            case "checkbox":
                if (isset($value)) {
                    return true;
                } else {
                    return false;
                }
            case "password":
                $old_pass = isset($this->values[$field]) ? $this->values[$field] : "";
                if ($value != $old_pass) {
                    // new value is different, so re-encode
                    return $this->encode($value);
                } else {
                    return $value;
                }
            case "date":
                if (preg_match("#(\d{2})/(\d{2})/(\d{2,4})#", $value, $matches)) {
                    if (strlen($matches[3]) == 2) {
                        $matches[3] = "20".$matches[3];
                    }
                    return $matches[3]."-".$matches[2]."-".$matches[1];
                } else {
                    return $value;
                }
            default:
                return $value;
        }
    }

    // override this if you want to change how a field of type "password" is hashed
    protected function encode($value) {
        return sha1($value);
    }

    // child classes can override this, useful for deletes etc
    public function getChildren() {
        return array();
    }
}
