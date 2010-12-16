<?php
function smarty_function_setting($params, $template) {
    if (!isset($params["value"])) {
        trigger_error("no value supplied");
    }
    $value = Settings::getValue($params["value"], false);
    if (!isset($params["assign"])) {
        return $value;
    } else {
        $template->assign($params["assign"], $value);
    }
}
