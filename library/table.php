<?php

class Table {
	protected $primary_key = "id";
	protected $parent_key = "parent_id";
	protected $order_by = "`created` ASC";
	
	protected $object_name = NULL;
	protected $table_name = NULL;
	
	protected $meta = array();
	
	public static function getNewObject($model) {
		$model = self::factory($model);
		$name = $model->getObjectName();
		return new $name;
	}
	
	public static function factory($model) {
		$m_class = $model;
		if (class_exists($m_class)) {
			return new $m_class;
		}
		$apps = AppManager::getAppPaths();
		foreach ($apps as $app) {
			$path = "apps/{$app}/models/".strtolower($model).".php";
			if (file_exists($path)) {
				include($path);
				return self::factory($model);
			}
		}
		throw new CoreException("Could not find model in any path");
	}
	
	public function getObjectName() {
		if (!isset($this->object_name)) {
			$name = get_class($this);
			$this->object_name = substr($name, 0, -1);
		}
		return $this->object_name;
	}
	
	public function getTableName() {
		if (!isset($this->table_name)) {
			$table = strtolower(get_class($this));
			$this->table_name = "app_{$table}";
		}
		return $this->table_name;
	}
	
	public function findAll($where = NULL, $params = NULL, $order_by = NULL, $limit = NULL) {
		$q = "SELECT * FROM `".$this->getTableName()."`";
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
	
	public function read($id = NULL) {
		$q = "SELECT * FROM `".$this->getTableName()."` WHERE `{$this->primary_key}` = ?";
		$dbh = Db::getInstance();
		$sth = $dbh->prepare($q);
        $sth->setFetchMode(PDO::FETCH_CLASS, $this->getObjectName());
        $sth->execute(array($id));
		return $sth->fetch();
	}
	
	public function getColumnInfo($column) {
		return $this->meta["columns"][$column];
	}
		
}
