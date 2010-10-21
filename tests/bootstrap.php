<?php
//require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__."/../library/");

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_STRICT);

date_default_timezone_set("Europe/London");

include("Smarty-3.0rc4/libs/Smarty.class.php");
include("core_exception.php");
include("email.php");
include("file.php");
include("validate.php");
include("error_handler.php");
include("flash_messenger.php");
include("log.php");
include("path.php");
include("path_manager.php");
include("request.php");
include("controller.php");
include("settings.php");
include("database.php");
include("table.php");
include("object.php");
include("app.php");
include("app_manager.php");
include("session.php");
include("utils.php");

// set some settings manually
Settings::setFromArray(array(
    "session" => array(
        "handler" => "test",
    ),
    "log" => array(
        "debug_handle" => "../../library.log",
        "debug" => "debug_handle",
        "verbose" => "debug_handle",
    ),
));

// log some debug straight away to check the file is there
try {
    Log::debug("Bootstrapping test process");
} catch (CoreException $e) {
    die("Could not initialise library test logfile [../../library.log]. Please ensure it exists.\n");
}

$library_settings = "../../library.ini";
if (file_exists($library_settings) && is_readable($library_settings)) {
    Log::debug("loading in additional settings from library.ini");
    Settings::loadFromFile($library_settings);
}
