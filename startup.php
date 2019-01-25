<?php
/**
 * @author greathqy@gmail.com
 * @file   框架初始化文件，定义常量
 *         加载各常用库文件
 */
define('DS', DIRECTORY_SEPARATOR);
define('SYS_ROOT', dirname(__FILE__));
define('PROJ_LIB', SYS_ROOT . DS . 'lib');

require PROJ_LIB . DS . 'core' . DS . 'ClassLoader.php';
require PROJ_LIB . DS . 'core' . DS . 'Configurator.php';

//Load manifest configuration file, and register module listened events
$manifestPath = SYS_ROOT . DS . 'configs' . DS . 'manifest.cfg.php';
Configurator::loadNamedConfig('manifest', $manifestPath);

$classFileMapping = Configurator::$generalConf['manifest']['class_file_mapping'];
//Class auto loading
ClassLoader::setFileMapping($classFileMapping);
spl_autoload_register(array('ClassLoader', 'mappingLoader'));

Event::init();

//加载常用类库/函数库
include(PROJ_LIB . DS . 'misc' . DS . 'common.php');

//Handle timezone setting
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Shanghai');
}

//Deregister global
if (ini_get('register_globals')) {
    $globals = array($_REQUEST, $_SESSION, $_SERVER, $_FILES, $_GET, $_POST);
    foreach ($globals as $global) {
        foreach (array_keys($global) as $key) {
            unset($$key);
        }
    }
    unset($globals);
}

//Handle magic quotes gpc
if (ini_get('magic_quotes_gpc')) {
    function cleangpc(& $arr) {
        foreach ($arr as $key => & $val) {
            if (is_array($val)) {
                cleangpc($val);
            } else {
                $val = stripslashes($val);
            }
        }
    }
	cleangpc($_GET);
	cleangpc($_POST);
	cleangpc($_REQUEST);
	cleangpc($_COOKIE);
    unset($cleans);
}

//加载其他需要的文件
load_helper('common');

if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
}
//Handle error & exception
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

function errorHandler($errno, $errstr, $errfile, $errline) {
    if (ENV == 'DEV') {
        $msg = "发生错误拉 :) <br />";
        $msg .= "错误码: $errno <br />";
        $msg .= "错误文件: $errfile <br />";
        $msg .= "错误行数: $errline <br />";
        $msg .= "错误描述: $errstr <br />";
		//$msg .= print_r(debug_backtrace(), TRUE);
    } else {
        $msg = $errstr;
    }
    $controllerInstance = Registry::get("__CURRENT_CONTROLLER");
    if ($controllerInstance) {
        $controllerInstance->forwardAction('error', 'index', array('__sys_error__' => $msg));
    } else {
        Dispatcher::dispatch('error', 'index', NULL, array('__sys_error__' => $msg));
    }
}

function exceptionHandler($e) {
    $code = $e->getCode();
    $msg  =  $e->getMessage();
	$msg = '<br />' . $msg;
    $module = 'error';
    $action = 'index';
	$params = array('__sys_error__' => $msg);
	if ($code == Error::ERROR_404_NOT_FOUND) {
		$action = 'error_404';
		$params = array();
	}
    $controllerInstance = Registry::get("__CURRENT_CONTROLLER");
    if ($controllerInstance) {
        $controllerInstance->forwardAction($module, $action, $params);
    } else {
        Dispatcher::dispatch($module, $action, NULL, $params);
    }
}
