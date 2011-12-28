<?php
class Cli_Create extends Cli {

    protected $smarty;

    public function __construct() {
        require_once(JAOSS_ROOT."library/Smarty/libs/Smarty.class.php");

        $this->smarty = new Smarty();
        $this->smarty->template_dir	= array(JAOSS_ROOT."library/cli/cmd/resources/");
        $this->smarty->compile_dir = sys_get_temp_dir();
    }

    public function run() {
        if (count($this->args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                1 => 'project',
                2 => 'app',
                3 => 'table',
            ));
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function project() {
        if (count($this->args) === 0) {
            // no problemo, go interactive
            $dir = $this->prompt('Please choose a project directory', getcwd());
        } else {
            $dir = $this->shiftArg();
        }

        $this->exec(
            'git clone --recursive git://github.com/makeusabrew/jaoss-web-template.git '.escapeshellarg($dir),
            'Cloning jaoss web template github repo into folder ['.$dir.']'
        )->exec(
            'rm -rf '.escapeshellarg($dir).'/.git/',
            'Removing web template git folder'
        )->exec(
            'cd '.escapeshellarg($dir).'; git init',
            'Creating new git project'
        );
    }

    protected function app() {
        if ($this->hasArg("--model")) {
            $model = $this->getArgValue("--model");
            if ($model === null) {
                $model = $this->prompt("Please enter a SINGULAR model name (e.g. Car not Cars)");
            }
        }

        if (count($this->args) === 0) {
            $appName = $this->prompt('Please choose an app name (all lowercase, single word)');
        } else {
            $appName = $this->shiftArg();
        }
        if (!defined("PROJECT_ROOT")) {
            throw new CliException("This method is designed to be used in project mode only", 1);
        }

        $appPath = PROJECT_ROOT."apps/".$appName;
        if (is_dir($appPath)) {
            throw new CliException("App directory [".$appName."] already exists", 1);
        }
        if (!is_writable(PROJECT_ROOT."apps/")) {
            throw new CliException("Apps directory is not writable", 1);
        }

        $this->writeLine("Creating apps/".$appName." directory");
        mkdir($appPath);
        $this->writeLine("Creating apps/".$appName."/controllers directory");
        mkdir($appPath."/controllers");

        if (isset($model)) {
            $this->writeLine("Creating apps/".$appName."/models directory");
            mkdir($appPath."/models");
        }

        $this->writeLine("Creating apps/".$appName."/views directory");
        mkdir($appPath."/views");

        $this->write("\n");

        $this->smarty->assign("pattern", "/".$appName);
        $this->smarty->assign("action", "index");
        $this->smarty->assign("controller", ucfirst(strtolower($appName)));
        $this->smarty->assign("app", $appName);
        $this->smarty->assign("fullPath", $appPath);
        if (isset($model)) {
            $this->smarty->assign("model", $model);
        }

        $this->writeLine("Creating apps/".$appName."/paths.php file");
        $handle = fopen($appPath."/paths.php", "w");
        fwrite($handle, $this->smarty->fetch("create/app/paths.php.tpl"));
        fclose($handle);

        $this->writeLine("Creating apps/".$appName."/controllers/".strtolower($appName).".php controller");
        $handle = fopen($appPath."/controllers/".strtolower($appName).".php", "w");
        fwrite($handle, $this->smarty->fetch("create/app/controller.php.tpl"));
        fclose($handle);

        if (isset($model)) {
            $this->writeLine("Creating apps/".$appName."/models/".strtolower($model)."s.php model");
            $handle = fopen($appPath."/models/".strtolower($model)."s.php", "w");
            fwrite($handle, $this->smarty->fetch("create/app/model.php.tpl"));
            fclose($handle);
        }

        $this->writeLine("Creating apps/".$appName."/views/index.tpl view");
        $handle = fopen($appPath."/views/index.tpl", "w");
        fwrite($handle, $this->smarty->fetch("create/app/view.tpl.tpl"));
        fclose($handle);
    }

    protected function table() {
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

        // right then, let's get busy!
        $sql = $this->createSql($class->getTable(), $columns);
        if ($this->hasArg("--output-only")) {
            $this->writeLine(Colours::yellow($sql));
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

    public function createSql($tableName, $columns) {
        $sql = 
        "CREATE TABLE `".$tableName."` (\n".
        "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,\n".
        "`created` DATETIME NOT NULL,\n".
        "`updated` DATETIME NOT NULL";
        foreach ($columns as $field => $column) {
            $sql .= ",\n`".$field."` ";
            if (!isset($column["type"])) {
                throw new CliException("Field [".$field."] has no column type", 1);
            }

            switch ($column["type"]) {
                case "foreign_key":
                    $sql .= "INT UNSIGNED NOT NULL";
                    break;
                case "number":
                    $sql .= "INT NOT NULL";
                    break;
                case "textarea":
                case "html_textarea":
                    $sql .= "TEXT NOT NULL";
                    break;
                case "date":
                    $sql .= "DATE NOT NULL";
                    break;
                case "datetime":
                    $sql .= "DATETIME NOT NULL";
                    break;
                case "select":
                    $sql .= "ENUM(";
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
                    $sql .= "VARCHAR( 255 ) NOT NULL";
                    break;
                case "postcode":
                    $sql .= "VARCHAR( 8 ) NOT NULL";
                    break;
                case "bool":
                    $sql .= "TINYINT(1) NOT NULL";
                    break;
                default:
                    Log::warn("Creating default VARCHAR(255) field for unknown type [".$column["type"]."]");
                    $sql .= "VARCHAR( 255 ) NOT NULL";
                    break;
            }
        }

        $sql .= "\n)";

        return $sql;
    }
}
