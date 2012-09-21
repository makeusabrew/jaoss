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

        $this->mkdir($appPath);

        $this->mkdir($appPath."/controllers");

        if (isset($model)) {
            $this->mkdir($appPath."/models");
        }

        $this->mkdir($appPath."/views");

        $this->write("\n");

        $this->mkdir($appPath."/tests");
        $this->mkdir($appPath."/tests/controllers");
        if (isset($model)) {
            $this->mkdir($appPath."/tests/models");
        }

        $this->smarty->assign("pattern", "/".$appName);
        $this->smarty->assign("action", "index");
        $this->smarty->assign("controller", ucfirst(strtolower($appName)));
        $this->smarty->assign("app", $appName);
        $this->smarty->assign("fullPath", $appPath);
        if (isset($model)) {
            $this->smarty->assign("model", $model);
        }

        $this->writeFile($appPath."/paths.php", "create/app/paths.php");
        $this->writeFile($appPath."/controllers/".strtolower($appName).".php", "create/app/controller.php");

        if (isset($model)) {
            $this->writeFile($appPath."/models/".strtolower($model).".php", "create/app/model.php");
        }

        $this->writeFile($appPath."/views/index.tpl", "create/app/view.tpl");
    }

    protected function mkdir($dir, $friendlyDir = null) {
        if ($friendlyDir === null) {
            $friendlyDir = $dir;
        }
        $this->writeLine("Creating ".$friendlyDir." directory");
        mkdir($dir);
    }

    protected function writeFile($target, $source) {
        $this->writeLine("Creating ".$target." file");
        $handle = fopen($target, "w");
        fwrite($handle, $this->smarty->fetch($source.".tpl"));
        fclose($handle);
    }
}
