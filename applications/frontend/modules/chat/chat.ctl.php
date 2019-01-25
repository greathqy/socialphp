<?php
/**
 * @file	咖啡屋controller。
 * @author	cmworld@gmail.com
 */
class chatController extends Controller
{
	//咖啡屋
	public function index() {
//echo 'ddddd';
		$this->handleActionSubmit();

		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$page = $this->context->get('page');
		$page = $page ? $page : 1;		

		$chatList = Module::call('chat', 'getChatList', array($page));
		$options = array(
			'page_size' => $chatList['pagesize'],
			'item_total' => $chatList['total'],
			'display_pages' => Configurator::get('module.frontend_chat.misc.display_pages'),
			'current_page' => $chatList['page'],
		);
			
		$pagination = Pagination::textual($options, 'chat', 'index', array());
		
		$data['__title__'] = '咖啡屋';
		$data['userinfo'] = $userInfo;
		$data['chatlist'] = $chatList['chats'];
		$data['pagination'] = $pagination;
		$this->setData($data);
	}
	
	public function _submit_index() {
		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();
		$chattoofast = FALSE;
		$timestamp = time();
		
		$time_limit = Configurator::get('module.frontend_chat.misc.time_limit');
		(int)$lasttime_chatTime = s7::get('user_chat_lasttime', $uid);

		if($timestamp - $lasttime_chatTime <  $time_limit){
			$chattoofast = TRUE;
		}
		
		if(!$chattoofast){
		 	$postMsg = $this->context->post('msg');
		 	$limit = Configurator::get('module.frontend_chat.misc.message_limit');
		 	$postMsg = strLimit($postMsg,0,$limit);
		 	
			$ret = Module::call('chat', 'postchat', array($uid, $userInfo['nickname'],$postMsg));
			$this->redirectAction('chat', 'index');
		}
		
		$data['chattoofast'] = $chattoofast;
		$this->setData($data);	
	}
}
