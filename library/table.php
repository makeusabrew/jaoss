<?php

class Table {
	protected $primary_key = "id";
	protected $parent_key = "parent_id";
	protected $order_by = "`created` ASC";
	
	protected $object_name = NULL;
	protected $table = NULL;
	
	protected $meta = array();
	
	public static function getNewObject($model) {
		$model = self::factory($model);
		return $model->newObject();
	}
	
	public function newObject() {
		$name = $this->getObjectName();
		return new $name;
	}
	
	public static function factory($model) {
		$m_class = $model;
		if (class_exists($m_class)) {
			return new $m_class;
		}
		$apps = AppManager::getAppPaths();
		foreach ($apps as $app) {
			$path = PROJECT_ROOT."apps/{$app}/models/".Utils::fromCamelCase($model).".php";
			if (file_exists($path)) {
				include($path);
				return self::factory($model);
			}
		}
		throw new CoreException("Could not find model in any path: {$model}");
	}
	
	public function getObjectName() {
		if (!isset($this->object_name)) {
			$name = get_class($this);
			$this->object_name = substr($name, 0, -1);
		}
        if (!class_exists($this->object_name)) {
            throw new CoreException("Object class does not exist: ".$this->object_name);
        }
		return $this->object_name;
	}
	
	public function getTable() {
		if (!isset($this->table)) {
			$table = Utils::fromCamelCase(get_class($this));
			$this->table = "{$table}";
		}
		return $this->table;
	}
	
	public function findAll($where = NULL, $params = NULL, $order_by = NULL, $limit = NULL) {
		$q = "SELECT * FROM `".$this->getTable()."`";
		if ($where !== NULL) {
			$q .= " WHERE {$where}";
		}
		if ($order_by !== NULL) {
			$q .= " ORDER BY {$order_by}";
		} else if ($this->order_by !== NULL) {
			$q .= " ORDER BY {$this->order_by}";
		}
		if ($limit !== NULL) {
			$q .= " LIMIT {$limit}";
		}
		$dbh = Db::getInstance();
		$sth = $dbh->prepare($q);
		$sth->setFetchMode(PDO::FETCH_CLASS, $this->getObjectName());
		
		$sth->execute($params);
		return $sth->fetchAll();
	}

    public function find($where = NULL, $params = NULL, $order_by = NULL) {
		$q = "SELECT * FROM `".$this->getTable()."`";
		if ($where !== NULL) {
			$q .= " WHERE {$where}";
		}
		if ($order_by !== NULL) {
			$q .= " ORDER BY {$order_by}";
		} else if ($this->order_by !== NULL) {
			$q .= " ORDER BY {$this->order_by}";
		}
		$dbh = Db::getInstance();
		$sth = $dbh->prepare($q);
		$sth->setFetchMode(PDO::FETCH_CLASS, $this->getObjectName());

		$sth->execute($params);
		return $sth->fetch();
	}
	
	public function read($id = NULL) {
		$q = "SELECT * FROM `".$this->getTable()."` WHERE `{$this->primary_key}` = ?";
		$dbh = Db::getInstance();
		$sth = $dbh->prepare($q);
        $sth->setFetchMode(PDO::FETCH_CLASS, $this->getObjectName());
        $sth->execute(array($id));
		return $sth->fetch();
	}
	
	public function getColumnInfo($column) {
		return isset($this->meta["columns"][$column]) ? $this->meta["columns"][$column] : NULL;
	}
	
	public function getHasManyInfo($column) {
		return isset($this->meta["has_many"][$column]) ? $this->meta["has_many"][$column] : NULL;
	}
	
	public function getColumns() {
		return $this->meta["columns"];
	}
	
	public function getColumnString($prefix = NULL) {
		$cols = $this->getColumns();
		$cols = array_keys($cols);
		array_unshift($cols, "id");
		if ($prefix) {
			foreach ($cols as &$col) {
				$col = $prefix.".".$col;
			}
		}
		return implode($cols, ",");
	}

    public function queryAll($sql, $params = array(), $objectName = NULL) {
        $objectName = $objectName ? $objectName : $this->getObjectName();
		$dbh = Db::getInstance();
		$sth = $dbh->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_CLASS, $objectName);
		$sth->execute($params);
		return $sth->fetchAll();
    }
		
}
