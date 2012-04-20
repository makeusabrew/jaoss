<?php
 class Colours {

    const RED     = "0;31";
    const GREEN   = "0;32";
    const YELLOW  = "0;33";
    const BLUE    = "0;34";
    const MAGENTA = "0;35";
    const CYAN    = "0;36";

    protected static $disabled = false;

    public static function disable() {
        self::$disabled = true;
    }

    public static function red($str) {
        return self::colour($str, self::RED);
    }

    public static function green($str) {
        return self::colour($str, self::GREEN);
    }

    public static function yellow($str) {
        return self::colour($str, self::YELLOW);
    }
    
    public static function blue($str) {
        return self::colour($str, self::BLUE);
    }

    public static function magenta($str) {
        return self::colour($str, self::MAGENTA);
    }

    public static function cyan($str) {
        return self::colour($str, self::CYAN);
    }

    public static function colour($str, $code) {
        if (self::$disabled) {
            return $str;
        }
        return chr(27)."[".$code."m".$str.chr(27)."[0m";
    }
        
}
