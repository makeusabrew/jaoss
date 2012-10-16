<?php
function smarty_function_validation_regex($params, $template) {
    if (!isset($params["type"])) {
        trigger_error("no type supplied");
    }
    $regex = Validate::regex($params["type"]);
    return substr($regex, 2, -2);
}
