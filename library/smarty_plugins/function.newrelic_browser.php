<?php
function smarty_function_newrelic_browser($params, $template) {
    if (!isset($params["section"])) {
        trigger_error("no section supplied");
    }

    if ($params["section"] !== "header" && $params["section"] !== "footer") {
        trigger_error("incorrect section supplied");
    }

    if (extension_loaded("newrelic")) {

        if ($params["section"] === "header") {
            return newrelic_get_browser_timing_header();
        }

        return newrelic_get_browser_timing_footer();
    }
}
