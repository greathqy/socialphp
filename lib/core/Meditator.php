<?php
/**
 * @author greathqy@gmail.com
 * @file   相当于front controller, 负责路由解析, 分发请求, 
 *         初始化配置信息等工作
 */
class Meditator
{
    private $envMode = 'DEV';

    static private $_machines = array(
        'DEV' => array(
                'KY-PC40', 'ERIC-PC', 'office-test1-249','KY-PC251',
            ),
        'TEST' => array(
            ),
        'PROD' => array(
            ),
        );

    //路由方式
    private $routerName = 'http';

    //默认呈现类型
    private $defaultRender = 'json';

    public function __construct() {
    }

    /**
     * 设置渲染类型
     */
    public function setDefaultRender($renderStyle) {
        $this->defaultRender = $renderStyle;
        return $this;
    }

    /**
     * 设置路由类
     */
    public function setRouter($routerName) {
        $this->routerName = $routerName;
        return $this;
    }

    /**
     * 执行一个程序
     *
     * @param String $appDir 应用目录 
     */
    public function service($options) {
        HttpRouter::setSource('REQUEST');
        $uri = HttpRouter::route();

        $module = $uri['module'];
        $action = $uri['action'];

        $options['module'] = $module;
        $options['action'] = $action;

        $this->init($options);

		Dispatcher::$responseType = $this->defaultRender;
        Dispatcher::dispatch($module, $action);
    }

    //初始化环境
    public function init($options, $envMode = NULL) {
        if ($envMode) {
            $this->envMode = $envMode;
        } else {
            $hostName = php_uname('n');
            $envMode = 'PROD';

            foreach (self::$_machines as $env => $conf) {
                if (in_array($hostName, $conf)) {
                    $envMode = $env;
                    break;
                }
            }
            $this->envMode = $envMode;
        }

        $options['env'] = $this->envMode;

        defined("ENV") || define("ENV", $this->envMode);

        $this->initConfig($options);

        return TRUE;
    }

    //初始化配置信息
    private function initConfig($options) {
        Registry::set('routerOption', $options);
        Configurator::init($options);
    }
}
