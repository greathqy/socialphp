<?php
/**
 * @author	greathqy@gmail.com
 * @file	工作模块逻辑
 */
class jobLogic extends Logic
{
	/**
	 * 获得公司文字类型
	 *
	 * @param Integer	$type	公司类型
	 * @return Array
	 */
	public function getJobTypeMap($type) {
		$typeMap = Configurator::get('module.frontend_job.module.typemap');
		if (!isset($typeMap[$type])) {
			throw new Exception("公司类型非法");
		}

		$arr = $typeMap[$type];

		return $arr;
	}

	/**
	 * 获得互动动作配置信息
	 *
	 * @param	Integer	$actionId	互动类型id
	 * @return Array
	 */
	public function getInterActionInfo($actionId) {
		$actions = Configurator::get('module.frontend_job.module.interaction.actions');
		if (!isset($actions[$actionId])) {
			throw Exception("配置错误，没有$actionId这种互动");
		}

		return $actions[$actionId];
	}

	/**
	 * 获得当前能做的工作
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$type	工作类型 act/sing/ads
	 * @return Array
	 */
	public function getJobsIcanDo($uid, $type) {
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$jobCenter = s7::get('job_center', $uid);

		$typeMap = Configurator::get('module.frontend_job.module.typemap');
		if (!isset($typeMap[$type])) {
			throw new Exception("公司类型不合法");
		}
		$type = $typeMap[$type]['type'];

		$normalJobs = $this->getNormalJobsIcanDo($company, $jobCenter, $type);
		$specialJobs = $this->getSpecialJobsIcanDo($company, $jobCenter, $type);

		$ret = array(
			'normal' => $normalJobs,
			'special' => $specialJobs,
			);

		return $ret;
	}

	/**
	 * 获得当前能做的普通工作
	 *
	 * @param Array	$arrCompanyInfo	公司信息
	 * @param Array	$arrJobCenterInfo	工作中心信息	
	 * @param String	$type	工作类型
	 * @return Array
	 */
	public function getNormalJobsIcanDo($arrCompanyInfo, $arrJobCenterInfo, $type) {
		$companyLevel = $arrCompanyInfo['level'];
		//$companyLevel = $arrCompanyInfo['level'] +  2;
		$levelKey = $type . '_level';
		$jobCenterLevel = isset($jobCenter[$levelKey]) ? $jobCenter[$levelKey] : 1;
		//$jobCenterLevel = 3;
		$myLimit = $companyLevel * 1000 + $jobCenterLevel;

		//可供选择的工作
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		//通过等级限制来过滤用户能做的工作
		$levelLimits = Configurator::get('module.frontend_job.module.job_conf.level_limit');
		$arr = array_keys($levelLimits);
		$avails = array(); //Contails all keys that i can do
		foreach ($arr as $limit) { //limit = companyLevel * 1000 + jobCenterLevel
			if ($limit <= $myLimit) {
				$avails[] = $limit;
			} else {
				break;
			}
		}
		$allJobsIds = $generalJobs;
		$allJobs = array();
		foreach ($avails as $key) {
			if (isset($levelLimits[$key][$type])) {
				$allJobsIds = array_merge($allJobsIds, $levelLimits[$key][$type]);
			}
		}
		$allJobsIds = array_unique($allJobsIds);
		foreach ($allJobsIds as $id) {
			if (isset($allNormalJobs[$id])) {
				$allJobs[$id] = $allNormalJobs[$id];
			}
		}

		return $allJobs;
	}

	/**
	 * 获得当前能做的特殊工作
	 *
	 * @param Array	$arrCompanyInfo	公司信息
	 * @param Array $arrJobCenterInfo	工作中心信息
	 * @param String	$type	工作类型
	 * @return Array
	 */
	public function getSpecialJobsIcanDo($arrCompanyInfo, $arrJobCenterInfo, $type) {
		$ret = array();

		return $ret;
	}

