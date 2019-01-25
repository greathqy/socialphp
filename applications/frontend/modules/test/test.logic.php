<?php
/**
 * @author greathqy@gmail.com
 * @file   模块的逻辑封装模块
 */
class testLogic extends Logic
{
    //for controller
    public function index() {
    }

    //for event
    //处理初始化事件
    public function _onEventInit(& $params) {
        /*
        $ret = $this->subUserAmount('gb', $this->uid, mt_rand(1, 1000));
        $params['subed'] = $ret;
         */
        //$params['modified_by_logic_module'] = TRUE;
    }
  
    //for other module OR general encapsulation
    public function logictest($nm) {
        if ($nm == 1) {
            return self::succ(array('msg' => 'its 1'));
        } else {
            return self::err(1024, 'the number must be 1');
        }
    }
  
    public function subUserAmount($type, $uid, $amount) {
        $userInfo = Storage::get('userinfo', $uid, array('gb', 'mb'));
        if ($type == 'gb') {
            $ret = Storage::inplaceOp('userinfo', $userInfo, 'sub@gb!positive', $amount);
        } else if ($type == 'mb') {
            $ret = Storage::inplaceOp('userinfo', $userInfo, 'sub@mb!positive', $amount);
        }

        if ($ret) {
            self::status(0);
        }
        return $ret;
    }
}
