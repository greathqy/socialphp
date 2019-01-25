<?php
/**
 * @file	star模块逻辑
 * @author	greathqy@gmail.com
 */
class starLogic extends Logic
{
    //For controller
	/**
	 * 明星改名
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星ID
	 * @param String	$newName	明星新名字
	 * @return Boolean
	 *
	 * @throw noMoneyException	没钱了
	 */
    public function changename($uid, $starId, $newName) {
		$star = s7::get('star', $starId);
		$needSub = FALSE;
		if ($star['cnamefree']) { 
			$needSub = TRUE;
		}
		if ($needSub) {
			$amount = Configurator::get('module.frontend_star.module.change_name_fee');
			Module::call('index', 'subUserDb', array($uid, $amount));
		}
		$star['cnamefree'] = $star['cnamefree'] + 1;
		$star['name'] = $newName;

		s7::set('star', $starId, $star);

		return TRUE;
    }

	/**
	 * 获得我所有的明星的编号
	 *
	 * @param Integer	$uid	用户id
	 * @return Array
	 */
	public function getAllStarIds($uid) {
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);

		$stars = $company['stars'];
		$stars = $stars ? $stars : array();

		return $stars;
	}

	//For other module OR general encapsulation
	/**
	 * 获得用户的明星列表
	 *
	 * @param Integer $uid	用户id
	 * @param Boolean $fullyLoad 是否加载star全部属性
	 * @param Boolean $onlyAvail 是否只获取空闲艺人
	 * @return Array
	 */
	public function getStarLists($uid, $fullyLoad = FALSE, $onlyAvail = FALSE) {
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$total = $company['hire_num'];
		$max = $company['starlimits'];
		$still = $max - $total;
		$starIds = $company['stars'] ? $company['stars'] : array();
		$stars  = array();
		foreach($starIds as $starId) {
			if ($fullyLoad) {
				$star = s7::rget('star', $starId);
			} else {
				$star = s7::get('star', $starId);
			}
			if ($star) {
				$star = $this->buildStarPlusAttrs($uid, $star);
				if ($onlyAvail) {
					if (!$star['jobing']) {
						$star['id'] = $starId;
						$stars[] = $star;
					}
				} else {
					$star['id'] = $starId;
					$stars[] = $star;
				}
			}
		}

		$data['total'] = $total;
		$data['max'] = $max;
		$data['still'] = $still;
		$data['stars'] = $stars;

		return $data;
	}

	/**
	 * 获得艺人的服饰装备信息
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @return Array
	 */
	public function getStarDecorateDetail($uid, $starId) {
		$star = s7::get('star', $starId);
		if (isset($star['equip']['top'])) {
			$star['equip_detail']['top'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['top']));
		} else {
			$star['equip_detail']['top'] = NULL;
		}
		if (isset($star['equip']['bottom'])) {
			$star['equip_detail']['bottom'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['bottom']));
		} else {
			$star['equip_detail']['bottom'] = NULL;
		}
		if (isset($star['equip']['d1'])) {
			$star['equip_detail']['d1'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['d1']));
		} else {
			$star['equip_detail']['d1'] = NULL;
		}
		if (isset($star['equip']['d2'])) {
			$star['equip_detail']['d2'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['d2']));
		} else {
			$star['equip_detail']['d2'] = NULL;
		}

		return $star;
	}

	/**
	 * 获得用户的所有艺人的服饰装备信息
	 *
	 * @param Integer	$uid	用户id
	 * @return Array
	 */
	public function getStarsDecorateDetail($uid) {
		$starInfo = $this->getStarLists($uid, FALSE);
		$stars = $starInfo['stars'];
		foreach ($stars as $i => $star) {
			if (isset($star['equip']['top'])) {
				$star['equip_detail']['top'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['top']));
			} else {
				$star['equip_detail']['top'] = NULL;
			}
			if (isset($star['equip']['bottom'])) {
				$star['equip_detail']['bottom'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['bottom']));
			} else {
				$star['equip_detail']['bottom'] = NULL;
			}
			if (isset($star['equip']['d1'])) {
				$star['equip_detail']['d1'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['d1']));
			} else {
				$star['equip_detail']['d1'] = NULL;
			}
			if (isset($star['equip']['d2'])) {
				$star['equip_detail']['d2'] = Module::call('prop', 'getDePropInfo', array($uid, $star['equip']['d2']));
			} else {
				$star['equip_detail']['d2'] = NULL;
			}

			$stars[$i] = $star;
		}

		return $stars;
	}

	/**
	 * 获得用户的在工作中的明星列表
	 *
	 * @param	Integer	$uid	用户id
	 * @return Array
	 */
	public function getUserWorkingStars($uid) {
		$starInfo = $this->getStarLists($uid);
		$stars = $starInfo['stars'];
		$workingStars = array();
		foreach ($stars as $star) {
			if ($star['jobing']) {
				$star['jobinfo'] = $this->buildStarWorkDetail($star);

				$workingStars[] = $star;
			}
		}

		return $workingStars;
	}

	/**
	 * 构造明星详细的工作信息
	 *
	 * @param Array	$arrStar	明星信息数组
	 * @return Array
	 */
	public function buildStarWorkDetail($arrStar) {
		$jobInfo = array(
			'injob' => FALSE,
			'jobid' => 0,
			'jobname' => '',
			'expire' => FALSE,
			'still' => '',
		);

		$now = time();

		if ($arrStar['jobing']) {
			$jobInfo['injob'] = TRUE;
			$jobInfo['jobid'] = $arrStar['jobing']['jobid'];
			$jobDetail = Module::call('job', 'getJobInfo', array($jobInfo['jobid']));
			$jobInfo['jobname'] = $jobDetail['name'];
			if ($now >= $arrStar['jobing']['end']) {
				$jobInfo['expire'] = TRUE;
			} else {
				$jobInfo['still'] = getTimeSpan($now, $arrStar['jobing']['end']);
			}
		}	
		return $jobInfo;
	}

	/**
	 * 构造明星装备属性加成信息
	 *
	 * @param Integer	$uid	用户id
	 * @param Array		$arrStar	明星属性
	 * @param Boolean	$accumulated 是否累加到本身的属性中去
	 * @return Array
	 */
	public function buildStarPlusAttrs($uid, $arrStar, $accumulated = FALSE) {
		$charmPlus = $actingPlus = $singPlus = 0;
		$equips = $arrStar['equip'];
		$equips = is_array($equips) ? $equips : array();
		foreach ($equips as $pos => $propId) {
			if ($propId) {
				$prop = Module::call('prop', 'getDePropInfo', array($uid, $propId));
			} else {
				$prop = NULL;
			}
			if ($prop) {
				foreach ($prop['effect'] as $effect => $plus) {
					if ($effect == 'charm') {
						$charmPlus += $plus;
					} else if ($effect == 'acting') {
						$actingPlus += $plus;
					} else if ($effect == 'sing') {
						$singPlus += $plus;
					}
				}
			}
		}
		if ($accumulated) {
			$arrStar['attrs']['charm'] += $charmPlus;
			$arrStar['attrs']['acting'] += $actingPlus;
			$arrStar['attrs']['sing'] += $singPlus;
		}

		$arrStar['charm_plus'] = $charmPlus;
		$arrStar['acting_plus'] = $actingPlus;
		$arrStar['sing_plus'] = $singPlus;

		return $arrStar;
	}

	/**
	 * 解雇一名艺人
	 * 
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @return Boolean
	 *
	 * @throw notMeetException 艺人在打工不能解雇
	 */
	public function fireStar($uid, $starId) {
		$starInfo = s7::get('star', $starId);
		if ($starInfo['jobing']) {
			throw new notMeetException("艺人正在打工中，不能解雇!");
		}

		Module::call('job', 'removeFromSameLevelPkQueue', array($starId, $starInfo));

		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);

		$fired = isset($company['fired_num']) ? $company['fired_num'] : 0;
		$fired++;
		$hired = isset($company['hire_num']) ? $company['hire_num'] : 0;
		if ($hired > 0) {
			$hired--;
		}
		//卸载道具
		Module::call('prop', 'undecorateStarProp', array($uid, $starId));

		$company['fired_stars'][] = $starId;
		foreach ($company['stars'] as $i => $sid) {
			if ($sid == $starId) {
				unset($company['stars'][$i]);
				break;
			}
		}
		$company['hire_num'] = $hired;
		$company['fired_num'] = $fired;

		s7::set('user_company', $userInfo['company'], $company);

		return TRUE;
	}

	/**
	 * 获得明星的荣誉 普通工作
	 *
	 * @param Integer	$starId	明星Id
	 * @return Array
	 */
	public function getStarHonor($starId) {
		$info = s7::get('star_pkinfo', $starId);
		$honor = array();
		if ($info && is_array($info)) {
			$info = $info['normaljobs'];

			foreach ($info as $jobId => $stat) {
				$arr = explode(',', $stat);
				$win = $arr[0];
				$lost = $arr[1];
				$ratio = round($win / $win + $lost, 2);
				$ratio *= 100;

				if ($jobId == 0) { //总荣誉
					$jobName = '普通工作';
				} else {
					$jobInfo = Module::call('job', 'getJobInfo', array($jobId));
					$jobName = $jobInfo['name'];
				}

				$honor[$jobId] = array(
					'id' => $jobId,
					'name' => $jobName,
					'win' => $win,
					'lost' => $lost,
					'ratio' => $ratio,
					);
			}
		}
		ksort($honor, SORT_NUMERIC);

		return $honor;
	}

	/**
	 * 减少明星的信心值
	 *
	 * @param Integer	$starId	明星id
	 * @param Integer	$amount	减少数量. 不传表示介绍配置文件里的struggle_points
	 * @return Boolean
	 *
	 * @throw noMoneyException	明星信心值不够
	 */
	public function subStarConfidence($starId, $amount = NULL) {
		$starAttrs = s7::get('star.attrs', $starId);
		$attr = isset($starAttrs['confidence']) ? $starAttrs['confidence'] : 0;
		if (is_null($amount)) {
			$amount = Configurator::get('module.frontend_job.module.struggle_points');
		}
		if ($attr < $amount) {
			throw new noMoneyException("明星:$starId 信心值不够");
		}
		$attr -= $amount;
		$starAttrs['confidence'] = $attr;
		s7::set('star.attrs', $starId, $starAttrs);

		return TRUE;
	}

	/**
	 * 恢复明星信心值
	 *
	 * @param Integer	$starId	明星id
	 * @param Integer	$amount	恢复数值
	 * @return Boolean
	 */
	public function addStarConfidence($starId, $amount) {
		$star = s7::get('star', $starId);
		s7::l($star, 'attrs');
		$confidence = isset($star['attrs']['confidence']) ? $star['attrs']['confidence'] : 0;

		$starLevel = $star['level'];
		$confidenceLimitConf = Configurator::get('module.frontend_star.misc.confidencelimits');
		$limit = $confidenceLimitConf[$starLevel];
		$add = $amount;
		if ($confidence + $amount > $limit) {
			$add = $limit - $confidence;
		}
		$star['attrs']['confidence'] += $add;
		s7::set('star.attrs', $starId, $star['attrs']);

		return TRUE;
	}

    //For event processing
    //处理明星完成工作
    public function _onEventStar_complete_work(& $params) {
		$starId = $params['star_id'];
		$starExp = $params['star_exp'];
		$star = s7::get('star', $starId);
		s7::l($star, 'attrs');
		$star['attrs']['fame'] += $starExp;

		$ret = Util::getStarUpgradeRequirement($star, $star['level'] + 1);
		$upgraded = FALSE;
		//modify by liujp
		//if ($ret['need'] <= $starExp) {
		if ($ret['need'] <= 0) {
			$upgraded = TRUE;
			$star['level'] += 1;

			$data['star_upgraded_to'] = $star['level'];
		}


		if ($starExp || $upgraded) {
			s7::set('star', $starId, $star);	
		}
		$data['star_fame_added'] = $starExp;

		$controller = Registry::get('__CURRENT_CONTROLLER');
		if ($controller) {
			$controller->setData($data);
		}

		return TRUE;
	}

	//处理明星恢复体力
	public function _onEventStar_restore_confidence(& $params) {
		$now = time();
		$starId = $params['star_id'];
		$restoreConf = Configurator::get('module.frontend_star.module.confidence_restore');
		$period = $restoreConf[0];
		$amount = $restoreConf[1];

		$lastRestore = s7::get('star_durable_stat', $starId . "#starrestorepower");
		$lastRestore = $lastRestore ? $lastRestore : 0;

		if ($lastRestore + $period * 60 <= $now) {
			$star = s7::get('star', $starId);
			$starLevel = $star['level'];
			$confidenceLimitConf = Configurator::get('module.frontend_star.misc.confidencelimits');
			$limit = $confidenceLimitConf[$starLevel];

			$starAttrs = s7::get('star.attrs', $starId);
			if ($starAttrs['confidence'] < $limit) {
				$multiples = floor(($now - $lastRestore) / ($period * 60));
				$restoreAmount = $multiples * $amount;
				$starAttrs['confidence'] += $restoreAmount;
				if ($starAttrs['confidence'] > $limit) {
					$starAttrs['confidence'] = $limit;
				}
				s7::set('star.attrs', $starId, $starAttrs);

				s7::set('star_durable_stat', $starId . "#starrestorepower", $now);
			}
		}

		return TRUE;
	}
}
