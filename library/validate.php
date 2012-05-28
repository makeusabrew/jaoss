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
        return preg_match("#^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$#i", $value) > 0;
    }

    public static function unsigned($value, $settings = null) {
        return ($value >= 0);
    }

    public static function postcode($value, $settings = null) {
        // @see http://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom
        return preg_match("#^(GIR 0AA)|(((A[BL]|B[ABDHLNRSTX]?|C[ABFHMORTVW]|D[ADEGHLNTY]|E[HNX]?|F[KY]|G[LUY]?|H[ADGPRSUX]|I[GMPV]|JE|K[ATWY]|L[ADELNSU]?|M[EKL]?|N[EGNPRW]?|O[LX]|P[AEHLOR]|R[GHM]|S[AEGKLMNOPRSTY]?|T[ADFNQRSW]|UB|W[ADFNRSV]|YO|ZE)[1-9]?[0-9]|((E|N|NW|SE|SW|W)1|EC[1-4]|WC[12])[A-HJKMNPR-Y]|(SW|W)([2-9]|[1-9][0-9])|EC[1-9][0-9]) [0-9][ABD-HJLNP-UW-Z]{2})$#i", $value) > 0;
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

    public static function matchOption($value, $settings) {
        // for now, we expect options to be an associative array like so:
        // array("actual_value" => "Display Value")
        return isset($settings['options'][$value]);
    }

    public static function matchCheckboxOptions($value, $settings) {
        if (!is_array($value) || count($value) == 0) {
            return false;
        }
        foreach ($value as $key => $val) {
            // we expect $value to be something like
            // array("actual_value" => "On")
            // in effect, we ignore the $val, as the key's
            // presence is all we're interested in
            if (!isset($settings['options'][$key])) {
                return false;
            }
        }
        return true;
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
        if (preg_match("#^(\d{2})/(\d{2})/(\d{2}|\d{4})$#", $value, $matches)) {
            return self::dateInternal($matches);
        }
        return false;
    }

    public static function dateTime($value, $settings = null) {
        if (preg_match("#^(\d{2})/(\d{2})/(\d{2}|\d{4})\s\d{2}:\d{2}(:\d{2}|)$#", $value, $matches)) {
            return self::dateInternal($matches);
        }
        return false;
    }

    protected static function dateInternal($matches) {
        if (strlen($matches[3]) == 2) {
            $matches[3] = "20".$matches[3];
        }
        return checkdate($matches[2], $matches[1], $matches[3]);
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
            case "dateTime":
                return "{$title} must be in the format dd/mm/yyyy hh:mm:ss";
            case "minAge":
                return "{$title} does not meet the minimum age requirement of {$settings["age"]}";
            case "postcode":
                return "{$title} is not a valid postcode";
            case "matchOption":
                return "{$title} does not match one of the available options";
            case "matchCheckboxOptions":
                return "one or more of the options chosen for {$title} are not valid";
            case "unsigned":
                return "{$title} must be zero or greater";
            default:
                return "{$title} is not valid";
        }
    }
}
