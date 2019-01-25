<?php
/**
 * @author greathqy@gmail.com
 * @file   render抽象基类
 */
abstract class AbstractRender
{
    public $result = array(
        'errno' => 0,
        'msg' => '',
		'errors' => array(),
        'result' => array(), 
        );
    public $context;

    abstract public function render();

	/**
	 * 包含入partial模板文件
	 *
	 * @param String	$partialName	部分模板文件名 省略.php
	 * @param String	$responseType	返回内容类型 html wml等
	 * @return Boolean
	 */
	public function include_partial($partialName, $responseType = 'wml') {
		$conf = Registry::get('routerOption');
		$module = $conf['module'];

		$path = Module::getModuleFilePath($module, "view.{$responseType}");
		$file = $path . $partialName . '.php';

		$data = $this->result['result'];
		unset($this->result['result']);
        extract($this->result);
		$this->result['result'] = $data;
		extract($data);

		include $file;

		return TRUE;
	}
}
