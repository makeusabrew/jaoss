<?php

class Validate {
    public static function required($value, $settings) {
        $type = isset($settings["type"]) ? $settings["type"] : "text";
        switch ($type) {
            default:
                return (trim($value) != "");
        }
    }

    public static function email($value, $settings) {
        return preg_match("#^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$#", $value) > 0;
    }

    public static function minLength($value, $settings) {
        $length = isset($settings["length"]) ? $settings["length"] : 0;
        return (strlen($value) >= $length);
    }

    public static function getMessage($function, $title, $settings = null, $value = null) {
        switch ($function) {
            case "email":
                return "{$title} is not a valid email address";
            case "required":
                return "{$title} is required";
            case "minLength":
                return "{$title} must be at least {$settings["length"]} characters long";
            case "match":
                return "the two {$title}s do not match";
            default:
                return "{$title} is not valid";
        }
    }

    public static function match($value, $settings) {
        return ($value == $settings["confirm"]);
    }
}
