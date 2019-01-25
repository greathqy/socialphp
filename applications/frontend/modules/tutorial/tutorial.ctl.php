<?php
/**
 * @file 新手导航模块
 * @author greathqy@gmail.com
 */
class tutorialController extends Controller
{
    
	//新手导航
	public function index() {
		$data['__title__'] = '新手导航';
		$this->setData($data);
	}

	//定向去正确的新手导航步骤
	private function _toSuitableStep($wantedStep = 1) {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$tutorialStatus = s7::get('new_user_tutorial', $uid);

		if(is_null($tutorialStatus)){
			$tutorialStatus = 0;
		}

		if ($tutorialStatus === FALSE) {
			$step = 'completed';
		} else if (($tutorialStatus > $wantedStep)) {
			$step = 'step' . $tutorialStatus;
		} else if ($tutorialStatus + 1 < $wantedStep){
			$tutorialStatus++;
			$step = 'step' . $tutorialStatus;				
		} else {
			$step = null;
		}

		if ($step !== null) {
			$this->redirectAction('tutorial', $step);
			exit;
		}
	}

	//记录通过该步骤
	private function _passStep($step) {
		$step = (int) $step;

		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		
		//modify by Liujp
		$current_step = (int)s7::get('new_user_tutorial', $uid);

		if($current_step < $step && $current_step + 1 == $step){
			s7::set('new_user_tutorial', $uid, $step);
			return TRUE;
		}else{
			return FALSE;
		}
	}

	//发送新手导航奖励
	private function _sendPrizes($step) {
		$prizeConf = Configurator::get('module.frontend_tutorial.misc.prizes');
		if (isset($prizeConf[$step])) {
			$prizeConf = $prizeConf[$step];
		} else {
			$prizeConf = NULL;
		}

		if ($prizeConf) {
			$data = array();

			$userInfo = $this->getUserInfo();
			$uid = $userInfo['uid'];

			if (isset($prizeConf['gb'])) {
				Module::call('index', 'addUserGb', array($uid, $prizeConf['gb']));

				$data['prize']['gb'] = $prizeConf['gb'];
			}
			if (isset($prizeConf['props'])) {
				foreach ($prizeConf['props'] as $propId => $propAmount) {
					Module::call('prop', 'addProp', array($uid, $propId, $propAmount));
					$propInfo = Module::call('prop', 'getPropInfo', array($propId));
					$propInfo['num'] = $propAmount;

					$data['prize']['props'][$propId] = $propInfo;
				}
			}

			if ($data) {
				$this->setData($data);
			}
		}
	}

	//第一步 公司改名
	public function step1() {
		$this->_toSuitableStep(1);
		
		$data['__title__'] = '开设公司';
        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

        $this->handleActionSubmit();

        //正常呈现逻辑
		$userDetail = s7::get('userinfo', $uid);
        $companyInfo = s7::get('user_company', $userDetail['company']);
        $data['nickname'] = $userInfo['nickname'];
        $data['companyname'] = $companyInfo['name'];
        $this->setData($data);
	}

	//第一步 公司改名提交
    public function _submit_step1() {
		$this->_toSuitableStep(1);

        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
        $newName = $this->context->post('companyname');
        
		$userDetail = s7::get('userinfo', $uid);
		$companyInfo = s7::get('user_company', $userDetail['company']);
		$companyInfo['name'] = $newName;
		s7::set('user_company', $userDetail['company'], $companyInfo);

		$data = array('created' => TRUE);

		//发送奖励
		if($this->_passStep(1)){
			$this->_sendPrizes(1);
		}

		$this->setData($data);
    }

	//来到招聘中心 刊登广告链接
	public function step2() {
		$this->_toSuitableStep(2);
		$this->_passStep(2);
		
		$data = array();
		$data['__title__'] = '+招聘中心+';
		$this->setData($data);
	}

	//上街
	/*
	public function step2_street() {
		$data['__title__'] = '街道';

		$this->setData($data);
	}
	 */

	//刊登招聘广告成功
	public function step3() {
		$this->_toSuitableStep(3);
		if($this->_passStep(3)){
			$this->_sendPrizes(3);
		}

		$data['__title__'] = '+招聘中心+';
		$this->setData($data);
	}

