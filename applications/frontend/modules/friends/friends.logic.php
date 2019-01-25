<?php
/**
 * @file	friends模块逻辑
 * @author	cmworld@gmail.com
 */
class friendsLogic extends Logic
{
	/**
	 * 获取好友ID列表
	 * @param Integer $uid 用户id
	 * return Array
	 */
	public function getFriends($uid) {
		$user_friends = $this->getFriendsData($uid);
		if (!$user_friends) {
			return array();
		}
		return $user_friends['friends'];
	}
	
	/**
	 * 获取好友详细列表
	 * @param Integer $uid 用户id
	 * return Array
	 */	
	public function getFriendsList($uid) {
		
		$friendsList = array();
		
		$friends = $this->getFriends($uid);

		foreach ($friends as $fid => $refresh) {
			$friendDetial  = $this->getFriendDetialByFid($uid,$fid);
			if(empty($friendDetial)) {
				continue;
			}
			
			$friendsList[$fid] = $friendDetial;
		}
			
		return $friendsList;
	}
	
	/**
	 * 获取好友列表翻页
	 * @param Integer $uid  用户id
	 * @param Integer $page 页数
	 * return Array
	 */	
	public function getFriendsPage($uid, $page) {
		$friendsList = array();
		
		$pageSize =  Configurator::get('module.frontend_friends.misc.friend_page_size');
		$friends = $this->getFriends($uid);
			
		foreach ($friends as $fid => $refresh) {
			$friendDetial  = $this->getFriendDetialByFid($fid);
			if(empty($friendDetial)) {
				continue;
			}
			
			$friendsList[$fid] = $friendDetial;
		}
			
		$totalNotifyCount = sizeof($friendsList);
		$totalPages =  ceil($totalNotifyCount / $pageSize);
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		
		$start = ($page - 1) * $pageSize;
		$friendsList = array_slice($friendsList, $start, $pageSize, TRUE);
	
		return array(
			'total' => $totalNotifyCount,
			'pagesize' => $pageSize,
			'page' => $page,
			'friends' => $friendsList,
		);
	}	
	
	/**
	 * 获取好友详细列表
	 * @param Integer $fid 好友id
	 * return Array
	 */
	public function getFriendDetialByFid($fid) {
		$friendDetial = array();
		$userDetail = s7::get('userinfo',$fid);
		if(!$userDetail) {
			return array();
		}
				
		$companyInfo = s7::get('user_company', $userDetail['company']);
		if(!$companyInfo){
			return array();
		}
		
		$friendDetial['company_name'] = $companyInfo['name'];
		$friendDetial['company_level'] = $companyInfo['level'];
		$friendDetial['boss_name'] = $userDetail['name'];
		$friendDetial['uid'] = $fid;

		return $friendDetial;
	}

	/**
	 * 验证是否为自己的好友
	 * @param Integer $uid 用户id
	 * @param Integer $fid 好友id
	 * return Array
	 */	
	public function isFriend($uid, $fid) {
		$friends = $this->getFriends($uid);

		if(is_array($friends) && isset($friends[$fid])) {
			return TRUE;
		}
		return FALSE;
	}		
	
	/**
	 * 添加好友
	 */
	public function addFriend($uid, $fuid) {
		$friends = $this->getFriendsData($uid);
		if(!$friends) {
			$friends = array();
		}
		
		#删除requesting
		if(isset($friends["requesting"]))
		{
			if(isset($friends["requesting"][$fuid]))
			{
				unset($friends["requesting"][$fuid]);
			}
		} else {
			$friends["requesting"] = array();
		}
		
		#添加好友
		if(!isset($friends['friends']))
		{
			$friends['friends'] = array();
		}
		
		if(!isset($friends['friends'][$fuid]))
		{
			$friends['friends'][$fuid] = array('refresh' => time());
		}
		
		$this->setFriendsData($uid, $friends);
		return TRUE;
	}

	/**
	 * 获取用户好友数据
	 * @param Integer $uid
	 * @return Array:
	 */
	public function getFriendsData($uid) {
		$friendsData = s7::get('user_friends',$uid);
		if(!$friendsData || !is_array($friendsData))
			$friendsData = array();
		return $friendsData;
	}
	
	/**
	 * 更新用户好友数据
	 * @param Integer $uid      用户ID
	 * @param Array   $arrFriends  好友数据
	 * @return Array
	 */
	public function setFriendsData($uid, $arrFriends) {
		s7::set('user_friends',$uid ,$arrFriends);
	}	
	
	/**
	 * 删除双向好友数据
	 */
	public function removeFriend($uid, $fuid, $userName) {
	/*	$res = caocall("friends", "deleteFriend", array($uid, $fuid));
		if($res === true)
		{
			$devres = caocall("friends", "deleteFriend", array($fuid, $uid));
			if($devres){
				logcall("activity", "pushSelfActivity", array($fuid, packDelFriend($uid, $userName)));
			}
			if(!$devres)
			{
				//TODO 异步删除用户好友
			}
			$this->setUserFriendCountAndSubPower($uid, $fuid, __XD_ADD_FRIEND_POWER);
			$this->setUserFriendCountAndSubPower($fuid, $uid, __XD_ADD_FRIEND_POWER);
		}
		return $res;*/
	}

	
	/**
	 * 取用户收到的好友请求
	 */
	public function getFriendRequestings($uid) {
	//	return caocall("friends", "getRequesting", array($uid));
	}
	
	/**
	 * 取用户发送的好友请求
	 */
	public function getFriendRequesteds($uid) {
	//	return caocall("friends", "getRequested", array($uid));
	}
	
	/**
	 * 当前用户被邀请记录条数
	 */
	public function getFriendRequestingCount($uid) {
	/*	$friendRequestings = $this->getFriendRequestings($uid);
		if(!is_array($friendRequestings))
			return false;
		return count($friendRequestings);*/
	}
	
	/**
	 * 当前用户发出的邀请记录数
	 */
	public function getFriendRequestedCount($uid) {
	/*	$friendRequesteds = $this->getFriendRequesteds($uid);
		if(!is_array($friendRequesteds))
			return false;
		return count($friendRequesteds);*/
	}
	
	/**
	 * 等级对应可添加同伴数
	 */
	public function getLevelFriendCount($level) {
		//return intval((($level - 1) / 10) * 5 + 10);
	}	
	
	/**
	 * 判断是否有fuid用户的好友申请
	 */
	public function isRequestedExist($uid, $fuid) {
		//return caocall("friends", "isRequested", array($uid, $fuid));
	}
	
	/**
	 * 判断是否已有发送给fuid的好友申请
	 */
	public function isRequestingExist($uid, $fuid) {
		//return caocall("friends", "isRequesting", array($uid, $fuid));
	}
	
	/** 
	 * 发送好友请求
	 */
	public function sendRequest($uid, $fuid) {
/*		$res = caocall("friends", "addRequesting", array($uid,$fuid));
		if($res)
		{
			$r = caocall("friends", "addRequested", array($fuid, $uid));
			if(!$r)
			{
				//TODO 发送异步请求更新fuid发送的好友请求数据
			}
		}
		return $res;*/
	}	
	
}
?>