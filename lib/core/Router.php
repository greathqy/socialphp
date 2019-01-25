<?php
/**
 * @author huangqingyun
 * @file   从GET/POST请求中路由出目标
 */
abstract class Router
{
    static public function route() {
        return array(
            'module' => self::$defaultModule,
            'action' => self::$defaultAction,
            );
    }

    static public $defaultModule = 'index';
    static public $defaultAction = 'index';

    static public function setDefaultModule($module) {
        self::$defaultModule = $module;
    }

    static public function setDefaultAction($action) {
        self::$defaultAction = $action;
    }
}

class HttpRouter extends Router
{
    //路由材料来源
    static public $source = 'POST';
    
    static public function setSource($source) {
        $allowed = array(
            'GET',
            'POST',
            'REQUEST',
            );
        if (!in_array($source, $allowed)) {
            return FALSE;
        }
        self::$source = $source;
        return TRUE;
    }

    //路由
    static public function route() {
        $uri = array();
        $req = $_POST;
        if (self::$source == 'GET') {
            $req = $_GET;
        } else if (self::$source == 'POST') {
            $req = $_POST;
        } else if (self::$source == 'REQUEST') {
            $req = $_REQUEST;
        }
        if (isset($req['m'])) {
            $uri['module'] = $req['m'];
        }
        if (isset($req['a'])) {
            $uri['action'] = $req['a'];
        }
        $uri['module'] = isset($uri['module']) ? $uri['module'] : self::$defaultModule;
        $uri['action'] = isset($uri['action']) ? $uri['action'] : self::$defaultAction;
        $pattern = '/^[a-zA-Z0-9\-_\.]+$/';
        if (!preg_match($pattern, $uri['module'])) {
            throw new Exception("module name illegal {$uri['module']}");
        }
        if (!preg_match($pattern, $uri['action'])) {
            throw new Exception("action name illegal {$uri['action']}");
        }

        return $uri;
    }
}

class AMFRouter extends Router
{
    static public function route() {
        return array();
    }
}
