<?php
 class Colours {

    const YELLOW = "0;33";
    const BLUE   = "0;34";

    public static function red($str) {
        return self::colour($str, "0;31");
    }

    public static function green($str) {
        return self::colour($str, "0;32");
    }

    public static function yellow($str) {
        return self::colour($str, self::YELLOW);
    }
    
    public static function blue($str) {
        return self::colour($str, self::BLUE);
    }

    public static function magenta($str) {
        return self::colour($str, "0;35");
    }

    public static function cyan($str) {
        return self::colour($str, "0;36");
    }

    public static function colour($str, $code) {
        return chr(27)."[".$code."m".$str.chr(27)."[0m";
    }
        
}
