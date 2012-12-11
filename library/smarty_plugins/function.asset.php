<?php
function smarty_function_asset($params, $template) {
    static $files = array(
        "js"  => array(),
        "css" => array(),
    );

    $type = $params["type"];

    if (isset($params["file"])) {
        $compress = (isset($params["min"]) && $params["min"]);
        
        if ($compress) {
            $outputFile = $params["file"].".min.".$type;
        } else {
            $outputFile = $params["file"].".".$type;
        }

        if (Settings::getValue("assets", "compile", false)) {

            $data = "";
            foreach ($files[$type] as $file) {
                $data .= file_get_contents(PROJECT_ROOT.$file);
                $data .= "\n/***/\n";
            }

            $filePath = null;

            if (!$compress) {
                $filePath = PROJECT_ROOT."public/assets/".$type."/".$outputFile;
            } else {
                $filePath = tempnam(sys_get_temp_dir(), $type);
            }

            // either way, write to the handle
            $handle = fopen($filePath, "w");
            fwrite($handle, $data, strlen($data));
            fclose($handle);

            if ($compress) {
                $outputPath = PROJECT_ROOT."public/assets/".$type."/".$outputFile;
                // now, if we've got compression, run it through a minifier
                // @todo yes, of course this is awful and WILL definitely change ASAP
                $cmd = "java -jar ".PROJECT_ROOT."yuicompressor-2.4.7.jar --type ".$type." -o ".escapeshellarg($outputPath)." ".escapeshellarg($filePath);
                Log::info("Compressing ".$type." [".$cmd."]");
                $output = null;
                $retval = null;
                exec($cmd, $output, $retVal);
                if ($retVal !== 0) {
                    Log::warn($type." compression failed, return code [".$retVal."]");
                    Log::warn("Compression output: ".implode(",", $output));
                }
                unlink($filePath);
            }
        }

        return "<script src=\"/assets/".$type."/".$outputFile."\"></script>";

    } else if (isset($params["add"])) {

        $files[$type][] = $params["add"];
    }
}

