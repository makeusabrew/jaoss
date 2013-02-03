<?php
/**
 * init simply takes care of loading all the library's dependencies
 * so that projects using jaoss don't have to keep their own bootstrap
 * files up-to-date
 */
include("library/Smarty/libs/Smarty.class.php");
include("library/exception/core.php");
include("library/exception/init.php");
include("library/exception/rejected.php");
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
include("library/curl/request.php");
include("library/curl/response.php");
include("library/asset_pipeline.php");

// if we're running the CLI tool, include some extra helpers
if (defined('JAOSS_CLI')) {
    include("library/cli/cli.php");
    include("library/cli/colours.php");
    include("library/cli/exception.php");
}
