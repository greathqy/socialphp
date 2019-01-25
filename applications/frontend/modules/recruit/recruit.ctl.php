<?php
/**
 * @file	招聘模块控制器
 * @author	greathqy@gmail.com
 */
class recruitController extends Controller
{
	//刊登招聘广告 新手导航
	public function index() {
		$arr = array();
		$userInfo = $this->getUserInfo();
		$info = s7::get('recruit', $userInfo['uid']);
		if (!isTrue($info, 'visited')) {
			$arr['tutorial'] = TRUE;
		} else {
			$this->redirectAction('recruit', 'candidates');
		}

		$this->setData($arr);
	}

	//首次免费刊登招聘广告
	public function first_recruit() {
		$userInfo = $this->getUserInfo();
		$data = array();
		$recruit = s7::get('recruit', $userInfo['uid']);

		if (isTrue($recruit, 'visited')) { //非首次访问, 标记错误
			$this->setErr(Error::ERROR_INVALID_OP, '你已经免费刊登过广告, 不允许再次免费刊登!');
		} else { //刷新明星，将玩家导航至明星列表
			$data['refreshed'] = TRUE;
			Module::call('recruit', 'refreshRecruit', array($userInfo['uid']));
		}
		unset($recruit);

		$this->setData($data);
	}

	//应聘明星列表
	public function candidates() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();
		$now = time();

		$recruit = s7::get('recruit', $uid);
		if (!isTrue($recruit, 'visited')) {
			$this->redirectAction('recruit', 'index');
			exit;
		}
		$nextRefresh = $recruit['nxtrefresh'];

		$refreshTimer = Configurator::get('module.frontend_recruit.module.refresh_timer');
		$refreshable = Util::isRefreshable($refreshTimer, $recruit);
		$refreshable = $refreshable['refreshable'];

		if ($refreshable) {
			Module::call('recruit', 'refreshRecruit', array($uid));
			$recruit = s7::get('recruit', $uid);
			$nextRefresh = $recruit['nxtrefresh'];
		}
		$total = 0;
		$star1 = s7::l($recruit, 'star1');
		$star2 = s7::l($recruit, 'star2');
		$star3 = s7::l($recruit, 'star3');
		$stars = array();
		foreach (array($star1, $star2, $star3) as $seq => $star) {
			if ($star) {
				$total++;
				//处理
				$star['_seq'] = $seq + 1;
				$star['type_text'] = getStarTypeTextual($star['type']);
				$star['talent_text'] = strtoupper($star['talent'][0]);
				$stars[] = $star;
			}
		}
		$data['total'] = $total;
		$data['stars'] = $stars;
		$data['next_refresh'] = getTimeSpan($now, $nextRefresh);
		
		$this->setData($data);
	}

	//查看某个明星详情
	//@todo 替换成真实身价
	public function stardetail() {
		$id = $this->context->get('id');
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();
		if (!$id) {
			$this->setErr(Error::ERROR_PARAM_INVALID, '缺少要查看的明星编号');
		} else {
			$recruit = s7::get('recruit', $uid);
			$star = s7::l($recruit, "star{$id}");
			if (!$star) {
				$this->setErr(Error::ERROR_PARAM_INVALID, '缺少要查看的明星编号, 或者编号非法!');
			} else {
				$star['_seq'] = $id;
				$star['sex_text'] = getSexTypeTextual($star['sex']);
				$star['talent_text'] = strtoupper($star['talent'][0]);
				$star['type_text'] = getStarTypeTextual($star['type']);
				$starAttrs = s7::l($star, 'attrs');

				$data['star'] = $star;
				$data['attrs'] = $starAttrs;
				$data['recruit'] = $recruit;
			}
		}

		$this->setData($data);
	}

	//手动刷新明星
	public function refresh() {
		$userInfo = $this->getUserInfo();
		$confirmed = (int) $this->context->get('confirm');
		$uid = $userInfo['uid'];
		$data = array();

		$amount = Configurator::get('module.frontend_recruit.module.refresh_amount');
		$data['confirmed'] = $confirmed;
		$data['amount'] = $amount;
		if (!$confirmed) {
		} else {
			$subed = FALSE;
			try {
				$subed = Module::call('index', 'subUserDb', array($uid, $amount));
			} catch (noMoneyException $e) {
				$data['nomoney'] = TRUE;
			}

			if ($subed) {
				Module::call('recruit', 'refreshRecruit', array($uid));
				$this->redirectAction('recruit', 'candidates');
			}
		}
		
		$this->setData($data);
	}

	//雇佣某位明星
	public function hire() {
		$id = $this->context->get('id');
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();

		$star = s7::get('recruit.star'. $id, $uid);
		if (!is_numeric($id) || !$star) {
			$this->setErr(Error::ERROR_PARAM_INVALID, "缺少要查看的明星编号, 或者编号不合法!");
		} else {
			try {
				$ret = Module::call('recruit', 'hireStar', array($uid, $id));
				$data['star_id'] = $ret;
			} catch (noSpaceException $e) {
				$data['full'] = TRUE;
			} catch (noMoneyException $e) {
				$data['nomoney'] = TRUE;
			}
		}

		$data['star'] = $star;

		$this->setData($data);
	}

	//升级招聘中心
	public function upgrade() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$ret = Module::call('recruit', 'isRecruitAbleUpgrade', array($uid));
		$canUpgrade = $ret['can_upgrade'];

		$data['__title__'] = '+招聘中心+';
		$data['can_upgrade'] = $canUpgrade;
		$data['required'] = $ret['required'];

		$this->setData($data);
	}

	//正式升级招聘中心
	public function doupgrade() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$ret = Module::call('recruit', 'isRecruitAbleUpgrade', array($uid));
		$canUpgrade = $ret['can_upgrade'];
		if (!$canUpgrade) {
			throw new Exception("你不能升级招聘中心，请不要篡改uri。");
		}
		try {
			Module::call('recruit', 'upgradeRecruit', array($uid));
			$data['succ'] = TRUE;
		} catch (notMeetException $e) {
			$data['failed'] = TRUE;

			$msg = $e->getMessage();
			if ($msg == 'level') {
				$data['level_not_meet'] = TRUE;
			} else if ($msg == 'prop') {
				$data['prop_not_meet'] = TRUE;
			}
		}

		$data['__title__'] = '+招聘中心+';

		$this->setData($data);
	}
}
