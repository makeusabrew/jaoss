<?php
 class Colours {
    public static function red($str) {
        return self::colour($str, "0;31");
    }

    public static function green($str) {
        return self::colour($str, "0;32");
    }

    public static function yellow($str) {
        return self::colour($str, "0;33");
    }

    public static function cyan($str) {
        return self::colour($str, "0;36");
    }

    protected static function colour($str, $code) {
        return chr(27)."[".$code."m".$str.chr(27)."[0m";
    }
        
}
