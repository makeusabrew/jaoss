<?php

class Utils {
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
		$str = $days." day";
		if ($days != 1) {
			$str .= "s";
		}
		$str .= " ago";
		return $str;
	}
}
