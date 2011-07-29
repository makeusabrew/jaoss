<?php
define("PROJECT_ROOT", realpath(dirname(__FILE__)."/../")."/");
if (!defined("JAOSS_ROOT")) {
    define("JAOSS_ROOT", PROJECT_ROOT);
}
set_include_path(get_include_path() . PATH_SEPARATOR . PROJECT_ROOT);
set_include_path(get_include_path() . PATH_SEPARATOR . JAOSS_ROOT);
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_STRICT);

date_default_timezone_set("Europe/London");

include("library/Smarty/libs/Smarty.class.php");
include("library/core_exception.php");
include("library/email.php");
include("library/file.php");
include("library/validate.php");
include("library/error_handler.php");
include("library/flash_messenger.php");
include("library/log.php");
include("library/path.php");
include("library/path_manager.php");
include("library/request.php");
include("library/response.php");
include("library/controller.php");
include("library/settings.php");
include("library/database.php");
include("library/table.php");
include("library/object.php");
include("library/app.php");
include("library/app_manager.php");
include("library/cookie_jar.php");
include("library/session.php");
include("library/utils.php");
include("library/image.php");

// set some settings manually
Settings::setFromArray(array(
    "session" => array(
        "handler" => "test",
    ),
    "log" => array(
        "warn" => JAOSS_ROOT."tests/log/test_log.log",
        "debug" => JAOSS_ROOT."tests/log/test_log.log",
        "verbose" => JAOSS_ROOT."tests/log/test_log.log",
        "level" => "verbose",
    ),
    "errors" => array(
        "verbose" => true,
    ),
    "smarty" => array(
        "compile_dir" => "/tmp",
    ),
    "site" => array(
        "namespace" => "test_namespace",
    ),
));

// log some debug straight away to check the file is there
try {
    Log::debug("Bootstrapping test process");
} catch (CoreException $e) {
    die("Could not initialise library test logfile. Please ensure it exists.\n");
}
