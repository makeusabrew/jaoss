<?php
function smarty_function_validation_regex($params, $template) {
    if (!isset($params["type"])) {
        trigger_error("no type supplied");
    }
    $regex = Validate::regex($params["type"]);

    $value = substr($regex, 2, -2);
    if (!isset($params["assign"])) {
        return $value;
    } else {
        $template->assign($params["assign"], $value);
    }
}