	/**
	 * 我是否可以做这份工作
	 *
	 * @param Integer 	$uid	用户id
	 * @param Integer 	$starId	明星id
	 * @param Integer	$jobId	工作id
	 * @return Boolean
	 *
	 * @throw noMoneyException 艺人属性点不够不能接受这工作
	 */
	public function isThisJobICanDo($uid, $starId, $jobId) {
		$userInfo = s7::get('userinfo', $uid);
		$jobType = $jobId ? $jobId[0] : 1;
		if ($jobType != 1) {
			$jobType--;
		}
		$typeMap = Configurator::get('module.frontend_job.module.typemap');
		if (!isset($typeMap[$jobType])) {
			throw new Exception("工作id不合法，系统没有这份工作!");
		}
		$companyType = $typeMap[$jobType]['type'];
		$allJobs = $this->getJobsIcanDo($uid, $jobType);
		if (!isset($allJobs['normal'][$jobId]) && !isset($allJobs['special'][$jobId])) {
			throw new Exception("工作id不合法, 你不能打这份工作。");
		}
		$jobInfo = $this->getJobInfo($jobId);
		$constraint = $jobInfo['constraint'];
		$constraints = $jobInfo['constraints'];

		$star = s7::rget('star', $starId);
		$star = Module::call('star', 'buildStarPlusAttrs', array($uid, $star, TRUE));
		$starAttrs = $star['attrs'];
		$starAttr = isset($starAttrs[$constraint]) ? $starAttrs[$constraint] : 0;
		if ($starAttr < $constraints) {
			throw new noMoneyException("艺人的属性点不够, 不能接受这份工作");
		}

		return TRUE;
	}

	/**
	 * 计算pk结果
	 * @todo 使用正式pk函数
	 *
	 * @param Array	$arrStar	明星信息
	 * @param Array	$arrVsStar	对手明星信息
	 * @return Boolean
	 */
	public function pk($arrStar, $arrVsStar) {
		$result = TRUE;
		
		return $result;
	}

	/**
	 * 这份工作是否需要pk，需要的话返回pk对手
	 *
	 * @param Integer	$companyId	公司Id
	 * @param Integer	$starId	明星Id
	 * @param Integer	$jobId	工作id
	 * @return Array
	 */
	public function isThisJobNeedPk($companyId, $starId, $jobId) {
		$needPk = TRUE;
		$generalJobs = Configurator::get('module.frontend_job.module.job_conf.general');
		if (in_array($jobId, $generalJobs)) {
			$needPk = FALSE;
		}
		$players = array();
		if ($needPk) { //读取pk对手名单
			$star = s7::get('star', $starId);
			$starLevel = (isset($star['level']) && $star['level']) ?  $star['level'] : 1;
			$info = s7::get('same_level_pk', $starLevel);

			if ($info && isset($info['members']) && $info['members']) { //we got some players
				shuffle($info['members']);

				$total = 0;
				foreach ($info['members'] as $cidsid => $item) {
					if ($total == 3) {
						break;
					}
					if ($item['cid'] != $companyId) {
						$players[$cidsid] = $item;
						$total++;
					}
				}
			}
		}

		foreach ($players as $cidsid => & $info) { //加载明星信息和公司信息
			$cid = $info['cid'];
			$sid = $info['sid'];

			$company = s7::get('user_company', $cid);
			$star = s7::get('star', $sid);
			$star['sex_text'] = getSexTextual($star['sex']);

			$info['company'] = $company;
			$info['star'] = $star;
		}

		$ret = array(
			'needpk' => $needPk,
			'players' => $players,
			);

		return $ret;
	}

	/**
	 * 通过工作id获得工作配置详情
	 *
	 * @param Integer $jobId	工作id
	 * @return Array
	 */
	public function getJobInfo($jobId) {
		$allNormalJobs = Configurator::get('module.frontend_job.module.job_conf.all_normal_jobs');
		$allSpecialJobs = Configurator::get('module.frontend_job.module.job_conf.all_special_jobs');

		if (!isset($allNormalJobs[$jobId]) && !isset($allSpecialJobs[$jobId])) {
			throw new Exception("工作id $jobId 不存在");
		}
		if (isset($allNormalJobs[$jobId])) {
			$jobInfo = $allNormalJobs[$jobId];
		} else {
			$jobInfo = $allSpecialJobs[$jobId];
		}

		return $jobInfo;
	}

