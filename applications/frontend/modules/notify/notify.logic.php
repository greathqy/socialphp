<?php
/**
 * @file	公告,通知,动态,系统信息模块逻辑
 * @author	cmworld@gmail.com
 */
class notifyLogic extends Logic
{
    //For controller
	static private $notify_storage = array();

	/**
	 * 艺人完成工作发送通知
	 * @param Array		$params	包含通知信息的数组
	 * @return void
	 */	
	public function starJobFinishedAlert($params){
		$uid = $params['uid'];
		$star_id = $params['star_id'];
		$link_href = linkwml('job', 'complete', array('id' => $star_id, 'jid' => $params['job_id']));
		
		$replace = array($params['star_name'],$params['job_name'],$link_href);
		$msg = $this->get_notify_template('star_work_alert',$replace);
		$hash = create_hash( array($params['star_name'],$params['job_name']));
    	$this->addAlert($uid,$msg,$hash);
	}
	
    //For other module OR general encapsulation
	/**
	 * 获取公告信息
	 *
	 * @param Integer	$limit		获取几条数据
	 * @return Array	公告信息
	 */	
	public function getNotifySys($limit = 0) {
		return $this->getNotifyList('sys' ,'list' ,$limit);
	}

	/**
	 * 获取通知信息
	 * @param Integer	$uid		用户id
	 * @param Integer	$limit		获取几条数据
	 * @return Array	通知信息
	 */
	public function getNotifyAlert($uid, $limit = 0) {	
		return $this->getNotifyList('alert' ,$uid ,$limit);
	}
	
	/**
	 * 获取动态信息
	 * @param Integer	$uid		用户id
	 * @param Integer	$limit		获取几条数据
	 * @return Array	动态信息
	 */
	public function getNotifyFeed($uid, $limit = 0) {
		return $this->getNotifyList('feed' ,$uid ,$limit);
	}
	
	/**
	 * 获得最新系统信息
	 * @param String	$type		信息类型
	 * @param String    $shardid	标示
	 * @return String
	 */	
	public function getNotifyLast($type,$shardid){

		$notify = s7::get('notify', $shardid);
		$return_str = $notify['last_'.$type];
		
		if(!$return_str){	
			$handler = 'getNotify'.ucfirst($type);
			if(!method_exists($this, $handler)){
				return '';
			}
			
			if($type == 'sys'){
				$parmes = array(1);
			}else{
				$parmes = array($shardid,1);
			}

			$notifyList = call_user_func_array(array(__CLASS__,$handler),$parmes);
			if(!empty($notifyList)){
				$row = array_shift($notifyList);
				$return_str = $row['msg'];
				$this->setNotifyLast($type,$shardid,$return_str);
			}
		}

		return $return_str;
	}
	
	/**
	 * 设置最新系统信息
	 * @param String	$type		信息类型
	 * @param String    $shardid	标示
	 * @param String    $msg		内容
	 * 
	 * return void
	 */
	public function setNotifyLast($type, $shardid, $msg){
		
		$notify = s7::get('notify', $shardid );
		if(!$notify || !is_array($notify)){
	        $notify = array(
	            'last_sys' => null,
				'last_alert' => null,
	            'last_feed' => null
			);
		}
		
		$notify[$type] = $msg;
		s7::set('notify', $shardid,$notify);
	}
	
	/**
	 * 获得列表翻页
	 * @param String	$type		信息类型
	 * @param String    $shardid	标示
	 * @param Integer	$page		第几页
	 * @return Array
	 */
	public function getNotifyPage($type, $shardid, $page) {
		
		$setting =  Configurator::get('module.frontend_notify.module.'.$type);
		$pageSize = $setting['pageSize'];
		
		$handler = 'getNotify'.ucfirst($type);
		if(!method_exists($this, $handler)){
			return array();
		}
		
		if($type == 'sys'){
			$parmes = array();
		}else{
			$parmes = array($shardid);
		}

		$notifyList = call_user_func_array(array(__CLASS__,$handler),$parmes);
		
		if(!$notifyList || !is_array($notifyList)){
			$notifyList = array();
		}
		
		$notifyList = Util::filterSchemaInfo($notifyList);

		$totalNotifyCount = sizeof($notifyList);
		$totalPages =  ceil($totalNotifyCount / $pageSize);
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		
		$start = ($page - 1) * $pageSize;
		$notifyList = array_slice($notifyList, $start, $pageSize, TRUE);

		return array(
			'total' => $totalNotifyCount,
			'pagesize' => $pageSize,
			'page' => $page,
			'notifise' => $notifyList,
		);
	}
	
	/**
	 * 添加公告信息
	 * @param Integer	$uid	用户id
	 * @param String	$msg	信息内容
	 * @param String	$hash	信息唯一标识
	 * @return void
	 */
	public function addSys($msg,$hash = '') {
		$arrinfo = array('msg'=>$msg,'hash'=>$hash,'time'=>time(),'checked'=>0);
		if($this->addNotify('sys','list',$arrinfo)){
			$this->setNotifyLast('last_sys','list',$msg);	
		}
	}
	
