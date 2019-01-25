<?php
/**
 * @author greathqy@gmail.com
 * @file   工作模块controller
 */
class jobController extends Controller
{
	//工作列表
	public function index() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();
		$type = $this->context->get('type');
		$typeMap = Configurator::get('module.frontend_job.module.typemap');

		if (!isset($typeMap[$type])) {
			throw new Exception("公司不存在, 请不要构造uri请求游戏!");
		}
		$companyName = $typeMap[$type]['name'];
		$ret = Module::call('job', 'getJobsIcanDo', array($uid, $type));

		$data['__title__'] = $companyName;
		$data['company_name'] = $companyName;
		$data['normal'] = $ret['normal'];
		$data['special'] = $ret['special'];
		$data['type'] = $type;

		$this->setData($data);
	}

	//工作详情
	public function detail() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$id = $this->context->get('id');
		$idFirst = $id ? $id[0] : 1;
		if ($idFirst != 1) {	//除非id是用1开头的, 工作id第一位表明公司类型。1表示人人可做的工作
			$idFirst--;
		}
		$data = array();

		$allJobs = Module::call('job', 'getJobsIcanDo', array($uid, $idFirst));
		if (!isset($allJobs['normal'][$id]) && !isset($allJobs['special'][$id])) {
			throw new Exception("没有这份工作或者你还不能接这份工作, 请不要构造uri请求游戏!", Error::ERROR_PARAM_INVALID);
		}
		if (isset($allJobs['normal'][$id])) {
			$jobType = 'normal';
			$job = $allJobs['normal'][$id];
		} else if (isset($allJobs['special'][$id])) {
			$jobType = 'special';
			$job = $allJobs['special'][$id];
		}
		//恢复信心
		$starIds = Module::call('star', 'getAllStarIds', array($uid));
		foreach ($starIds as $sid) {
			$params = array('star_id' => $sid);
			Event::trigger('star_restore_confidence', $params);
		}

		//得到所有空闲中的艺人列表
		$starInfo = Module::call('star', 'getStarLists', array($uid, TRUE, TRUE));
		$stars = $starInfo['stars'];

		$typeMap = Configurator::get('module.frontend_job.module.typemap');
		$companyName = $typeMap[$idFirst]['name'];
		$data['company_type'] = $idFirst;
		$data['job_id'] = $id;
		$data['__title__'] = $companyName;
		$data['company_name'] = $companyName;
		$data['job_type'] = $jobType;
		$data['job'] = $job;
		$data['stars'] = $stars;

		$this->setData($data);
	}

	/**
	 * pk赛. 判断这份工作是否要pk，不需要的话直接工作成功
	 */
	public function pk() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$companyType = $this->context->get('type');
		$jobId = $this->context->get('jid');
		$starId = $this->context->get('sid');

		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("艺人不是你的, 不允许替他挑选工作", Error::ERROR_PARAM_INVALID);
		}
		//工作是否合法，是否够资格做这份工作
		$myJobs = Module::call('job', 'getJobsIcanDo', array($uid, $companyType));
		if (!isset($myJobs['normal'][$jobId]) && !isset($myJobs['special'][$jobId])) {
			throw new Exception("工作类型错误, 不要自己构造uri请求游戏!", Error::ERROR_PARAM_INVALID);
		}
		$userDetail = s7::get('userinfo', $uid);
		$companyId = $userDetail['company'];
		$pkInfo = Module::call('job', 'isThisJobNeedPk', array($companyId, $starId, $jobId));
		if (!$pkInfo['players']) {
			$pkInfo['needpk'] = FALSE;
		}
		$jobInfo = Module::call('job', 'getJobInfo', array($jobId));

		$typeMap = Configurator::get('module.frontend_job.module.typemap');
		$data['struggle_points'] = Configurator::get('module.frontend_job.module.struggle_points');
		$data['__title__'] = $typeMap[$companyType]['name'];
		$data['company_type'] = $companyType;	
		$data['needpk'] = $pkInfo['needpk'];
		$data['players'] = $pkInfo['players'];
		$starInfo = s7::rget('star', $starId);
		$starInfo['id'] = $starId;
		$data['mystarinfo'] = $starInfo;
		$data['jobinfo'] = $jobInfo;
		$data['jobid'] = $jobId;

		if (!$data['needpk']) { //直接获得工作
			//判断艺人属性值是否达到了工作的要求
			try {
				$ret = Module::call('job', 'isThisJobICanDo', array($uid, $starId, $jobId));
			} catch (noMoneyException $e) {
				$data['nomoney'] = TRUE;
			}
			if (!isset($data['nomoney'])) {
				$ret = Module::call('job', 'startWork', array($starId, $jobId));
				if ($ret) {
					$data['startwork'] = TRUE;
					$data['expiretime'] = getTimeSpan(time(), $ret['end']);
				}
			}
		}
	
		$this->setData($data);
	}

	//pk对手状态.. vsid vcid sid jid
	public function pkerstat() {
		$vcid = $this->context->get('vcid');
		$sid = $this->context->get('sid');
		$vsid = $this->context->get('vsid');
		$jid = $this->context->get('jid');
		$noStruggle = $this->context->get('nostruggle');

		if (!is_numeric($vcid) || !is_numeric($sid) || !is_numeric($vsid) || !is_numeric($jid)) {
			throw new Exception("pk参数错误", Error::ERROR_PARAM_INVALID);
		}
		$company = s7::get('user_company', $vcid);
		$star = s7::get('star', $vsid);
		if (!$company || !$star) {
			throw new Exception("pk选择参数错误", Error::ERROR_PARAM_INVALID);
		}

		$star['star_text'] = getSexTextual($star['sex']);
		$star['star_type'] = getSexTypeTextual($star['sex']);
		$company['id'] = $vcid;
		$data['company'] = $company;
		$data['star'] = $star;
		$data['vcid'] = $vcid;
		$data['vsid'] = $vsid;
		$data['sid'] = $sid;
		$data['jid'] = $jid;
		$data['nostruggle'] = $noStruggle;

		$this->setData($data);
	}

	//pk结果
	public function pkresult() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();

		$starId = $this->context->get('sid');
		$vsStarId = $this->context->get('vsid');
		$jobId = $this->context->get('jid');
		$vcId = $this->context->get('vcid');
		$acceptDiscount = $this->context->get('acceptdiscount');
		$pkFailPercent = Configurator::get('module.frontend_job.module.pk_fail_harvest');

		//判断是否自己的艺人?, 判断是否能做这份工作, 艺人信心是否足够, 出pk结果
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("艺人不是你的, 不允许替他挑选工作", Error::ERROR_PARAM_INVALID);
		}
		if (Util::isStarBelongToUser($vsStarId, $uid)) {
			throw new Exception("被挑战的艺人是你自己的, 不允许进行挑战", Error::ERROR_PARAM_INVALID);
		}

		try {
			Module::call('job', 'isThisJobICanDo', array($uid, $starId, $jobId));
		} catch (noMoneyException $e) { //属性点不够
			$data['notmeet'] = TRUE;
		}
		if (!isset($data['notmeet'])) { //判断信心
			try {
				Module::call('star', 'subStarConfidence', array($starId));
			} catch (noMoneyException $e) { //信心点不足
				$data['noconfidence'] = TRUE;
			}
		}
		//进行pk
		$arrStar = s7::rget('star', $starId);
		$arrStar['id'] = $starId;
		$arrVsStar = s7::rget('star', $vsStarId);
		$arrVsStar['id'] = $vsStarId;
		if (!isset($data['noconfidence'])) {
			$ret = Module::call('job', 'pk', array($arrStar, $arrVsStar));

			$event = array(
				'result' => $ret,
				'jobid' => $jobId,
				'starid' => $starId,
				);
			Event::trigger('pkresult', $event);

			$data['result'] = $ret; //pk成功或失败
		}
		if (isset($data['result'])) {
			if ($data['result']) { //挑战成功获得工作
				$ret = Module::call('job', 'startWork', array($starId, $jobId));
				if ($ret) {
					$data['startwork'] = TRUE;
					$data['expire'] = getTimeSpan(time(), $ret['end']);
				}
			} else { //挑战失败
				if ($acceptDiscount) { //接受折扣，工资打折
					$arr = array(
						'percent' => $pkFailPercent,
					);
					$ret = Module::call('job', 'startWork', array($starId, $jobId, $arr));
					$data['startwork'] = TRUE;
					$data['accept_discount'] = TRUE;
					$data['expire'] = getTimeSpan(time(), $ret['end']);
				}
			}
		}

		$companyType = $jobId[0];
		if ($companyType != 1) {
			$companyType--;
		}
		$jobInfo = Module::call('job', 'getJobInfo', array($jobId));
		$data['star'] = $arrStar;
		$data['vsstar'] = $arrVsStar;
		$data['cid'] = (int) $vcId;
		$data['jid'] = (int) $jobId;
		$data['company_type'] = $companyType;
		$data['jobinfo'] = $jobInfo;
		$data['percent'] = $pkFailPercent;

		$this->setData($data);
	}

	//安排工作
	public function arrange() {
		$id = $this->context->get('id');

		if (!$id) {
			throw new Exception("明星id非法");
		}
		$star = s7::get('star', $id);
		if (!$star) {
			throw new Exception("明星不存在");
		}
		$star['id'] = $id;
		$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));
		$star['sex_text'] = getSexTextual($star['sex']);

		$data['__title__'] = '+安排工作+';
		$data['star'] = $star;

		$this->setData($data);
	}

	//完成工作
	public function complete() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('id');
		if (!$starId) {
			throw new Exception("缺少参数, 需要明星编号", Error::ERROR_PARAM_INVALID);
		}
		$star = s7::get('star', $starId);
		if (!$star || !$star['jobing']) {
			throw new Exception("艺人不在工作状态中, 请不要篡改uri玩游戏。");
		}
		$star['id'] = $starId;
		$userDetail = s7::get('userinfo', $uid);
		$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));

		$interacts = Module::call('job', 'getInteractStats', array($starId, $star['jobing']['jobid']));
		Module::call('job', 'completeStarWork', array($uid, $userDetail['company'], $starId, $star['jobing']['jobid']));
		
		$data['__title__'] = '+验收工作+';
		$data['star'] = $star;
		$data['working_interacts'] = $interacts;

		$this->setData($data);
	}

	//升级公司
	public function upgrade() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');

		$typeMap = Module::call('job', 'getJobTypeMap', array($type));
		$ret = Module::call('job', 'isJobAbleUpgrade', array($uid, $typeMap['type']));
		$canUpgrade = $ret['can_upgrade'];

		$data['__title__'] = $typeMap['name'];
		$data['can_upgrade'] = $canUpgrade;
		$data['required'] = $ret['required'];
		$data['profit'] = $ret['profit'];
		$data['typemap'] = $typeMap;
		$data['type'] = $type;

		$this->setData($data);
	}

	//正式升级公司
	public function doupgrade() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');
		
		$typeMap = Module::call('job', 'getJobTypeMap', array($type));
		$ret = Module::call('job', 'isJobAbleUpgrade', array($uid, $typeMap['type']));
		$canUpgrade = $ret['can_upgrade'];
		if (!$canUpgrade) {
			throw new Exception("你不能升级工作中心，请不要篡改uri。");
		}
		
		$data['succ'] = FALSE;
		try {
			Module::call('job', 'upgradeJobCenter', array($uid, $typeMap['type']));
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
		
		$data['__title__'] = $typeMap['name'];
		$data['profit'] = $ret['profit'];
		$data['company_name'] = $typeMap['name'];
		$data['type'] = $type;

		$this->setData($data);
	}
}