	/**
	 * 开始一份工作
	 *
	 * @param Integer	$starId	明星Id
	 * @param Integer	$jobId	工作Id
	 * @param Array		$extra	额外参数,如工资折扣信息等
	 * @return Array
	 */
	public function startWork($starId, $jobId, $extra = array()) {
		$starInfo = s7::get('star', $starId);
		if ($starInfo['jobing']) {
			throw new Exception("该明星已经在工作中, 不允许再打工.", Error::ERROR_PARAM_INVALID);
		}
		$jobInfo = $this->getJobInfo($jobId);
		if (!isset($jobInfo['time'])) {
			throw new Exception("工作信息配置不正确, 工作id: $jobId");
		}
		//Start Work
		$end = time() + $jobInfo['time'] * 60;
		$job = array('jobid' => $jobId, 'end' => $end);
		if (is_array($extra) && $extra) {
			$job = array_merge($job, $extra);
		}
		$starInfo['jobing'] = $job;
		s7::set('star', $starId, $starInfo);

		return $job;
	}

	/**
	 * 获得明星工作中的互动消息
	 *
	 * @param Integer	$starId	明星id
	 * @param Integer	$jobId	工作id
	 * @return Array
	 */
	public function getInteractStats($starId, $jobId) {
		$jobInteractStats = s7::get('jobing_stats', $starId);
		$jobStealStats = s7::get('job_steal_stats', $starId);
		$interacts = array();

		$jobInfo = $this->getJobInfo($jobId);
		$baseAmount = $jobInfo['company_cash'];
		#设置互动状态
		if (isset($jobInteractStats[$jobId]) && $jobInteractStats[$jobId]) {
			foreach ($jobInteractStats[$jobId] as $item) {
				$actId = $item['act'];
				$actionInfo = $this->getInterActionInfo($actId);
				$type = $actionInfo['effect']['type'];
				$amount = $actionInfo['effect']['amount'];
				if ($type == 'inc' || $type == 'incratio') {
					$direction = 'add';
				} else if ($type == 'dec' || $type == 'decratio') {
					$direction = 'sub';
				}
				if ($type == 'incratio' || $type == 'decratio') { //减少或者增加百分比
					$amount = round($amount / 100, 2) * $baseAmount;
				}
				$theUser = s7::get('userinfo', $item['uid']);
				$interacts[$item['tm']] = array(
					'type' => 'interact',	//好友互动类动作
					'time' => date('H:i', $item['tm']),
					'uid' => $item['uid'],
					'uname' => isset($theUser['name']) ? $theUser['name'] : '神秘人',
					'actname' => $actionInfo['name'],
					'direction' => $direction,
					'amount' => $actAmount,
					);
			}
		}
		$stealName = Configurator::get('module.frontend_job.module.interaction.steal.name');
		$stealConf = Configurator::get('module.frontend_job.misc.friend_steal');
		$stealAmount = $stealConf['amount'];
		if ($stealConf['type'] == 'percent') {
			$stealAmount = round($stealAmount / 100, 2) * $baseAmount;
		}
		if (isset($jobStealStats[$jobId]) && $jobStealStats[$jobId]) {
			foreach ($jobStealStats[$jobId] as $uid => $timestamp) {
				$theUser = s7::get('userinfo', $uid);

				$interacts[$timestamp] = array(
					'type' => 'steal',
					'time' => date('H:i', $timestamp),
					'uid' => $uid,
					'uname' => isset($theUser['name']) ? $theUser['name'] : '神秘人',
					'actname' => $stealName,
					'direction' => 'sub',
					'amount' => $stealAmount,
					);
			}
		}

		#按时间排序interacts
		arsort($interacts, SORT_NUMERIC);

		return $interacts;
	}

