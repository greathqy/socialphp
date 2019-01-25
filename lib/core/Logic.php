<?php
/**
 * @author greathqy@gmail.com
 * @file   逻辑处理基类
 */
abstract class Logic
{
    static public $errno = 0;
    static public $errmsg = '';

    /**
     * 事件处理总入口
     */
    public function onEvent($event, & $params) {
        $handler = '_onEvent' . ucfirst($event);
        if (method_exists($this, $handler)) {
            return $this->$handler($params);
        } else {
            throw new Exception("模块" . __CLASS__ . "监听了事件 $event, 但没有相应的处理函数!");
        }
    }

    /**
     * 设置函数处理状态
     */
    static public function status($errno = 0, $errmsg = '') {
        self::$errno = $errno;
        self::$errmsg = $errmsg;
    }
}
