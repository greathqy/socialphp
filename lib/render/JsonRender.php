<?php
/**
 * @author greathqy@gmail.com
 * @file   json渲染类
 */
class JsonRender extends AbstractRender
{
    public function render() {
        echo json_encode($this->result);
    }
}
