<?php
/**
 * @author greathqy@gmail.com
 * @file    事件类，注册事件，触发事件。时间处理基类
 */
class Event
{
    static public $events = array();

    /**
     * 注册事件
     *
     * @param String $event 事件名
     * @param String $app   负责处理的应用名
     * @param String $module 负责处理的模块名
     * @return Boolean
     */
    static public function register($event, $app, $module) {
        if (!isset(self::$events[$event])) {
            self::$events[$event] = array();
        }
        $key = $app . '_' . $module;
        if (!isset(self::$events[$event][$key])) {
            self::$events[$event][$key] = array(
                'app' => $app, 
                'module' => $module
            );
        }

        return TRUE;
    }

    /**
     * 触发事件
     *
     * @param String $event 事件名
     * @param String $params 事件参数
     * @return Boolean
     */
    static public function trigger($event, & $params) {
        if (isset(self::$events[$event]) && self::$events[$event]) {
            foreach (self::$events[$event] as $key => $destination) {
                $app = $destination['app'];
                $module = $destination['module'];
                Module::setApp($app);
                $logicInstance = Module::instance($module);

                $result = call_user_func_array(array($logicInstance, 'onEvent'), array($event, & $params));
                if ($result === FALSE) {
                    break;  //某个回调返回false，终止继续执行
                }
            }
        }

        return TRUE;
    }

    /**
     * 注册manifest配置的事件
     */
    static public function init() {
        $conf = isset(Configurator::$generalConf['manifest']) ? Configurator::$generalConf['manifest'] : array();
        $conf = isset($conf['events']) ? $conf['events'] : array();

        foreach ($conf as $app => $moduleConf) {
            foreach ($moduleConf as $module => $events) {
                foreach ($events as $event) {
                    self::register($event, $app, $module);
                }
            }
        }

        return TRUE;
    }

    //获得事件监听者列表
    static public function getEventListener($event) {
        return TRUE;
    }

    //移除事件监听者
    static public function removeEventListener($event, $app, $module) {
        return TRUE;
    }
}

//事件处理基础类
abstract class EventHandler
{
	public function onEvent($event, & $params) {
        $handler = '_onEvent' . ucfirst($event);
        if (method_exists($this, $handler)) {
            return $this->$handler($params);
        } else {
            throw new Exception("模块" . __CLASS__ . "监听了事件 $event, 但没有相应的处理函数!");
		}
	}
}
