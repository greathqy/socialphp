<?php
/**
 * @author cmworld@gmail.com
 * @file   系统信息,公告,通知,动态Controller
 */
class notifyController extends Controller
{
    //应用入口
    public function index() {
    	
		$data = array();
		$type = $this->context->get('type');
		$type = in_array($type,array('sys','alert','feed')) ? $type : 'sys';			
		$page = $this->context->get('page');
		$page = $page ? $page : 1;	

        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];		
		$shardid = $type == 'sys' ? 'list' : $uid;
		
		$notifyList = Module::call('notify', 'getNotifyPage', array($type,$shardid,$page));

		$options = array(
			'page_size' => $notifyList['pagesize'],
			'item_total' => $notifyList['total'],
			'current_page' => $notifyList['page'],
		);
		
		$pagination = Pagination::textual($options, 'notify', 'index', array('type'=>$type));
		
		$data['__title__'] = '系统信息';
		//$data['userinfo'] = $userInfo;
		$data['notifylist'] = $notifyList['notifise'];
		$data['pagination'] = $pagination;
		$this->setTemplate($type);
		$this->setData($data);
    }
    
    //查看详情
    /*public function info (){
    	$index = $this->context->get('index');
    	$type = $this->context->get('type');
    	
    	if($index || in_array($type,array('sys','alert','feed'))){
    		throw new Exception("公告不存在, 请不要构造uri请求游戏!");
    	}
    	
    	$notifyList = Module::call('notify', 'getNotify'.ucfirst($type));
    	if(!isset($notifyList[$index])) {
    		throw new Exception("公告不存在, 请不要构造uri请求游戏!");
    	}
    	
    	$data['notifyinfo'] = $notifyList[$index];
    	$setting = Configurator::get('module.frontend_notify.module.'.$type);
    	$data['__title__'] = $setting['title'];
    	$this->setData($data);
    }*/   
}
