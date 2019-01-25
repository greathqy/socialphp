<?php
/**
 * @file Misc utils for application
 * @author greathqy@gmail.com
 */
class Util
{
	/**
	 * 判断数据是否允许刷新了
	 *
	 * @param Array	$refreshConf	刷新配置,包含type, [interval, start] | [hours]等字段
	 * @param Array $inst	要刷新的对象，包含nxtrefresh字段
	 * @return Array
	 */
	static public function isRefreshable($refreshConf, $inst) {
		$type = isset($refreshConf['type']) ? $refreshConf['type'] : 'regular';
		if ($type == 'regular') {
			return self::isRegularRefreshable($refreshConf, $inst);
		} else if ($type == 'discrete') {
			return self::isDiscreteRefreshable($refreshConf, $inst);
		}

		return FALSE;
	}
	
	/**
	 * 判断采用离散时间间隔定时器的数据是否允许刷新了
	 *
	 * @param Array	$refreshConf	刷新配置,包含type, [interval, start] | [hours]等字段
	 * @param Array $inst	要刷新的对象，包含nxtrefresh字段
	 * @return Array
	 */
	static public function isDiscreteRefreshable($refreshConf, $inst) {
		$ret = array(
			'nxtrefresh' => 0,	//下次刷新时间
			'refreshable' => FALSE, //是否可刷新
			);
		$hours = $refreshConf['hours']; //哪些小时会进行刷新
		$nxtRefresh = isset($inst['nxtrefresh']) ? $inst['nxtrefresh'] : 0;
		$now = time();
		$nowHour = date('G');

		if ($nxtRefresh) {	
			if ($now >= $nxtRefresh) { 
				//Maybe at the same day, may be days later, but it not matters, we start from $now
				$refreshHour = NULL;
				$maxIdx = sizeof($hours) -1;
				foreach ($hours as $idx => $hour) {
					if ($nowHour >= $hour) {
						$refreshHour = $idx;
					}
				}
				$arr = date('i:s', $now);
				$arr = explode(':', $arr);
				$nowMin = $arr[0];
				$nowSec = $arr[1];
				$redundantSecs = $nowMin * 60 + $nowSec;

				if (is_null($refreshHour)) { //尚未到当天中第一次可刷新时间
					$diffHours = $hours[0] - $nowHour;
				} else {
					if ($refreshHour == $maxIdx) {	//下次刷新在下一天了, 下一天可能是当月 也可能是下月 :( 甚至到下年第一天
						$diffHours = 24 - $nowHour + $hours[0];
					} else { //下次刷新尚在当天内
						$diffHours = $hours[$refreshHour + 1] - $nowHour;
					}
				}
				$diffSecs = $diffHours * 3600;
				$nxtRefresh = $now + $diffSecs - $redundantSecs;

				$ret = array(
					'refreshable' => TRUE,
					'nxtrefresh' => $nxtRefresh,
					);
			} else {
				$ret = array(
					'refreshable' => FALSE,
					'nxtrefresh' => $nxtRefresh,
					);
			}
		} else { //从未刷新过
			$refreshHour = NULL;
			$maxIdx = sizeof($hours) - 1;
			foreach ($hours as $idx => $hour) {
				if ($nowHour >= $hour) { //We can refresh at this hour
					$refreshHour = $idx;
				}
			}
			if (!is_null($refreshHour)) {
				$ret['refreshable'] = TRUE;
				$nxtRefresh = mktime($hours[$refreshHour], 0, 0, date('n'), date('j'), date('Y'));
				//Add the time span
				if ($refreshHour != $maxIdx) {
					$diffHours = $hours[$refreshHour + 1] - $hours[$refreshHour];
				} else {
					$diffHours = (24 - $hours[$maxIdx]) + $hours[0];
				}
				$diffSecs = $diffHours * 3600;
				$nxtRefresh += $diffSecs;
			} else {
				$ret['refreshable'] = FALSE;
				$nxtRefresh = mktime($hours[0], 0, 0, date('n'), date('j'), date('Y'));
			}

			$ret['nxtrefresh'] = $nxtRefresh;
		}

		return $ret;
	}

	/**
	 * 判断采用规律间隔定时器的数据是否允许刷新了
	 *
	 * @param Array	$refreshConf	刷新配置,包含type, [interval, start] | [hours]等字段
	 * @param Array $inst	要刷新的对象，包含nxtrefresh字段
	 * @return Array
	 */
	static public function isRegularRefreshable($refreshConf, $inst) {
		$ret = array(
			'nxtrefresh' => 0, //下次刷新时间
			'refreshable' => FALSE, //是否可刷新
			);

		$nxtRefresh = isset($inst['nxtrefresh']) ? $inst['nxtrefresh'] : 0;
		$interval = $refreshConf['interval'];
		$intervalSecs = $interval * 3600;	//间隔秒数
		$startHour = $refreshConf['start'];
		$startTime = mktime($startHour, 0, 0, date('n'), date('j'), date('Y'));
		$now = time();
		$nowHour = date('G');

		if ($nxtRefresh) {
			if ($now >= $nxtRefresh) { //判断是否可再刷新
				$ret['refreshable'] = TRUE;
				//Calculate the next refresh time
				$passed = $now - $nxtRefresh;
				$multiples = ceil($passed / $intervalSecs);
				$nxtRefresh = $nxtRefresh + $multiples * $intervalSecs;
				$ret['nxtrefresh'] = $nxtRefresh;
			} else { //尚未到刷新时机
				$ret = array(
					'refreshable' => FALSE,
					'nxtrefresh' => $nxtRefresh,
					);
			}
		} else { //从未刷新过
			if ($nowHour >= $startHour) {
				$ret['refreshable'] = TRUE;
				$passed = $now - $startTime;
				$multiples = ceil($passed / $intervalSecs);
				$nxtRefresh = $startTime + $multiples * $intervalSecs;
			} else {
				$ret['refreshable'] = FALSE;
				$nxtRefresh = $startTime;
			}

			$ret['nxtrefresh'] = $nxtRefresh;
		}

		return $ret;
	}

