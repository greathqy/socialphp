<?php
/**
 * @file	咖啡屋模块逻辑
 * @author	cmworld@gmail.com
 */
class chatLogic extends Logic
{
    //For controller

    //For other module OR general encapsulation

	/**
	 * 获得聊天列表
	 *
	 * @param Integer	$page		显示第几页商品
	 * @return Array	聊天信息
	 */
	public function getChatList($page) {
		
		$pageSize = Configurator::get('module.frontend_chat.misc.page_size');
		$displaypages = Configurator::get('module.frontend_chat.misc.display_pages');
		$max_show = $pageSize * $displaypages;

		$chatroom = s7::get('chatroom','list');

		if(!$chatroom || !is_array($chatroom)){
			$chatroom = array();
		}
		
		$chatroom = Util::filterSchemaInfo($chatroom);
		$chatroom = array_slice($chatroom, 0, $max_show, TRUE);
		
		$totalChatCount = sizeof($chatroom);
		$totalPages =  ceil($totalChatCount / $pageSize);
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		
		$start = ($page - 1) * $pageSize;
		$chats = array_slice($chatroom, $start, $pageSize, TRUE);

		return array(
			'total' => $totalChatCount,
			'pagesize' => $pageSize,
			'page' => $page,
			'chats' => $chats,
			);
	}
	
	/**
	 * 添加发言
	 *
	 * @param Integer	$uid		用户id
	 * @param Integer	$nickname	用户昵称
	 * @param String	$msg		发言内容
	 * @return Boolean
	 */
	public function postChat($uid ,$nickname ,$msg) {

		$timestamp = time();
		
		$chatroom = s7::get('chatroom','list');
		$chatroom = $chatroom ? $chatroom : array();
		$chatInfo= array(
			'uid'  => $uid,
			'nickname' => $nickname,
			'msg'  => $msg,
			'timestamp' => $timestamp
		);
		
		array_unshift($chatroom,$chatInfo);

		s7::set('chatroom','list',$chatroom);
		s7::set('user_chat_lasttime', $uid, $timestamp);
		
		return TRUE;
	}
    //For event processing
}
