<?php
function smarty_function_script($params, $template) {
    static $files = array();

    if (isset($params["file"])) {
        $compress = (isset($params["min"]) && $params["min"]);
        
        if ($compress) {
            $outputFile = $params["file"].".min.js";
        } else {
            $outputFile = $params["file"].".js";
        }

        if (Settings::getValue("assets", "compile", false)) {

            $data = "";
            foreach ($files as $file) {
                $data .= file_get_contents(PROJECT_ROOT.$file);
                $data .= "\n/***/\n";
            }

            $filePath = null;

            if (!$compress) {
                $filePath = PROJECT_ROOT."public/assets/js/".$outputFile;
            } else {
                $filePath = tempnam(sys_get_temp_dir(), "js");
            }

            // either way, write to the handle
            $handle = fopen($filePath, "w");
            fwrite($handle, $data, strlen($data));
            fclose($handle);

            if ($compress) {
                $outputPath = PROJECT_ROOT."public/assets/js/".$outputFile;
                // now, if we've got compression, run it through a minifier
                // @todo yes, of course this is awful and WILL definitely change ASAP
                $cmd = "java -jar ".PROJECT_ROOT."yuicompressor-2.4.7.jar --type js -o ".escapeshellarg($outputPath)." ".escapeshellarg($filePath);
                Log::info("Compressing JS [".$cmd."]");
                $output = null;
                $retval = null;
                exec($cmd, $output, $retVal);
                if ($retVal !== 0) {
                    Log::warn("JS compression failed, return code [".$retVal."]");
                    Log::warn("Compression output: ".implode(",", $output));
                }
            }
        }

        return "<script src=\"/assets/js/".$outputFile."\"></script>";

    } else if (isset($params["add"])) {

        $files[] = $params["add"];
    }
}

