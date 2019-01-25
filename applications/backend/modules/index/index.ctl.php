<?php
/**
 * @file 后台index模块控制器
 * @author greathqy@gmail.com
 */
class indexController extends Controller
{
	//数据管理维护
	public function index() {
		$data['function_description'] = '数据管理维护';
		$data['menu'] = 'schema';
		$data['level1'] = '数据结构名';
		$data['level2'] = '操作方法';
		$data['level3'] = '参数列表';
		$data['action'] = 'schema';

		$vars = array();

		$schemas = $this->getAllStandaloneSchemas();

		foreach ($schemas as $schema) {
			//生成动作列表
			$actions = array();
			$actions['rget'] = array(
				'intro' => '获取/编辑数据',
				'retintro' => '',
				'paras' => array('分区Id'),
				);
			$actions['set'] = array(
				'intro' => '存储数据',
				'retintro' => '',
				'paras' => array('分区Id', '数据内容'),
				);
			$actions['del'] = array(
				'intro' => '删除数据',
				'retintro' => '',
				'paras' => array('分区Id'),
				);
			$actions['add'] = array(
				'intro' => '添加数据',
				'retintro' => '',
				'paras' => array('分区Id', '数据内容'),
				);

			$vars[$schema['schema']] = array(
				'comments' => '',
				'intro' => $schema['desc'],
				'actions' => $actions,
				);
		}

		$data['javascript_vars'] = json_encode($vars);

		$this->setData($data);
	}
	
	//API调用结果
	public function api() {
	}

	//执行返回数据
	public function response() {
		$this->applyRender = FALSE;

		$type = $this->context->get('function');
		$type = $type ? $type : 'schema';

		$class = $this->context->post('class');
		$method = $this->context->post('method');
		$params = $this->context->post('paras');
		$params = is_array($params) ? $params : array();

		$params = array(
			'class' => $class,
			'method' => $method,
			'params' => $params,
			);
		$func = 'do' . ucfirst($type);
		try {
			$ret = call_user_func(array($this, $func), $params);
		} catch (Exception $e) {
			$ret = '捕获异常: ' . $e->getMessage();
		}

		if (is_array($ret) || is_object($ret)) {
			$ret = json_encode($ret);
		}
		echo $ret;
		exit;
	}

	//做数据操作
	public function doSchema($params) {
		$schema = $params['class'];
		$method = $params['method'];
		$params = $params['params'];

		$parameter[] = $schema;
		foreach($params as $ele) {
			$parameter[] = $ele;
		}

		$ret = call_user_func_array(array('s7', $method), $parameter);
		return $ret;
	}

	//做api调用操作
	public function doApi($params) {
	}

	//------------------- 支撑功能函数 ----------------------
	//获得所有单独使用的schema名
	public function getAllStandaloneSchemas() {
		$config = Configurator::$generalConf['manifest']['schema_module_mapping'];
		$ret = array();

		foreach ($config as $schemaName => $appModule) {
			$schemaInfo = s7::getSchema($schemaName);
			$schemaInfo = $schemaInfo['struct'];

			if (!isset($schemaInfo['__!standalone__']) || 
				(isset($schemaInfo['__!standalone__']) && $schemaInfo['__!standalone__'] == FALSE)
			) {
				$ret[] = array(
					'schema' => $schemaName,
					'desc' => isset($schemaInfo['desc']) ? $schemaInfo['desc'] : '',
					'type' => $schemaInfo['type'],
					'storage' => $schemaInfo['storage'],
					);
			}
		}

		return $ret;
	} 

	/**
	 * 反射获得一个模块的api信息
	 *
	 * @param String $app	应用名
	 * @param String $module 模块名
	 * @return Array
	 */
	public function reflectionAnModule($app, $module) {
	}
}