	//招聘一名艺人 艺人列表
	//先刷新招聘中心
	public function step4() {
		$this->_toSuitableStep(4);
		$this->_passStep(4);

		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$recruit = s7::get('recruit', $uid);

		if (!isTrue($recruit, 'visited')) {
			Module::call('recruit', 'refreshRecruit', array($uid));
			$recruit = s7::get('recruit', $uid);
		}
		$nextRefresh = $recruit['nxtrefresh'];

		$data = array();
		$now = time();

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
		
		$data['__title__'] = '+招聘中心+';
		$this->setData($data);
	}

	//艺人等级制度介绍
	public function step4_levelintro() {
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

	//第四步 艺人详细资料
	public function step4_stardetail() {
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

	//雇佣艺人成功页面
	//@todo 有时会雇佣失败

	public function step5() {
		$this->_toSuitableStep(5);
		
		$id = $this->context->get('id');
		$id = $id ? $id : 1;
		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$data = array();

		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);

		if (!$userCompany['hire_num'])  {
			$star = s7::rget('recruit.star'. $id, $uid);
			if (!is_numeric($id) || !$star) {
				$this->setErr(Error::ERROR_PARAM_INVALID, "缺少要查看的明星编号, 或者编号不合法!");
			} else {
				try {
					Module::call('recruit', 'hireStar', array($uid, $id));
					$data['star_id'] = $id;
				} catch (noSpaceException $e) {
					$data['full'] = TRUE;
				} catch (noMoneyException $e) {
					$data['nomoney'] = TRUE;
				}
				
				if($this->_passStep(5)){
					$this->_sendPrizes(5);
				}	
			}

			$data['star'] = $star;
		} else { //读取用户的第一个雇佣的明星
			
			$this->_passStep(5);
			$stars = $userCompany['stars'];
			$starId = array_shift($stars);
			$star = s7::rget('star', $starId);
			$data['star'] = $star;
		}

		$this->setData($data);
	}

	//艺人改名提示页面
	public function step6() {
		$this->_toSuitableStep(6);

		$this->handleActionSubmit();
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$data['__title__'] = '+艺人改名+';
		
		$data['star'] = $star;

		$this->setData($data);
	}

	//处理艺人改名提交
	public function _submit_step6() {
		$this->_toSuitableStep(6);

		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::get('star', $starId);

		$data['old_name'] = $star['name'];
		
		$newName = $this->context->post('name');
		$star['name'] = $newName;
		s7::set('star', $starId, $star);

		if($this->_passStep(6)){
			$this->_sendPrizes(6);
		}
		$data['changed'] = TRUE;

		$this->setData($data);
	}

	//艺人列表
	public function step7() {
		$this->_toSuitableStep(7);
		$this->_passStep(7);
		
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

	//查看艺人状态
	public function step8() {
		$this->_toSuitableStep(8);
		$this->_passStep(8);
		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
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

	//培训中心
	public function step9() {
		$this->_toSuitableStep(9);
		$this->_passStep(9);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');
		$type = $type ? $type : 2; //2=歌艺
		//初始化培训班
		$training = Module::call('training', 'getTraining', array($uid));	

		$allowedClasses = Configurator::get('module.frontend_training.module.nameid_map');
		if (!in_array($type, array_keys($allowedClasses))) {
			throw new Exception("培训项目不合法");
		}
		$typeText = $allowedClasses[$type]['id'];
		$classes = Module::call('training', 'getClassesICanAttend', array($uid, $typeText));

		$data['classes'] = $classes;
		$data['class'] = $allowedClasses[$type]['id'];
		$data['class_id'] = 1;	//第一个培训班
		$data['__title__'] = '+培训中心+';

		$this->setData($data);
	}

	//开始培训, 选择明星
	public function step10() {
		$this->_toSuitableStep(10);
		$this->_passStep(10);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('class');
		$type = $type ? $type : 'sing';
		$class = Configurator::get('module.frontend_training.module.classes.normal');
		$class = $class[1];

		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$star['id'] = $starId;
		$star['type_text'] = getStarTypeTextual($star['type']);

		$data['__title__'] = '+培训中心+';
		$data['star'] = $star;
		$data['class'] = $class;
		$data['type'] = $type;

		$this->setData($data);
	}

	//艺人培训成功
	public function step11() {
		$this->_toSuitableStep(11);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');
		$type = $type ? $type : 'sing';
		$classRoomId = 1;
		$class = Configurator::get('module.frontend_training.module.classes.normal');
		$class = $class[1];

		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$star['id'] = $starId;
		
		if($this->_passStep(11)){
			Module::call('training', 'getTraining', array($uid));
			$ret = Module::call('training', 'starAttendClass', array($uid, $starId, $classRoomId, 1, FALSE, TRUE));
			$now = time();
			$data['time'] = getTimeSpan($now, $now + $ret['time']);
			$skillIdMap = Configurator::get('module.frontend_training.module.skill_id_map');
			foreach ($ret['effect'] as $effect => $amount) {
				$ret['effect'][$effect] = array(
					'name' => $skillIdMap[$effect]['text'],
					'amount' => $amount,
					);
			}
			$data['effect'] = $ret['effect'];
			$data['time'] = getTimeSpan($now, $now + $ret['time']);	
			$this->_sendPrizes(11);
		}

		
		$data['__title__'] = '+培训中心+';
		$data['star'] = $star;
		$this->setData($data);
	}

	//上街购买服饰 服饰店
	public function step12() {
		$this->_toSuitableStep(12);
		$this->_passStep(12);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$floorId = $this->context->get('fid');
		$floorId = $floorId ? $floorId : 1;

		$params = array(
			'uid' => $uid,
			);
		Event::trigger('boss_restore_power', $params);

		$userPower = s7::get('userinfo.power', $uid);
		$userInfo = s7::get('userinfo', $uid);
		$companyInfo = s7::get('user_company', $userInfo['company']);
		$companyLevel = $companyInfo['level'];
		$powerLimit = Util::getMyPowerLimit($companyLevel);

		$floorInfo = Module::call('prop', 'getFloorProps', array($uid, $floorId));
		$gb = Module::call('index', 'getUserGb', array($uid));
		$db = Module::call('index', 'getUserDb', array($uid));
		$maxFloors = Configurator::get('module.frontend_prop.misc.clothes_store.max_floors');

		if ($floorId >= $maxFloors) {
			$data['nomorefloor'] = TRUE;
		} else {
			$data['nomorefloor'] = FALSE;
		}

		$data['floorid'] = $floorId;
		$data['floor'] = $floorInfo;
		$data['gb'] = $gb;
		$data['db'] = $db;
		$data['expire'] = getTimeSpan(time(), $floorInfo['nxtrefresh']);
		$data['power'] = $userPower;
		$data['powerlimit'] = $powerLimit;
		$data['__title__'] = '+服饰店+';

		$this->setData($data);
	}

	//翻服饰
	public function step13() {
		$this->_toSuitableStep(13);
		$this->_passStep(13);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$fid = 1;
		$seqId = $this->context->get('seq');
		$seqId = $seqId ? $seqId : 0;

		$floorInfo = Module::call('prop', 'flipFloorProp', array($uid, $fid, $seqId));
		$propId = $floorInfo['props'][$seqId]['pid'];
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		$propInfo['sex_text'] = getSexTypeTextual($propInfo['sex']);

		$userPower = s7::get('userinfo.power', $uid);
		$userDetail = s7::get('userinfo', $uid);
		$companyInfo = s7::get('user_company', $userDetail['company']);
		$companyLevel = $companyInfo['level'];
		$powerLimit = Util::getMyPowerLimit($companyLevel);

		$gb = Module::call('index', 'getUserGb', array($uid));
		$db = Module::call('index', 'getUserDb', array($uid));

		$data['__title__'] = '+服饰店+';
		$data['propid'] = $propId;
		$data['propinfo'] = $propInfo;
		$data['user_gb'] = $gb;
		$data['user_db'] = $db;
		$data['user_power'] = $userPower;
		$data['power_limit'] = $powerLimit;

		$this->setData($data);
	}

	//购买服饰成功
	public function step14() {
		$this->_toSuitableStep(14);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$floorInfo = Module::call('prop', 'getFloorProps', array($uid, 1));
		$prop = NULL;
		foreach ($floorInfo['props'] as $seq => $conf) {
			if ($conf['flag']) {
				$prop = $conf['prop'];
				break;
			}
		}
		if($this->_passStep(14)){
			$propId = $prop['id'];
			Module::call('prop', 'buyProp', array($uid, $propId, 1));
			$this->_sendPrizes(14);
		}
		$data['__title__'] = '+服饰店+';
		$data['prop'] = $prop;
		$this->setData($data);
	}

	//给艺人装备服饰
	public function step15() {
		$this->_toSuitableStep(15);
		$this->_passStep(15);
		$tab = $this->context->get('pos');
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$equips = s7::get('user_equips', $uid);
		$propId = 1;
		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));

		$tab = $tab ? $tab : $propInfo['pos'];

		$data['__title__'] = '+艺人服饰+';
		$data['star'] = $star;
		$data['prop'] = $propInfo;
		$data['tab'] = $tab;

		$this->setData($data);
	}

	//服饰详情 与现有服饰比较
	public function step15_propdetail() {		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$equips = s7::get('user_equips', $uid);
		$propId = 1;
		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));
		$propInfo['sex_text'] = getSexTypeTextual($propInfo['sex']);

