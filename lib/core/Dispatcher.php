<?php
/**
 * @author greathqy@gmail.com
 * @file   请求分发类
 */
class Dispatcher
{
    static public $responseType = 'wml';

    static public function setDefaultResponseType($responseType = 'json') {
        self::$responseType = $responseType;
    }
    
    static public function dispatch($module, $action, $context = NULL, $passby = array()) {
        $controllerClassName = $module . 'Controller';
        $routerOption = Registry::get('routerOption');
        $projDir = $routerOption['proj_dir'];
        $appName = $routerOption['app_name'];
        $controllerFile = $projDir . DS . 'applications' . DS . $appName . DS . 'modules' . DS . $module . DS . $module . '.ctl.php';
		if ($action[0] == '_') {
			throw new Exception("$module action $action not found", Error::ERROR_404_NOT_FOUND);
		}
        if (file_exists($controllerFile)) {
            if (!in_array($controllerFile, get_included_files())) {
                include($controllerFile);
            }
        } else {
			throw new Exception("$module module is not exists", Error::ERROR_404_NOT_FOUND);
        }
        if (!class_exists($controllerClassName)) {
			throw new Exception("$module module class is not exists", Error::ERROR_404_NOT_FOUND);
        }
        if (!$context instanceof Context) {
            $newContext = new Context();
        } else {
            $newContext = clone $context;
        }
        $newContext->uri['app'] = $appName;
        $newContext->uri['module'] = $module;
        $newContext->uri['action'] = $action;
        $controllerInstance=  new $controllerClassName($newContext);
        if (!isset($controllerInstance->responseType) || NULL === $controllerInstance->responseType) {
            $controllerInstance->responseType = self::$responseType;
        }
        $viewClassName = ucfirst($controllerInstance->responseType) . 'Render';
        $controllerInstance->view = new $viewClassName;
        if (is_array($passby) && $passby) {
            $controllerInstance->view->result['result'] = array_merge($controllerInstance->view->result['result'], $passby);
        }
        if ('wml' === $controllerInstance->responseType || 'html' === $controllerInstance->responseType) {
            $controllerInstance->view->templateDir = $projDir . DS . 'applications' . DS . $appName . DS . 'modules' . DS . $module . DS . 'views' . DS . $controllerInstance->responseType . DS;
            $controllerInstance->view->template = $action;
            $controllerInstance->view->layoutDir = $projDir . DS . 'applications' . DS . $appName . DS . 'modules' . DS . '_layout' . DS;
        }

        $controllerInstance->view->context = $controllerInstance->context;
        Registry::set('__CURRENT_CONTROLLER', $controllerInstance);
        if (method_exists($controllerInstance, $action)) {
            call_user_func(array($controllerInstance, $action));
        } else {
            throw new Exception ("$module module action not found", Error::ERROR_404_NOT_FOUND);
        }

        return $controllerInstance;
    }
}
