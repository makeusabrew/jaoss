<?php
class Cli_Table extends Cli {

    public function run() {
        if (count($this->args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                1 => 'create',
                2 => 'sync',
            ));
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function create() {
        list($class, $columns) = $this->getClassAndColumns();
        // right then, let's get busy!
        $sql = $this->createSql($class, $columns);
        $this->writeLine("The following SQL will create the table [".Colours::cyan(Settings::getValue("db", "dbname").".".$class->getTable())."]:");
        $this->writeLine(Colours::yellow($sql));

        $result = $this->prompt("Do you wish to commit these changes? [y/n]");
        if ($result !== 'y') {
            $this->writeLine("Aborting.");
            return;
        }

        $dbh = Db::getInstance();
        $sth = $dbh->prepare($sql);
        $result = $sth->execute();
        if ($result === true) {
            $this->writeLine(
                Colours::green("Table ".$class->getTable()." created on database [".Settings::getValue("db", "dbname")."]")
            );
            $this->writeLine(
                Colours::green("Don't forget to add this new table to any test fixtures!")
            );
        } else {
            $this->writeLine(
                Colours::red("Table ".$class->getTable()." could not be created. Check the log for further info.")
            );
        }
    }

    protected function sync() {
        list($class, $columns) = $this->getClassAndColumns();

        $sql = "DESCRIBE ".$class->getTable();

        $dbh = Db::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $rows = $sth->fetchAll();
        $fields = array();
        foreach ($rows as $row) {
            $fields[$row['Field']] = $row;
        }

        $sql = "ALTER TABLE ".$class->getTable()."\n";
        $modified = false;
        foreach ($columns as $field => $column) {
            if (!isset($fields[$field])) {
                $modified = true;
                $sql .= "ADD `".$field."` ".$this->getSqlForColumn($column).",\n";
            }
        }
        if ($modified === false) {
            $this->writeLine("No DB changes required.");
            return;
        }

        $sql = substr($sql, 0, -2);

        $this->writeLine("The following changes will be made to the table [".Colours::cyan(Settings::getValue("db", "dbname").".".$class->getTable())."]:");
        $this->writeLine(Colours::yellow($sql));

        $result = $this->prompt("Do you wish to commit these changes? [y/n]");
        if ($result !== 'y') {
            $this->writeLine("Aborting.");
            return;
        }

        $dbh = Db::getInstance();
        $sth = $dbh->prepare($sql);
        $result = $sth->execute();

        if ($result === true) {
            $this->writeLine(
                Colours::green("Table ".$class->getTable()." synced to database [".Settings::getValue("db", "dbname")."]")
            );
            $this->writeLine(
                Colours::green("Don't forget to update any test fixtures!")
            );
        } else {
            $this->writeLine(
                Colours::red("Table ".$class->getTable()." could not be synced. Check the log for further info.")
            );
        }
    }

    protected function getClassAndColumns() {
        if (!defined("PROJECT_ROOT")) {
            throw new CliException("This method is designed to be used in project mode only", 1);
        }
        if (count($this->args) === 0) {
            $model = $this->prompt('Please enter the class name of model for which to create the DB table for');
        } else {
            $model = $this->shiftArg();
        }
        $this->writeLine("Looking for model in project apps directory...");
        $class = Table::factory($model);
        $this->writeLine("Found ".get_class($class)." model!");

        if (($class instanceof Table) === false) {
            throw new CliException("Model does not extend Table class", 1);
        }
        
        $columns = $class->getColumns();
        if (empty($columns)) {
            throw new CliException("Model has no columns!", 1);
        }

        return array($class, $columns);
    }

    public function createSql($class, $columns) {
        $sql = 
        "CREATE TABLE `".$class->getTable()."` (\n".
        "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,\n";
        if ($class->shouldStoreCreated()) {
            $sql .= "`created` DATETIME NOT NULL,\n";
        }
        if ($class->shouldStoreUpdated()) {
            $sql .= "`updated` DATETIME NOT NULL,\n";
        }
        $sql = substr($sql, 0, -2);
        foreach ($columns as $field => $column) {
            $sql .= ",\n`".$field."` ";
            if (!isset($column["type"])) {
                throw new CliException("Field [".$field."] has no column type", 1);
            }

            $sql .= $this->getSqlForColumn($column);
        }

        $sql .= "\n)";

        return $sql;
    }

    protected function getSqlForColumn($column) {
        $sql = "";
        switch ($column["type"]) {
            case "foreign_key":
                $sql = "INT UNSIGNED NOT NULL";
                break;
            case "number":
                $modifier = isset($column['validation']) && $column['validation'] === 'unsigned' ? ' UNSIGNED' : '';
                $sql = "INT{$modifier} NOT NULL";
                break;
            case "double":
                $modifier = isset($column['validation']) && $column['validation'] === 'unsigned' ? ' UNSIGNED' : '';
                $sql = "DOUBLE{$modifier} NOT NULL";
                break;
            case "textarea":
            case "html_textarea":
                $sql = "TEXT NOT NULL";
                break;
            case "date":
                $sql = "DATE NOT NULL";
                break;
            case "datetime":
                $sql = "DATETIME NOT NULL";
                break;
            case "select":
                $sql = "ENUM(";
                if (!isset($column["options"])) {
                    throw new CliException("Column type [select] has no mandatory key [options]", 1);
                }
                foreach ($column["options"] as $key => $val) {
                    $sql .= "'".$key."', ";
                }
                $sql = substr($sql, 0, -2);
                $sql .= ") NOT NULL";
                break;
            case "text":
            case "email":
                $sql = "VARCHAR( 255 ) NOT NULL";
                break;
            case "postcode":
                $sql = "VARCHAR( 8 ) NOT NULL";
                break;
            case "bool":
            case "checkbox":
                $sql = "TINYINT(1) NOT NULL";
                break;
            default:
                Log::warn("Creating default VARCHAR(255) field for unknown type [".$column["type"]."]");
                $sql = "VARCHAR( 255 ) NOT NULL";
                break;
        }
        return $sql;
    }
}