	/**
	 * 添加通知信息
	 * @param Integer	$uid	用户id
	 * @param String	$msg	信息内容
	 * @param String	$hash	信息唯一标识
	 * @return void
	 */
	public function addAlert($uid,$msg,$hash = '') {
		$arrinfo = array('uid' => $uid,'hash'=>$hash, 'msg'=>$msg,'time'=>time(),'checked'=>0);
		if($this->addNotify('alert',$uid,$arrinfo)){
			$this->setNotifyLast('last_alert',$uid,$msg);			
		}
	}
	
	/**
	 * 添加动态信息
	 * @param Integer	$uid	用户id
	 * @param String	$msg	信息内容
	 * @param String	$hash	信息唯一标识
	 * @return void
	 */
	public function addFeed($uid,$msg,$hash = '') {
		$arrinfo = array('uid' => $uid, 'msg'=>$msg,'hash'=>$hash,'time'=>time(),'checked'=>0);
		if($this->addNotify('feed',$uid,$arrinfo)){
			$this->setNotifyLast('last_feed',$uid,$msg);			
		}
	}

	/**
	 * 删除通知信息
	 * @param Integer	$uid	用户id
	 * @param String	$hash	信息唯一标识
	 * @return void
	 */	
	public function delAlert($uid,$hash = ''){

		$setValue = $this->getNotifyAlert($uid);

		foreach($setValue as $k => $v){
			if($v['hash'] == $hash){
				unset($setValue[$k]);
			}
		}
		
		$this->editNotify('alert',$uid,$setValue);
	}	
	
	/**
	 * 获得系统信息列表
	 * @param String	$type		获取类型
	 * @param Integer	$shardid	标示
	 * @param Integer	$limit		获取几条数据
	 * @return Array	系统信息数组
	 */
	private function getNotifyList($type ,$shardid ,$limit = 0) {
		$setting =  Configurator::get('module.frontend_notify.module.'.$type);
		$limit = $limit > 0 ? $limit : $setting['storeKeep'];
		
		$rows = s7::get('notify_'.$type, $shardid);
		if(!$rows || !is_array($rows)){
			$rows = array();
		}
	
		$notifyArr = Util::filterSchemaInfo($rows);
		
		return $limit > 0 ? array_slice($notifyArr, 0, $limit, TRUE) : $notifyArr;
	}
	
	
	/**
	 * 添加系统信息
	 * @param String	$type		信息类型
	 * @param String	$shardid	标示
	 * @param Array		$value		内容
	 * @return Boolean
	 */
	private function addNotify($type, $shardid, $value) {
	
		if(is_null($shardid) ){
			$shardid = 'list';
		}
		
		$setValue = $this->getNotifyList($type,$shardid);
		
		foreach($setValue as $k => $v){
			if($value['hash'] && ($v['hash'] == $value['hash'])){
				return FALSE;
			}
		}
		array_unshift($setValue,$value);
		
		$this->editNotify($type,$shardid,$setValue);
		return TRUE;
	}
	
    /**
 	 * 编辑系统信息
	 * @param String	$type		类型
	 * @param Array		$shardid	标识 
	 * @param Array		$value		内容
	 * @return void
	 */	
	private function editNotify($type , $shardid, $value){

		$setting =  Configurator::get('module.frontend_notify.module.'.$type);
		$keep_max = $setting['storeKeep'];
		
		if(sizeof($value) > $keep_max){
			$value = array_slice($value, 0, $keep_max, TRUE);
		}
		//s7::set('notify_'.$type,$shardid,null);
		s7::set('notify_'.$type,$shardid,$value);
	}
   
    /**
 	 * 信息模板
	 * @param String	$tpl		模板名称
	 * @param Array		$params		替换的参数数组
	 * @return String
	 */
	private function get_notify_template($tpl, $params){
    	$notity_templates = Configurator::get('module.frontend_notify.misc.notity_template');
    	$template = $notity_templates[$tpl];

    	return vsprintf($template,$params);
	}	
	
    //For event processing
	
    //处理公司升级feed
    public function _onEventCompany_upgrade(& $params) {
		$uid = $params['uid'];
		$replace = array($params['company_name'],$params['company_level']);
		$msg = $this->get_notify_template('company_upgrade',$replace);
		$hash = create_hash($replace);
    	$this->addFeed($uid,$msg,$hash);
	}
	
	//处理艺人升级feed
	public function _onEventStar_upgrade(& $params){
		$uid = $params['uid'];
		$replace = array($params['name'],$params['star_name'],$params['star_level']);
		$msg = $this->get_notify_template('star_upgrade',$replace);
		$hash = create_hash($replace);
    	$this->addFeed($uid,$msg,$hash);
	}
	
	//处理培训中心升级feed
	public function _onEventTraining_upgrade(& $params){
		$uid = $params['uid'];
		//$msg = $this->get_notify_template('star_upgrade',array($params['name'],$params['star_name'],$params['star_level']));
    	$msg = '';
    	$hash = '';
		$this->addFeed($uid,$msg,$hash);	
	}
	
	//当验收工作后 删除通知信息
	public function _onEventStar_complete_work(& $params){
		$hash = create_hash( array($params['star_name'],$params['job_name']));
		$this->delAlert($params['uid'],$hash);
		$this->setNotifyLast('last_alert',$params['uid'],'');
	}
}
