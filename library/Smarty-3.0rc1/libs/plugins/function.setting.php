<?php
function smarty_function_setting($params, $smarty, $template)
{
    if (!empty($params["from"])) {
    	return Settings::getValue($params["from"]);
    }
    
    if (empty($params['section'])) {
        trigger_error("setting: missing section parameter",E_USER_WARNING);
        return;
    }
    
    if (empty($params['key'])) {
        trigger_error("setting: missing key parameter",E_USER_WARNING);
        return;
    }
    
    return Settings::getValue($params["section"], $params["key"]);
}
