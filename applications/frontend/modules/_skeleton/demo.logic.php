<?php
/**
 * @file	xx模块逻辑
 * @author	xx
 */
class demoLogic extends Logic
{
    //For controller
    public function xxx() {
    }

    //For other module OR general encapsulation

    //For event processing
    //处理初始化事件
    public function _onEventInit(& $params) {
        $params['modified_by_logic_module'] = TRUE;
		return TRUE;
    }
}