	/**
	 * 将明星从相同等级pk队列中拿掉
	 *
	 * @param Integer	$starId	明星Id
	 * @param Array		$arrStar	明星信息
	 * @return Boolean
	 */
	public function removeFromSameLevelPkQueue($starId, $arrStar) {
		$starLevel = $arrStar['level'];
		$queue = s7::get('same_level_pk', $starLevel);
		$removed = FALSE;
		if ($queue && is_array($queue)) {
			foreach ($queue as $cidsid => $item) {
				if ($item['sid'] == $starId) {
					$removed = TRUE;
					unset($queue[$cidsid]);
					break;
				}
			}
		}
		if ($removed) {
			s7::set('same_level_pk', $starLevel, $queue);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * 明星完成工作
	 *
	 * @param	Integer	$uid		用户id
	 * @param	Integer	$companyId	公司id
	 * @param	Integer	$starId	明星id
	 * @param	Integer	$jobId	工作id
	 * @return Boolean
	 */
	public function completeStarWork($uid, $companyId, $starId, $jobId) {
		$jobInteractStats = s7::get('jobing_stats', $starId);
		$jobStealStats = s7::get('job_steal_stats', $starId);

		$star = s7::get('star', $starId);
		if ($star['jobing']['jobid'] != $jobId) {
			throw new Exception("艺人完成工作类型不正确, 请不要非法操作。");
		}
		$now = time();
		if ($now < $star['jobing']['end']) {
			throw new Exception("艺人工作时间尚未结束，请不要非法操作。");
		}
		$percent = 100;
		if (isset($star['jobinfo']['percent'])) {
			$percent = $star['jobinfo']['percent'];
		}
		$percent = round($percent / 100, 2);
		$jobInfo = $this->getJobInfo($jobId);
		$baseCompanyGb = $jobInfo['company_cash'];
		$companyGb = $baseCompanyGb * $percent;
		$companyFame = $jobInfo['company_fame'];
		$starFame = $jobInfo['star_fame'];

		//互动中有帮助和损害，顺手牵羊部分要去掉损失
		if (isset($jobInteractStats[$jobId])) {
			foreach ($jobInteractStats[$jobId] as $item) {
				$actionType = $item['act'];
				$actionInfo = $this->getInterActionInfo($actionType);
				$type = $actionInfo['effect']['type'];
				$amount = $actionInfo['effect']['amount'];

				if ($type == 'inc') {
					$companyGb += $amount;
				} else if ($type == 'dec') {
					$companyGb -= $amount;	
				} else if ($type == 'incratio') {
					$companyGb += round($amount / 100, 2) * $baseCompanyGb;
				} else if ($type == 'decratio') {
					$companyGb -= round($amount / 100, 2) * $baseCompanyGb;;
				}
			}
			unset($jobInteractStats[$jobId]);
			s7::set('jobing_stats', $starId, $jobInteractStats);	
		}		
		//顺手羊羊
		if ($jobStealStats && $companyGb) {
			if (isset($jobStealStats[$jobId])) {
				$stealConf = Configurator::get('module.frontend_job.misc.friend_steal');
				$stealType = $stealConf['type'];
				$amount = $stealConf['amount'];
				foreach ($jobStealStats[$jobId] as $uid => $time) {
					if ($stealType == 'amount') {
						$companyGb -= $amount;
					} else if ($stealType = 'percent') {
						$companyGb -= round($amount / 100, 2) * $baseCompanyGb;
					}
				}
				unset($jobStealStats[$jobId]);
				s7::set('job_steal_stats', $starId, $jobStealStats);
			}
		}

		if ($companyGb < 0) {
			$companyGb = 0;
		}
		//去掉明星打工信息
		$star['jobing'] = array();
		s7::set('star', $starId, $star);

		
		//触发事件
		$params = array(
			'uid' => $uid,
			'star_id' => $starId,
			'company_id' => $companyId,
			'star_exp' => $starFame,
			'company_exp' => $companyFame,
			'company_gb' => $companyGb,
		
			//add by liujp
			'job_id' => $jobId,
			'job_name' => $jobInfo['name'],
			'star_name' => $star['name'],
			);
			
		Event::trigger('star_complete_work', $params);

		return TRUE;
	}

	/**
	 * 公司是否允许升级
	 *
	 * @param Integer	$uid	用户id
	 * @param String	$type	公司类型 ads/sing/act
	 * @return Array
	 */
	public function isJobAbleUpgrade($uid, $type) {
		$jobCenter = s7::get('job_center', $uid);
		$key = $type . '_level';
		$level = isset($jobCenter[$key]) ? $jobCenter[$key] : 1;
        $nextLevel = $level + 1;

        $canUpgrade = TRUE;
        $upgradeConf = Configurator::get('module.frontend_job.misc.upgrades_config.' . $type);
        if (!isset($upgradeConf[$nextLevel])) {
            $canUpgrade = FALSE;
        }
        if ($canUpgrade) {
            $required = $upgradeConf[$nextLevel];
        } else {
            $required = array();
        }

        if ($required) { //转化需要道具信息
            if (isset($required['props'])) {
                foreach ($required['props'] as $pid => &$num) {
                    $propInfo = Module::call('prop', 'getPropInfo', array($pid));
                    $propInfo['num'] = $num;
                    $num = $propInfo;
                }
            }
        }
		$levelLimit = Configurator::get('module.frontend_job.module.job_conf.level_limit');
		$userInfo = s7::get('userinfo', $uid);
		$companyInfo = s7::get('user_company', $userInfo['company']);
		$companyLevel = $companyInfo['level'];
		$myLimit = $companyLevel * 1000 + $level;
		$myNextLimit = $companyLevel * 1000 + $nextLevel;	//下一级别能到啥好处
		$newOpen = array();

		foreach ($levelLimit as $key => $conf) {
			if ($key > $myLimit && $key <= $myNextLimit) {
				if (isset($conf[$type])) {
					foreach ($conf[$type] as $jobId) {
						$newOpen[$jobId] = $this->getJobInfo($jobId);
					}
				}
			}
		}

        return array(
            'can_upgrade' => $canUpgrade,
            'required' => $required,
			'profit' => $newOpen,
            );
	}

	/**
	 * 升级公司
	 *
	 * @param Integer	$uid	用户id
	 * @param String	$type	公司类型 ads/act/sing
	 * @return Integer
	 *
	 * @throw notMeetException level/prop 公司等级或道具不满足
	 */
	public function upgradeJobCenter($uid, $type) {
		$ret = $this->isJobAbleUpgrade($uid, $type);
        $required = $ret['required'];

        $userInfo = s7::get('userinfo', $uid);
        $company = s7::get('user_company', $userInfo['company']);
        $companyLevel = $company['level'];
        if ($companyLevel < $required['level']) {
            throw new notMeetException("level");
        }
        $userProps = s7::get('user_sprops', $uid);
        foreach ($required['props'] as $pid => $item) {
            if (!isset($userProps[$pid]) || $userProps[$pid] < $item['num']) {
                throw new notMeetException('prop');
            }
        }

        foreach ($required['props'] as $pid => $item) {
            Module::call('prop', 'subProp', array($uid, $pid, $item['num']));
        }

		$jobCenter = s7::get('job_center', $uid);
		$key = $type . '_level';
		$level = isset($jobCenter[$key]) ? $jobCenter[$key] : 1;
        $level++;
        $jobCenter[$key] = $level;
        s7::set('job_center', $uid, $jobCenter);
		
        return $level;
	}

    //For event processing
	/**
	 * PK结果事件, 记录用户的pk信息 生成荣誉
	 *
	 * @param Array	$param	事件参数
	 * @return Boolean
	 */
	public function _onEventPkresult(&$params) {
		$jobId = $params['jobid'];
		$starId = $params['starid'];
		$result = $params['result'];

		$pkInfo = s7::get('star_pkinfo', $starId);
		$normalJobs = isset($pkInfo['normaljobs']) ? $pkInfo['normaljobs'] : array();
		$win = 1;
		$lost = 0;
		if (!$result) {
			$win = 0;
			$lost = 1;
		}
		$jobs = array(0, $jobId);
		foreach ($jobs as $job) {
			if (isset($normalJobs[$job])) { //原先已经有记录
				$arr = explode(',', $normalJobs[$job]);
				$arr[0] = $arr[0] + $win;
				$arr[1] = $arr[1] + $lost;
				$normalJobs[$job] = join(',', $arr);
			} else {
				$normalJobs[$job] = join(',', array($win, $lost));
			}
		}
		$pkInfo['normaljobs'] = $normalJobs;
		s7::set('star_pkinfo', $starId, $pkInfo);

		return TRUE;
	}
}
