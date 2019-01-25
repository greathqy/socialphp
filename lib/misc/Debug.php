<?php
/**
 * @author greathqy@gmail.com
 * @file   调试类
 */
class Debug
{
    private function __construct() {
    }

    /**
     * 输出调试变量
     *
     * @param Mixed $mixed  变量
     * @return NULL
     */
    static public function dump() {
        $args = func_get_args();
        if (count($args) == 1) {
            $arg = $args[0];
            $instance = Registry::get('__CURRENT_CONTROLLER');
            if (is_array($arg) || is_object($arg)) {
                $data = print_r($arg, TRUE);
				$data = "<pre>" . $data . "</pre>";
            } else {
                ob_start();
                var_dump($arg);
                $data = ob_get_contents();
                ob_end_clean();
            }
            self::appendOutput($instance, $data);
        } else {
        	 
            foreach ($args as $arg) {
                self::dump($arg);
            }
        }
    }

    /**
     * 打印backtrace
     */
    static public function backtrace() {

        $instance = Registry::get('__CURRENT_CONTROLLER');
        ob_start();
        debug_print_backtrace();
        $debug = ob_get_contents();
        ob_end_clean();
		$debug = "<pre>" . $debug . "</pre>";
        self::appendOutput($instance, $debug);
    }

    /**
     * 附加信息到debug输出
     *
     * @param Object    $instance 控制器实例
     * @param String    $data   调试信息
     * @return Boolean
     */
    static private function appendOutput($instance, $data) {
        if (isset($instance->view->result['__DEBUG_DATA__'])) {
            $instance->view->result['__DEBUG_DATA__'] .= '<br />' . $data;
        } else {
            $instance->view->result['__DEBUG_DATA__'] = $data;
        }

        return TRUE;
    }
}

class Timer
{
}