	/**
	 * 判断某艺人是否是用户公司下的艺人
	 *
	 * @param Integer	$starId	明星id
	 * @param Integer	$uid	用户id
	 * @return Boolean
	 */
	static public function isStarBelongToUser($starId, $uid) {
		$ret = FALSE;
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$stars = $company['stars'];	
		if (in_array($starId, $stars)) {
			$ret = TRUE;
		}

		return $ret;
	}

	/**
	 * 获得老板的体力上限
	 *
	 * @param Integer	$companyLevel	公司等级
	 * @return Integer
	 */
	static public function getMyPowerLimit($companyLevel) {
		$conf = Configurator::get('module.frontend_index.misc.powerlimits');
		$limit = 0;
		if (isset($conf[$companyLevel])) {
			$limit = $conf[$companyLevel];
		}

		return $limit;
	}

	/**
	 * 获得公司要升级的状况 
	 * 返回当前有多少经验值，要升级总共要多少经验值
	 *
	 * @param Array		$arrCompany	公司信息
	 * @param Integer	$toLevel	要升到何级别
	 * @return Array
	 */
	static public function getCompanyUpgradeRequirement($arrCompany, $toLevel) {
		$config = Configurator::get('module.frontend_index.misc.levels');
		$companyLevel = $arrCompany['level'];
		$nowFame = $arrCompany['fame'];

		$atLevelNeed = $config[$companyLevel];
		$toLevelNeed = $config[$toLevel];
		
		//modify by lujp
		$diff = $toLevelNeed - $atLevelNeed;
		$diffMy =  $nowFame - $atLevelNeed;
		$diffNeed = $diff - $diffMy;
		
		$arr = array(
			'my' => $diffMy,  //我在这级里有多少经验值了
			'need' => $diffNeed,
			//add by lujp
			'diff'  => $diff,
			);

		return $arr;
	}

	/**
	 * 获得明星要升级的状况
	 * 返回当前有多少经验值，要升级总共需要多少经验值
	 *
	 * @param Array	$arrStar	明星信息
	 * @param Integer	$toLevel	要升到哪个级别
	 * @return Array
	 */
	static public function getStarUpgradeRequirement($arrStar, $toLevel) {
		$config = Configurator::get('module.frontend_star.misc.levels');
		$starLevel = $arrStar['level'];
		$nowFame = $arrStar['attrs']['fame'];

		$atLevelNeed = $config[$starLevel];
		$toLevelNeed = $config[$toLevel];

		//modify by lujp
		$diff = $toLevelNeed - $atLevelNeed;
		$diffMy =  $nowFame - $atLevelNeed;
		$diffNeed = $diff - $diffMy;	

		$arr = array(
			'my' => $diffMy,  //我在这级里有多少经验值了
			'need' => $diffNeed,
			//add by liujp
			'diff'  => $diff,
			);

		return $arr;
	}

	/**
	 * 获得用户的行动力上限值
	 *
	 * @param Integer	$companyLevel	公司等级
	 * @return Integer
	 */
	static public function getUserPowerLimit($companyLevel) {
		$config = Configurator::get('module.frontend_index.misc.powerlimits');
		$limit = 0;
		if (isset($config[$companyLevel])) {
			$limit = $config[$companyLevel];
		}

		return $limit;
	}

	/**
	 * 获得明星的信心上限
	 *
	 * @param Integer	$starLevel	明星等级
	 * @return Integer
	 */
	static public function getStarConfidenceLimit($starLevel) {
		$config = Configurator::get('module.frontend_star.misc.confidencelimits');
		$limit = 0;
		if (isset($config[$starLevel])) {
			$limit = $config[$starLevel];
		}

		return $limit;
	}

	/**
	 * 过滤掉s7 get l函数中返回的schema信息
	 *
	 * @param Array	$arrData	数据
	 * @return Array
	 */
	static public function filterSchemaInfo($arrData) {
		if (isset($arrData['__schema__!'])) {
			unset($arrData['__schema__!']);
		}
		if (isset($arrData['__shardid__!'])) {
			unset($arrData['__shardid__!']);
		}
		if (isset($arrData['__loaded__!'])) {
			unset($arrData['__loaded__!']);
		}
		foreach ($arrData as $key => $val) {
			if (is_array($val)) {
				$arrData[$key] = self::filterSchemaInfo($val);
			}
		}

		return $arrData;
	}
}
