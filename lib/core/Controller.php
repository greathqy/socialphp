<?php
/**
 * @author greathqy@gmail.com
 * @file   抽象控制器类
 */
abstract class Controller
{
    //是否被子类调用parent::__construct()
    public $constructed = FALSE;

    //输出类型
    public $responseType = NULL;
    protected $applyRender = TRUE;
	
    //used with __set
    private $_responseType = NULL;
    private $_layout = NULL;
    private $_template = NULL;

    //请求上下文, GET POST SERVER等数组封装
    public $context;

    //权限和字段验证规则
    private $acl = array();
    private $dtds = array();

    //Controller的view对象
    public $view; 
    
    public function __construct($context) {
        $this->constructed = TRUE;

        $this->context = $context;
    }

    public function __destruct() {

        if ($this->applyRender) {
			//构造返回链接
			$prev = $this->context->get('_prev');
			if ($prev) {
				$prev = urldecode($prev);
			}
			$prev = $prev ? $prev : '/';
			$data['link_prev'] = $prev;
			$this->setData($data);

            $this->view->render();
        }
    }

    //设置返回数据
    public function setData($arr, $msg = NULL) {
        if (is_array($arr) && $arr) {
            $this->view->result['result'] = array_merge_recursive($this->view->result['result'], $arr);
        }
        if (!is_null($msg)) {
            $this->view->result['msg'] = $msg;
        }

        return TRUE;
    }

    //设置返回错误
    public function setErr($errno = NULL, $msg = NULL, $result = array()) {
        if (!is_null($errno)) {
            $this->view->result['errno'] = $errno;
        }
        if (!is_null($msg)) {
            $this->view->result['msg'] = $msg;
        }
        if (is_array($result) && $result) {
            $this->view->result['result'] = array_merge_recursive($this->view->result['result'], $result);
        }

        return TRUE;
    }

    //单独设置错误号
    public function setErrNo($errno = 0) {
        $this->view->result['errno'] = $errno;

        return TRUE;
    }

    /**
     * Magic function
     */
    public function __set($name, $value) {
        if ($name == '_responseType') {
            $this->setResponseType($value);
        } else if ($name == '_layout') {
            $this->setLayout($value);
        } else if ($name == '_template') {
            $this->setTemplate($value);
        }
    }

    /**
     * 设置控制器的responseType
     *
     * @param String    $type   返回类型
     * @return Boolean
     */
    protected function setResponseType($responseType) {
        if ($responseType != $this->responseType) {
            $class = ucfirst($responseType) . 'Render';
            $view = new $class();
            $view->result = $this->view->result;
            $view->context = $this->context;
            $this->view = $view;
        }
    }

    /**
     * 设置view使用的模版文件
     *
     * @param String    $templateName   模版名
     */
    protected function setTemplate($templateName) {
        $this->view->template = $templateName;
    }

    /**
     * 设置view使用的layout
     *
     * @param String    $layout 布局文件名
     */
    protected function setLayout($layout) {
        $this->view->layout = $layout;
    }

    /**
     * 验证输入数据合法性
     */
    protected function validate($action = NULL) {
        if (is_null($action)) {
            $action = $this->context->uri['action'];
            $action = '@' . $action;
        }
        $app = $this->context->uri['app'];
        $module = $this->context->uri['module'];
        $key = "module.{$app}_{$module}.dtds.$action";
        $dtds = Configurator::get($key);
        $dtds = $dtds ? $dtds : array();
		if (empty($dtds)) {
			$errors = array();
		} else {
			Validator::setDataSource($this->context);
			$errors = Validator::validate($dtds);
		}

        return $errors;
    }

    /**
     * 判断是否有POST请求，验证通过
     */
    protected function handleActionSubmit($action = NULL) {
        if ($_POST) {
            $errors = $this->validate($action);
            if (!$errors) {
                if (is_null($action)) {
                    $handler = $this->context->uri['action'];
                } else {
                    $handler = str_replace('@', '', $action);
                }
                $handler = '_submit_' . $handler;
                if (method_exists($this, $handler)) {
                    $this->$handler();
                }

                return TRUE;
            } else { //设置错误
                $this->view->result['errors'] = $errors;
                $this->setErrNo(-1);
            }
        }

        return FALSE;
    }

    //返回伪造的用户信息
    public function getUserInfo() {
		$users = array();
        $users[8848] =  array(
            'uid' => 8848,
            'nickname' => '黄青云',
            'oid' => 'E464X890012T',
            );
		$users[8849] = array(
            'uid' => 8849,
            'nickname' => '徐炜',
            'oid' => 'E464X890012B',
			);
		$users[8850] = array(
            'uid' => 8850,
            'nickname' => '刘健平',
            'oid' => 'E464X890012C',
			);
		$users[8851] = array(
            'uid' => 8851,
            'nickname' => '吴志坚',
            'oid' => 'E464X890012D',
			);
		
		if (isset($_SESSION['seluid'])) {
			$uid = (int) $_SESSION['seluid'];
		} else {
			//modify by liujp 自己做测试用
			header('Location:/userinit.php');
			$uid = 8848;
		}

		return $users[$uid];
    }

    /**
     * 重定向到另一个action
     */
    protected function redirectAction($module, $action, $args = array()) {
        $dest = linkhtml($module, $action, $args);
        header("Location: $dest");
    }

    /**
     * 继续处理另一个action
     *
     * @param String    $module 模块名
     * @param String    $action 方法名
     * @param Array     $passby 附带的数据信息
     */
    public function forwardAction($module, $action, $passby = array()) {
        if ($module != $this->context->uri['module']) { //Redispatch to another module
            $this->applyRender = FALSE;

            $data = $this->view->result;
            $controllerInstance = Dispatcher::dispatch($module, $action);
            $errcode = $controllerInstance->view->result['errno'];
            $msg = $controllerInstance->view->result['msg'];
            $controllerInstance->view->result = array_merge_recursive($controllerInstance->view->result, $data);
            $controllerInstance->view->result['errno'] = $errcode;
            $controllerInstance->view->result['msg'] = $msg;
            if (is_array($passby) && $passby) {
                $controllerInstance->view->result['result'] = array_merge($controllerInstance->view->result['result'], $passby);
            }
        } else { //Still in this module
            $this->context->uri['action'] = $action;
            $this->$action();
        }
    }
}
