<?php
/**
 * @author huangqingyun
 * @file   工厂类
 */
class Factory
{
    static $created = array();

    private function __construct() {
    }

    //单例构建
    static public function create($className) {
        $obj = NULL;
        if (isset(self::$created[$className]) && self::$created[$className]) {
            $obj = self::$created[$className];
        } else {
			$args = func_get_args();
            $obj = call_user_func_array(array(__CLASS__, 'doCreate'), $args);
            self::$created[$className] = $obj;
        }

        return $obj;       
    }

    //重复构建
    static public function alwayscreate($className) {
        $obj = call_user_func_array(self::doCreate, func_get_args());
        return $obj;
    }

    /**
     * 实际构造
     */
    static private function doCreate($className) {
        $args = func_get_args();
        $nums = func_num_args();
        $obj = NULL;
        if ($nums == 1) {
            $obj = new $className();
        } else if ($nums == 2) {
            $obj = new $className($args[1]);
        } else if ($nums == 3) {
            $obj = new $className($args[1], $args[2]);
        } else if ($nums == 4) {
            $obj = new $className($args[1], $args[2], $args[3]);
        } else if ($nums == 5) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4]);
        } else if ($nums == 6) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4], $args[5]);
        } else if ($nums == 7) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
        } else if ($nums == 8) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
        } else if ($nums == 9) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
        } else if ($nums == 10) {
            $obj = new $className($args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]);
        } else {
            exit('opps! we dont support args more than 10 :(');
        }

        return $obj;
    }
}