		$data['__title__'] = '+艺人服饰+';
		$data['star'] = $star;
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//装备上服饰
	public function step16() {
		$this->_toSuitableStep(16);
				
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$propId = 1;
		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));
		
		if($this->_passStep(16)){
			$ret = Module::call('prop', 'decoratePositionOfStar', array($uid, $starId, $propInfo['pos'], $propId));
			$data['removed'] = $ret['removed'];
			$data['decorated'] = $ret['decorated'];
			$this->_sendPrizes(16);
		}
		$data['__title__'] = '+艺人服饰+';
		$data['star'] = $star;
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//安排工作
	public function step17() {
		$this->_toSuitableStep(17);
		$this->_passStep(17);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$star['type_text'] = getStarTypeTextual($star['type']);

		$companyTypeMap = Configurator::get('module.frontend_job.module.typemap');
		$starTypes = Configurator::get('app.frontend.star_types_en');
		$starType = $starTypes[$star['type']];
		if ($starType == 'acting') {
			$companyType = 'acting';
			$companyName = '影视公司';
		} else if ($starType == 'sing') {
			$companyType = 'sing';
			$companyName = '唱片公司';
		} else if ($starType == 'charm') {
			$companyType = 'ads';
			$companyName = '广告公司';
		}

		$data['__title__'] = '+安排工作+';
		$data['star'] = $star;
		$data['company_type'] = $companyType;
		$data['company_name'] = $companyName;

		$this->setData($data);
	}

	//来到公司 选工作
	public function step18() {
		$this->_toSuitableStep(18);
		$this->_passStep(18);		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$starTypes = Configurator::get('app.frontend.star_types_en');
		$starType = $starTypes[$star['type']];
		
		if ($starType == 'acting') {
			$companyType = 'acting';
			$companyName = '影视公司';
		} else if ($starType == 'sing') {
			$companyType = 'sing';
			$companyName = '唱片公司';
		} else if ($starType == 'charm') {
			$companyType = 'ads';
			$companyName = '广告公司';
		}

		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$jobId = array_shift($generalJobs);
		$job = $allNormalJobs[$jobId];

		$data['__title__'] = '+' . $companyName . '+';
		$data['company_name'] = $companyName;
		$data['job'] = $job;

		$this->setData($data);
	}

	//打工 选择艺人
	public function step19() {
		$this->_toSuitableStep(19);
		$this->_passStep(19);	
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		
		$starTypes = Configurator::get('app.frontend.star_types_en');
		$starType = $starTypes[$star['type']];
		
		if ($starType == 'acting') {
			$companyType = 'acting';
			$companyName = '影视公司';
		} else if ($starType == 'sing') {
			$companyType = 'sing';
			$companyName = '唱片公司';
		} else if ($starType == 'charm') {
			$companyType = 'ads';
			$companyName = '广告公司';
		}

		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$jobId = array_shift($generalJobs);
		$job = $allNormalJobs[$jobId];

		$data['__title__'] = "+{$companyName}+";
		$data['job'] = $job;
		$data['star'] = $star;

		$this->setData($data);
	}

	//打工 接下工作
	public function step20() {
		$this->_toSuitableStep(20);
					
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
	
		$starTypes = Configurator::get('app.frontend.star_types_en');
		$starType = $starTypes[$star['type']];
		
		if ($starType == 'acting') {
			$companyType = 'acting';
			$companyName = '影视公司';
		} else if ($starType == 'sing') {
			$companyType = 'sing';
			$companyName = '唱片公司';
		} else if ($starType == 'charm') {
			$companyType = 'ads';
			$companyName = '广告公司';
		}
		
		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$jobId = array_shift($generalJobs);
		$job = $allNormalJobs[$jobId];
		if($this->_passStep(20)){
			Module::call('job', 'startWork', array($starId, $jobId));
	
			$this->_sendPrizes(20);
			//赠送一个缩短时间道具
			$propId = Configurator::get('module.frontend_tutorial.misc.send_time_reduce_prop');
			Module::call('prop', 'addProp', array($uid, $propId, 1));
		}
		$data['__title__'] = "+{$companyName}+";
		$data['star'] = $star;
		$data['job'] = $job;

		$this->setData($data);
	}

	//使用加速道具
	public function step21() {
		$this->_toSuitableStep(21);
		$this->_passStep(21);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');
		$gtype = $this->context->get('gtype');
		$type = $type ? $type : 1;
		$gtype = $gtype ? $gtype : 'star';

		$propId = Configurator::get('module.frontend_tutorial.misc.send_time_reduce_prop');
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));

		$data['__title__'] = '+背包+';
		$data['type'] = $type;
		$data['gtype'] = $gtype;
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//加速道具介绍
	public function step21_propdetail() {
		$propId = Configurator::get('module.frontend_tutorial.misc.send_time_reduce_prop');
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));

		$data['__title__'] = '+背包+';
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//选择对谁使用加速道具
	public function step22() {
		$this->_toSuitableStep(22);
		$this->_passStep(22);		
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$star['jobing'] = Module::call('star', 'buildStarWorkDetail', array($star));

		$propId = Configurator::get('module.frontend_tutorial.misc.send_time_reduce_prop');
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));

		$data['__title__'] = '+使用道具+';
		$data['star'] = $star;
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//艺人使用道具
	public function step23() {
		$this->_toSuitableStep(23);
					
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		
		$propId = Configurator::get('module.frontend_tutorial.misc.send_time_reduce_prop');
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));

		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$jobId = array_shift($generalJobs);
		$job = $allNormalJobs[$jobId];
		
		if($this->_passStep(23)){
			Module::call('prop', 'starConsumeProp', array($uid, $starId, $propId));
			$this->_sendPrizes(23);
		}
		
		$data['__title__'] = '+使用道具+';
		$data['job'] = $job;
		$data['star'] = $star;
		$data['prop'] = $propInfo;

		$this->setData($data);
	}

	//验收艺人工作
	public function step24() {
		$this->_toSuitableStep(24);
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userDetail = s7::get('userinfo', $uid);
		$userCompany = s7::get('user_company', $userDetail['company']);
		$stars = $userCompany['stars'];
		$starId = array_shift($stars);
		$star = s7::rget('star', $starId);
		$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));
		
		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$jobId = array_shift($generalJobs);
		$job = $allNormalJobs[$jobId];
		if($this->_passStep(24)){
			Module::call('job', 'completeStarWork', array($uid, $userDetail['company'], $starId, $jobId));
			$data['job_finished'] = true;
		}
		$data['__title__'] = '+验收工作+';
		$data['star'] = $star;
		$data['job'] = $job;

		$this->setData($data);
	}

	//新手导航完成
	public function complete() {

		$data['__title__'] = '+新手导航完成+';

		$this->setData($data);
	}
}
