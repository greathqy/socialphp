<?php
/**
 * @author  greathqy@gmail.com
 * @file    模块支持类, 检查模块是否存在
 *          模块间调用等
 *          模块逻辑文件基础
 */
class Module
{
    static public $app = NULL;
	static public $instances = array();

    static public function setApp($app) {
        self::$app = $app;
    }
    
    /**
     * 获得模块相应文件的文件路径
     *
     * @param String $module 模块名
     * @param String $type  config/logic/control
     */
    static public function getModuleFilePath($module, $type = 'config') {
        self::init();
        $app = self::$app;
        $baseDir = SYS_ROOT . DS . 'applications' . DS . $app . DS . 'modules' . DS . $module . DS;
        if ($type == 'config') {
            $file = $baseDir . 'conf.php';
        } else if ($type == 'logic') {
            $file = $baseDir . $module . '.logic.php';
        } else if ($type == 'control') {
            $file = $baseDir . $module . '.ctl.php';
		} else {
			$arr = explode('.', $type);
			if (sizeof($arr) != 2) {
				throw new Exception('非法module文件类型');
			}
			$dir = $arr[1];	//wml or html or ....
			$file = $baseDir . 'views' . DS . $dir . DS;
		}

        return $file;
    }

    /**
     * 初始化$app静态变量
     *
     * @param String $app 应用名
	 * @return Boolean
     */
    static private function init($app = NULL) {
        static $inited = FALSE;
        if ($inited) return TRUE;

        if ($app) {
            self::setApp($app);
            $inited = TRUE;
        }
        if (!self::$app) {
            $routerOption = Registry::get('routerOption');
            self::$app = $routerOption['app_name'];      //side effect?
            $inited = TRUE;
        }

        return $inited;
    }

    /**
     * 模块是否存在
     *
     * @param String $module 模块名
     * @return Boolean
     */
    static public function exists($module) {
        $file = self::getModuleFilePath($module, 'config');
        $flag = file_exists($file);

        return $flag;
    }

    /**
     * 调用其他模块的函数
     */
    static public function call($module, $action, $args) {
        $logicInstance = self::instance($module);
        $result = call_user_func_array(array($logicInstance, $action), $args);

        return  $result;
    }

    /**
     * 实例化一个模块逻辑对象
     */
    static public function instance($module) {
        self::load($module, array('logic'));
        $key = self::$app . '_' . $module;
        if (!isset(self::$instances[$key])) {
            $className = $module . 'Logic';
            if (!class_exists($className)) {
                throw new Exception("$className not found");
            }
            self::$instances[$key] = new $className();
        }

        return self::$instances[$key];
    }

    /**
     * 加载模块相关文件
     * @param String $module 模块名
     * @param Array  $files  加载哪几种文件
     * @return Boolean
     */
    static public function load($module, $files = array('logic')) {
        self::init();
        if (!self::exists($module)) {
            $app = self::$app;
            throw new Exception("{$app}'s module: {$module} not found");
        }
        foreach ($files as $file) {
            if ($file == 'config') {
                Configurator::setApp(self::$app);
                Configurator::loadModuleConfig($module);
                Configurator::restoreConf();
            } else {
                $filePath = self::getModuleFilePath($module, $file);
                if (!in_array($filePath, get_included_files())) {
                    include($filePath);
                }
            }
        }

        return TRUE;
    }
}

/**
 * 模块逻辑基类
 */
abstract class Logic
{
    //每次逻辑调用的返回值
    static private $ret = array(
        'errno' => 0,           //0表示调用成功
        'result' => array(),    //执行结果
        );

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
     * 设置逻辑处理失败状态
     *
     * @param Integer $errno 错误码
     * @param String  $errmsg 错误描述
     */
    static public function err($errno = -1, $errmsg = '') {
        self::$ret['errno'] = $errno;
        self::$errmsg = $errmsg;
    }

    /**
     * 设置逻辑处理成功状态
     *
     * @param Array $result 处理结果
     * @return Array
     */
    static public function succ($result) {
        self::$ret = array(
            'errno' => 0,
            'result' => $result,
            );

        return self::$ret;
    }

    /**
     * 获取最后一次处理状态
     *
     * @return Array
     */
    static public function getLastStatus() {
        return self::$ret;
    }
}
