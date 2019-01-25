<?php
/**
 * @author greathqy@gmail.com
 * @file    注册器类
 */
class Registry
{
    static public $storage = array();

    static public function set($key, $value) {
        self::$storage[$key] = $value;
    }

    static public function get($key) {
        return isset(self::$storage[$key]) ? self::$storage[$key] : NULL;
    }

    static public function del($key) {
        unset(self::$storage[$key]);
    }
}
