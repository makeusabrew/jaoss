<?php
function smarty_function_url($params, $template) {
    if (!isset($params["path"])) {
        trigger_error("No path name specified");
    }

    $url = PathManager::getUrlForOptions(array(
        "name" => $params["path"]
    ));

    $base = JaossRequest::getInstance()->getFolderBase();

    return substr($base, 0, -1).$url;
}
