<?php
/**
 * @author greathqy@gmail.com
 * @file   html渲染类
 */
class HtmlRender extends AbstractRender
{
    public $layoutDir;

    public $templateDir;

    public $layout = 'base';

    public $template;

    /**
     * 渲染result
     */
    public function render() {
		$data = $this->result['result'];
		unset($this->result['result']);
        extract($this->result);
		extract($data);

        if (isset($this->layout) && $this->layout) {
            include($this->layoutDir . $this->layout . '.php');
        } else {
            include($this->templateDir . $this->template . '.php');
        }
    }

    /**
     * 填充字段初始值
     *
     * @param String    $fieldName  字段名
     * @param String    $default    默认值
     * @param String
     */
    public function setField($fieldName, $default = '') {
        $val = $this->context->post($fieldName);
        $value = !is_null($val) ? $val : $default;
        return $value;
    }
}
