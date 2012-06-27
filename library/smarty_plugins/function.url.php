<?php
function smarty_function_url($params, $template) {
    if (!isset($params["path"])) {
        trigger_error("No path name specified");
    }

    return PathManager::getUrlForOptions(array(
        "name" => $params["path"]
    ));
}
