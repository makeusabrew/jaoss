<?php

class Validate {
    public static function required($value, $settings = null) {
        $type = isset($settings["type"]) ? $settings["type"] : "text";
        switch ($type) {
            default:
                return (trim($value) != "");
        }
    }

    public static function email($value, $settings = null) {
        // @todo replace this old school preg match with something better!
        return preg_match("#^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$#", $value) > 0;
    }

    public static function postcode($value, $settings = null) {
        // @see http://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom
        return preg_match("#^(GIR 0AA)|(((A[BL]|B[ABDHLNRSTX]?|C[ABFHMORTVW]|D[ADEGHLNTY]|E[HNX]?|F[KY]|G[LUY]?|H[ADGPRSUX]|I[GMPV]|JE|K[ATWY]|L[ADELNSU]?|M[EKL]?|N[EGNPRW]?|O[LX]|P[AEHLOR]|R[GHM]|S[AEGKLMNOPRSTY]?|T[ADFNQRSW]|UB|W[ADFNRSV]|YO|ZE)[1-9]?[0-9]|((E|N|NW|SE|SW|W)1|EC[1-4]|WC[12])[A-HJKMNPR-Y]|(SW|W)([2-9]|[1-9][0-9])|EC[1-9][0-9]) [0-9][ABD-HJLNP-UW-Z]{2})$#", $value) > 0;
    }

    public static function minLength($value, $settings = null) {
        $length = 0;
        if (isset($settings['minLength'])) {
            $length = $settings['minLength'];
        } else if (isset($settings['length'])) {
            // ['length'] is legacy - doesn't work if you need min and max
            $length = $settings['length'];
        }
        return (strlen($value) >= $length);
    }

    public static function maxLength($value, $settings = null) {
        $length = 0;
        if (isset($settings['maxLength'])) {
            $length = $settings['maxLength'];
        } else if (isset($settings['length'])) {
            // ['length'] is legacy - doesn't work if you need min and max
            $length = $settings['length'];
        }
        return (strlen($value) <= $length);
    }

    public static function match($value, $settings) {
        return ($value == $settings["confirm"]);
    }

    public static function unique($value, $settings) {
        $model = $settings["model"];
        $method = $settings["method"];
        $field = $settings["field"];
        $object = $model->$method("`{$field}` = ?", array($value));
        return $object ? false : true;
    }

    public static function numbersSpaces($value, $settings = null) {
        return preg_match("#^\d[\d\s]+\d$#", $value) > 0;
    }

    public static function date($value, $settings = null) {
        return preg_match("#^\d{2}/\d{2}/(\d{2}|\d{4})$#", $value) > 0;
    }

    public static function minAge($value, $settings = array()) {
        // we assume input dates are in the format dd/mm/yyyy
        $date = DateTime::createFromFormat('d/m/Y', $value);
        $target = null;
        if (isset($settings['target'])) {
            $target = DateTime::createFromFormat('d/m/Y', $settings['target']);
        } else {
            // without a target we assume validation against today
            $target = new DateTime();
        }
        $diff = $target->diff($date);
        return ($diff->y >= $settings['age']);
    }
    
    public static function getMessage($function, $settings, $value = null) {
        $title = $settings["title"];
        switch ($function) {
            case "email":
                return "{$title} is not a valid email address";
            case "required":
                return "{$title} is required";
            case "minLength":
                $length = 0;
                if (isset($settings['minLength'])) {
                    $length = $settings['minLength'];
                } else if (isset($settings['length'])) {
                    // ['length'] is legacy - doesn't work if you need min and max
                    $length = $settings['length'];
                }
                return "{$title} must be at least {$length} characters long";
            case "maxLength":
                $length = 0;
                if (isset($settings['maxLength'])) {
                    $length = $settings['maxLength'];
                } else if (isset($settings['length'])) {
                    // ['length'] is legacy - doesn't work if you need min and max
                    $length = $settings['length'];
                }
                return "{$title} must be no more than {$length} characters long";
            case "match":
                return "the two {$title}s do not match";
            case "unique":
                return "this {$title} is already in use";
            case "numbersSpaces":
                return "{$title} must contain only numbers and spaces";
            case "numbers":
                return "{$title} must contain only numbers";
            case "date":
                return "{$title} must be in the format dd/mm/yyyy"; 
            case "minAge":
                return "{$title} does not meet the minimum age requirement of {$settings["age"]}";
            case "postcode":
                return "{$title} is not a valid postcode";
            default:
                return "{$title} is not valid";
        }
    }
}
