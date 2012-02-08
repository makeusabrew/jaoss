<?php

class Utils {
    protected static $currentTimestamp = null;

	public static function fuzzyTime($from, $to = NULL) {
		if (!is_numeric($from)) {
			// handle dates graciously
			$from = strtotime($from);
		}
		if ($to === NULL) {
			$to = time();
		} else if (!is_numeric($to)) {
			$to = strtotime($to);
		}

		$elapsed = $to - $from;
		if ($elapsed < 30) {
			return "Just now";
		}
		if ($elapsed < 60) {
			return "About a minute ago";
		}
		
		// catch all
		$days = floor($elapsed / 86400);
		if ($days == 1) {
            return "Yesterday";
		}
		$str = $days." days ago";
		return $str;
	}
	
	public static function fromCamelCase($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}

    /* borrowed with thanks from http://wiki.jumba.com.au/wiki/PHP_Generate_random_password */
    public static function generatePassword($length) {
        $chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $maxLength = strlen($chars) - 1;
        $i = 0;
        $password = "";
        while ($i < $length) {
            $password .= $chars{mt_rand(0, $maxLength)};
            $i++;
        }
        return $password;
    }

    public static function getDate($str) {
        if (self::$currentTimestamp === null) {
            return date($str);
        }

        // otherwise we've manipulated the current timestamp, so serve that instead
        return date($str, self::$currentTimestamp);
    }

    public static function getTimestamp() {
        if (self::$currentTimestamp === null) {
            return time();
        }

        return self::$currentTimestamp;
    }

    public static function setCurrentDate($dateStr) {
        if (Settings::getValue("date.allow_override", false)) {
            if ($dateStr === null) {
                self::$currentTimestamp = null;
            } else {
                self::$currentTimestamp = strtotime($dateStr);
            }
        } else {
            Log::info("Attempted to set the current timestamp to [".$dateStr."] when not allowed");
        }
    }

    public static function reset() {
        self::$currentTimestamp = null;
    }

    public static function olderThan($seconds, $value) {
        $value = strtotime($value);
        return self::getTimestamp() > $value + $seconds;
    }
}
