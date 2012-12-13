<?php
require_once("library/asset_pipeline/script.php");
function smarty_function_script($params, $template) {

    $pipeline = ScriptPipeline::getInstance();

    if (isset($params["add"])) {
        return $pipeline->addFile($params["add"]);
    }

    if (!isset($params["output"])) {
        return trigger_error("Please pass either 'add' or 'output' parameters");
    }

    // definitely in output mode, carry on
    $pipeline->setOptions($params);

    if (Settings::getValue("assets", "compile", false)) {
        
        // @todo actual pipelining

    }

    return $pipeline->getHtmlTag();
}
