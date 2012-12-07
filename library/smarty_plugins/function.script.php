<?php
function smarty_function_script($params, $template) {
    static $files = array();

    if (isset($params["output"])) {
        if (Settings::getValue("assets", "compile", false)) {
            $runPath = PROJECT_ROOT."public/assets/".$params["output"];

            $data = "";
            foreach ($files as $file) {
                $data .= file_get_contents(PROJECT_ROOT.$file);
                $data .= "\n/***/\n";
            }
            $handle = fopen($runPath, "w");

            fwrite($handle, $data, strlen($data));
            fclose($handle);
        }

        return "<script src=\"/assets/".$params["output"]."\"></script>";

    } else if (isset($params["add"])) {

        $files[] = $params["add"];
    }
}

