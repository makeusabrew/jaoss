<?php
define("PROJECT_ROOT", realpath(dirname(__FILE__)."/../")."/");
if (!defined("JAOSS_ROOT")) {
    define("JAOSS_ROOT", PROJECT_ROOT);
}
set_include_path(get_include_path() . PATH_SEPARATOR . PROJECT_ROOT);
set_include_path(get_include_path() . PATH_SEPARATOR . JAOSS_ROOT);
ini_set("display_errors", 1);
error_reporting(-1);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (error_reporting() == 0) {
        //Log::info("Surpressed error (".$errno.") caught in handler: [".$errstr."] in [".$errfile."] line [".$errline."]");
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

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
include("library/cache.php");
include("library/statsd.php");

// set some settings manually
Settings::setFromArray(array(
    "session" => array(
        "handler" => "test",
    ),
    "email" => array(
        "handler" => "test",
    ),
    "request" => array(
        "handler" => "test",
    ),
    "log" => array(
        "warn" => JAOSS_ROOT."tests/log/test_log.log",
        "info" => JAOSS_ROOT."tests/log/test_log.log",
        "debug" => JAOSS_ROOT."tests/log/test_log.log",
        "verbose" => JAOSS_ROOT."tests/log/test_log.log",
        "level" => "verbose",
    ),
    "errors" => array(
        "verbose" => true,
    ),
    "smarty" => array(
        "compile_dir" => sys_get_temp_dir(),
    ),
    "site" => array(
        "namespace" => "test_namespace",
    ),
    "date" => array(
        "allow_override" => true,
    ),
));

// log some debug straight away to check the file is there
try {
    Log::debug("Bootstrapping test process");
} catch (CoreException $e) {
    die("Could not initialise library test logfile. Please ensure it exists.\n");
}

require_once("library/test/phpunit_test_controller.php");
require_once("library/test/selenium_test_controller.php");
require_once("library/test/test_request.php");
