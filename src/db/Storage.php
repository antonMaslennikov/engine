<?php

namespace tomdom\core\db;

class Storage extends \Rediska_Key_Hash {

    protected static $rediska;

    public function __construct() {
        return false;
    }

    public static function instance($key) {
        if (!self::$rediska) {
            self::$rediska = new \Rediska;
        }

        return new self($key);
    }

}