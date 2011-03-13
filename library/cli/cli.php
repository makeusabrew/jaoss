<?php

abstract class Cli {

    const MIN_ARG_COUNT = 2;

    public static function factory($argc, $argv) {
        if ($argc < self::MIN_ARG_COUNT) {
            throw new Exception(
                "Insufficient arguments",
                1
            );
        }
    }
}
