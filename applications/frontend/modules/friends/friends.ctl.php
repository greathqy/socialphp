<?php
/**
 * @author cmworld@gmail.com
 * @file   好友系统
 */
class friendsController extends Controller
{
    //应用入口
    public function index() {
 		$page = $this->context->get('page');
		$page = $page ? $page : 1;
		   	
        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		
        $friendsList = Module::call('friends', 'getFriendsPage', array($uid,$page));

		$options = array(
			'page_size' => $friendsList['pagesize'],
			'item_total' => $friendsList['total'],
			'current_page' => $friendsList['page'],
		);
		
		$pagination = Pagination::textual($options, 'friends', 'index');
		        
        $data['__title__'] = '好友列表';
        $data['friendsList'] = $friendsList['friends'];
        $data['friendstotal'] = $friendsList['total'];
		$data['pagination'] = $pagination;
        $this->setData($data);
    }
    
    function add(){
    	//$fid = 8848;//黄青云',
		//$fid = 8849;//徐炜',
		//$fid = 8850; //刘健平',
		$fid = 8851; //吴志坚'
		
        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];	
 		$isFriend = Module::call('friends', 'isFriend', array($uid,$fid));
 		
 		if(!$isFriend){
 			$succ = Module::call('friends', 'addFriend', array($uid,$fid));
 			if($succ){
 				$check = Module::call('friends', 'addFriend', array($fid,$uid));
 				
 				if($check){
 					//...
 				}
 				
 			}
 			
 			if(!$check){
 				//对方添加你失败
 			}
 			
 			if(!$succ){
 				//添加对方失败
 			}
 		}
    }
    
    function addfriend(){
 		$fid = (int)$this->context->get('fid');
 		$userDetail = s7::get('userinfo',$fid);
 		if(!$userDetail || !is_array($userDetail)){
 			throw new Exception("指定的用户不存在!");
 		}

        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];	
 		$isFriend = Module::call('friends', 'isFriend', array($uid,$fid));
 		
 		if(!$isFriend){
 		 	$succ = Module::call('friends', 'addFriend', array($uid,$fid));
 			if($succ){
 				$check = Module::call('friends', 'addFriend', array($fid,$uid));
 				
 				if($check){
 					//...
 				}
 				
 			}
 			
 			if(!$check){
 				//对方添加你失败
 			}
 			
 			if(!$succ){
 				//添加对方失败
 			}
 		}
 		
		$data['__title__'] = '添加好友';
		$data['isFriend'] = $isFriend;
		$this->setData($data);
    }

	//好友详情
	public function detail() {
		$fid = (int)$this->context->get('fid');
	 	$friendDetial = Module::call('friends', 'getFriendDetialByFid', array($fid));
 		if(!$friendDetial){
 			throw new Exception("好友不存在, 请不要构造uri请求游戏!");
 		}
		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
 		$isFriend = Module::call('friends', 'isFriend', array($uid,$fid));
		
 		$starLists = Module::call('star', 'getStarLists', array($fid));
 		$starsDisplay = array();
		foreach($starLists['stars'] as $star){
			$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));
			
			if ($star['jobinfo']['expire']){
				$percent = 100;
				if (isset($star['jobinfo']['percent'])) {
					$percent = $star['jobinfo']['percent'];
				}
				
				$percent = round($percent / 100, 2);
				$jobInfo = Module::call('job', 'getJobInfo', array($star['jobinfo']['jobid']));
				$baseCompanyGb = $jobInfo['company_cash'];
				$star['jobinfo']['gb_add'] = $baseCompanyGb * $percent;
			}
			
			$starsDisplay[] = $star;
		}

		$data['__title__'] = '好友详情';
		$data['friendDetial'] = $friendDetial;
		$data['isFriend'] = $isFriend;
		$data['stars'] = $starsDisplay;	
		$this->setData($data);
	}
}
