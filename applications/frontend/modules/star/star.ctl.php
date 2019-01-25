<?php
/**
 * @file	star模块controller
 * @author	greathqy@gmail.com
 */
class starController extends Controller
{
	//明星列表
	public function index() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();
		$info = Module::call('star', 'getStarLists', array($uid));
		$stars = $info['stars'];

		$now = time();
		foreach ($stars as $i => &$star) { //转换用户的工作信息
			$jobInfo = Module::call('star', 'buildStarWorkDetail', array($star));

			$star['jobinfo'] = $jobInfo;
		}
		
		$data['__title__']  = '+艺人列表+';
		$data['total'] = $info['total'];
		$data['still'] = $info['still'];
		$data['stars'] = $stars;
		$data['max'] = $info['max'];
		
		$this->setData($data);
	}

	//明星改名, 只能给自己的明星改名
	public function cname() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = (int) $this->context->get('id');
		
		$data = array('__title__' => '+艺人改名+');

		if (!Util::isStarBelongToUser($starId, $uid)) {
			$data['notmystar'] = TRUE;
		} else {
			$this->handleActionSubmit();

			$star = s7::get('star', $starId);
			$star['id'] = $starId;

			$data['star'] = $star;
			$data['amount'] = Configurator::get('module.frontend_star.module.change_name_fee');
		}

		$this->setData($data);
	}

	//做改名操作
	public function _submit_cname() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		$newName = $this->context->post('name');
		$data = array();

		try {
			$ret = Module::call('star', 'changename', array($uid, $starId, $newName));
			$this->redirectAction('star', 'index');
		} catch (noMoneyException $e) {
			$data['nomoney'] = TRUE;
		}

		$this->setData($data);
	}

	//明星资料
	public function stardetail() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = (int) $this->context->get('id');
		$data = array();

		$star = s7::rget('star', $starId);
		if (!$star) {
			throw new Exception("艺人不存在");
		}
		
		$star['id'] = $starId;
		$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));
		$star['type_text'] = getStarTypeTextual($star['type']);
		$star['talent_text'] = ucfirst($star['talent'][0]);
		$star['sex_text'] = getSexTypeTextual($star['sex']);
		$star['upgradeinfo'] = Util::getStarUpgradeRequirement($star, $star['level'] + 1);
		$star['level_limit'] = Util::getStarConfidenceLimit($star['level']);

		$star = Module::call('star', 'buildStarPlusAttrs', array($uid, $star));

		$data['confidence_restore'] = Configurator::get('module.frontend_star.module.confidence_restore');
		$data['star'] = $star;
		$data['__title__'] = '+艺人资料+';
		
		$this->setData($data);
	}

	//明星级别介绍
	public function levelintro() {
		$level = $this->context->get('level');
		if (!$level) {
			throw new Exception("级别参数错误");
		}
		$level = strtolower($level);
		$config = Configurator::get('app.frontend.level_training_limit');
		if (!isset($config[$level])) {
			throw new Exception("级别参数错误, 配置没有此等级。");
		}
		$config = $config[$level];

		$data['config'] = $config;
		$data['level'] = ucfirst($level);
		$data['__title__'] = '+艺人介绍+';

		$this->setData($data);
	}

	//解雇员工
	public function fire() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		$confirmed = $this->context->get('confirm');
		$data = array();

		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星，不得解雇。");
		}
		$star = s7::get('star', $starId);
		$star['id'] = $starId;
		if ($confirmed) {
			try {
				Module::call('star', 'fireStar', array($uid, $starId));
				$data['fired'] = TRUE;
			} catch (notMeetException $e) {
				$data['inwork'] = TRUE;
			}
		}

		$data['star'] = $star;

		$this->setData($data);
	}

	//艺人荣誉
	public function honor() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星，不能查看ta的荣誉。");
		}
		$honor = Module::call('star', 'getStarHonor', array($starId));
		$star = s7::get('star', $starId);
		
		$data['honor'] = $honor;
		$data['star'] = $star;
		$data['__title__'] = '+艺人荣誉+';

		$this->setData($data);
	}

	//使用减少时间道具
	public function consume() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		$page = $this->context->get('page');
		$page = $page ? $page : 1;
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星, 请不要篡改uri玩游戏。");
		}
		$star = s7::get('star', $starId);
		$star['id'] = $starId;
		$bagInfo = Module::call('prop', 'getBagInfo', array($uid, 1, 'star', $page, 'all'));
		$options = array(
			'page_size' => $bagInfo['pagesize'],
			'item_total' => $bagInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $page,
			);
		$pagination = Pagination::textual($options, 'star', 'consume', array('id' => $starId));

		$data['__title__'] = '+使用道具+';
		$data['star'] = $star;
		$data['props'] = $bagInfo['props'];
		$data['pagination'] = $pagination;

		$this->setData($data);
	}

	//使用恢复信心道具
	public function recover() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		$page = $this->context->get('page');
		$page = $page ? $page : 1;
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星，请不要篡改uri玩游戏。");
		}
		$star = s7::get('star', $starId);
		$star['id'] = $starId;
		$bagInfo = Module::call('prop', 'getBagInfo', array($uid, 1, 'star', $page, 'confidence'));

		$options = array(
			'page_size' => $bagInfo['pagesize'],
			'item_total' => $bagInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $page,
			);
		$pagination = Pagination::textual($options, 'star', 'recover', array('id' => $starId));

		$data['__title__'] = '+使用道具+';
		$data['star'] = $star;
		$data['props'] = $bagInfo['props'];
		$data['pagination'] = $pagination;

		$this->setData($data);
	}

	//艺人装备
	public function de() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("艺人不是你的，不能查看ta的装备信息。");
		}
		$star = Module::call('star', 'getStarDecorateDetail', array($uid, $starId));
		if (!$star) {
			throw new Exception("非法参数");
		}
		$star['id'] = $starId;

		$data['__title__'] = '+艺人服饰+';
		$data['star'] = $star;

		$this->setData($data);
	}

	//给艺人选择装备
	public function choose_de() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		$position = $this->context->get('pos');
		$page = $this->context->get('page');
		$page = $page ? $page : 1;
		
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("");
		}
		if (!in_array($position, array('top', 'bottom', 'd1', 'd2'))) {
			throw new Exception("非法的道具装饰位置");
		}
		
		$pos = $position;
		if ($position == 'd1' || $position == 'd2') {
			$pos = 'de';
		}
		$star = s7::get('star', $starId);
		$star['id'] = $starId;
		$bagInfo = Module::call('prop', 'getBagInfo', array($uid, 2, $pos, $page, 'all', $starId));

		$options = array(
			'page_size' => $bagInfo['pagesize'],
			'item_total' => $bagInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $page,
			);
		$pagination = Pagination::textual($options, 'star', 'choose_de', array('id' => $starId, 'pos' => $position));

		$data['__title__'] = '+使用道具+';
		$data['star'] = $star;
		$data['pos'] = $position;
		$data['props'] = $bagInfo['props'];
		$data['pagination'] = $pagination;

		$this->setData($data);
	}
}
