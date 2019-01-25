<?php
/**
 * @file	招聘模块逻辑
 * @author	greathqy@gmail.com
 */
class recruitLogic extends Logic
{
    //For module OR general encapsulation
	/**
	 * 刷新用户的招聘中心
	 *
	 * @param Integer $uid	用户id
	 * @return Boolean
	 */
    public function refreshRecruit($uid) {
		$recruit = s7::get('recruit', $uid);
		$recruit = $recruit ? $recruit : array();
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$companyLevel = $company['level'];
		$recruitLevel = isset($recruit['level']) ? $recruit['level'] : 1;
		$refreshConf = Configurator::get('module.frontend_recruit.module.refresh_star');
		$refreshTimer = Configurator::get('module.frontend_recruit.module.refresh_timer');
		$baseStarInfo = Configurator::get('module.frontend_recruit.module.base_star_info');
		$index = $companyLevel * 1000 + $recruitLevel;

		$stars = array();
		for ($i = 1; $i <= 3; $i++) {
			$ret = getSelection($refreshConf, $index);
			if ($ret === FALSE) {
				throw new Exception("明星刷新配置有误");
			}
			$starGrade = getRandSelection($ret[1]);
			$starGrade = $starGrade[1];
			$starInfo = $baseStarInfo[$starGrade];

			$stars[$i] = array('grade' => $starGrade, 'attrs' => $starInfo);
		}
		$builtStars = $this->buildStar($stars);

		//下次刷新时间
		$refreshable = Util::isRefreshable($refreshTimer, $recruit);
		$nextRefresh = $refreshable['nxtrefresh'];
		$recruit['level'] = $recruitLevel;
		$recruit['visited'] = TRUE;
		$recruit['nxtrefresh'] = $nextRefresh;
		$recruit['star1'] = $builtStars['star1'];
		$recruit['star2'] = $builtStars['star2'];
		$recruit['star3'] = $builtStars['star3'];

		s7::set('recruit', $uid, $recruit);

		return TRUE;
    }

	/**
	 * 生成一名待招聘明星
	 *
	 * @param Array $arrStars	明星基本信息配置, array(array(明星基础信息), ...)
	 * @return Array
	 */
	private function buildStar($arrStars) {
		$builtStars = array();
		$sexArr = Configurator::get('app.frontend.sexes');
		unset($sexArr[3]);
		$starTypes = Configurator::get('app.frontend.star_types');
		foreach ($arrStars as $id => $conf) { //$id = 1, 2, 3
			$sex = array_rand($sexArr);
			$star['sex'] = $sex;
			$star['name'] = $this->generateStarName($sex); 
			$star['level'] = 1;	//初始为1级
			$star['cnamefree'] = 0;
			$star['type'] = array_rand($starTypes);
			$star['talent'] = $conf['grade'];
			$star['confidence'] = $conf['attrs']['confidence'];
			$star['attrs'] = $this->buildAttr($conf['attrs']);
			$star['jobing'] = array();
			$star['achieve'] = array(
				'films' => 0,
				'tvs' => 0,
				'ads' => 0,
				'discs' => 0,
				);
			$star['equip'] = array();

			$key = 'star' . $id;
			$builtStars[$key] = $star;
		}

		return $builtStars; //star1=>array(), star2=>array(), starId=>array()
	}

	/**
	 * 分配待招聘明星的属性点
	 *
	 * @param Array $arrAttrs	待分配的属性点
	 * @return Array
	 */
	private function buildAttr($arrAttr) {
		$extra = $arrAttr['extra'];
		unset($arrAttr['extra']);
		//将多余的属性点随机分配到各属性中去
		while ($extra > 0) {
			$key = array_rand($arrAttr);
			if ($key != 'confidence') {
				$arrAttr[$key] += 1;
				$extra--;
			}
		}
		//名气初始化为0
		$arrAttr['fame'] = 0;

		return $arrAttr;
	}

	/**
	 * 雇佣一名明星
	 * @todo $price换成真实价格
	 *
	 * @param Integer	$uid	用户ID
	 * @param Integer	$starId	明星ID
	 * @return Boolean
	 *
	 * @throw  noSpaceException 当公司明星空闲位不够时
	 * @throw  noMoneyException 用户的大洋不够时
	 */
	public function hireStar($uid, $starId) {
		//判断明星位是否足够, 用户的钱是否够, 雇佣之
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);

		if ($company['hire_num'] >= $company['starlimits']) {
			throw new noSpaceException("艺人数量满了, 无法雇佣新艺人。");
		}
		
		$userGb = s7::get('userinfo.gb', $uid);
		$price = 1000;

		if ($userGb < $price) {
			throw new noMoneyException("现金不够无法雇佣该身价的艺人");
		}
		$sql = "INSERT INTO `star_list` (`create_time`) VALUES ({time})";
		$sql = str_replace('{time}', time(), $sql);

		$newStarId = s7::exec('star_list', $uid, 'sql', $sql, 'insertid');

		$company['hire_num'] += 1;
		$company['stars'][] = $newStarId;
		$starId = 'star' . $starId;
		$starInfo = s7::rget("recruit.{$starId}", $uid);

		$userGb -= $price;
		s7::set('userinfo.gb', $uid, $userGb);
		s7::set('star', $newStarId, $starInfo);
		s7::set('user_company', $userInfo['company'], $company);
		s7::del("recruit.{$starId}", $uid);

		return $newStarId;
	}

	/**
	 * 生成随机明星名字
	 *
	 * @param String $sex	性别,1/2
	 * @return String
	 */
	public function generateStarName($sex) {
		$firstNames = Configurator::get('module.frontend_recruit.misc.firstname_pool');
		$name1 = Configurator::get('module.frontend_recruit.misc.name1_pool');
		$name2 = Configurator::get('module.frontend_recruit.misc.name2_pool');
		$sexEnMap = Configurator::get('app.frontend.sex_en_map');
		$sex = $sexEnMap[$sex];

		$name1 = $name1[$sex];
		$name2 = $name2[$sex];
		$chars = 2; //双字名
		$rand = mt_rand(0, 1);
		if ($rand == 0) {
			$chars = 1;
		}

		$generated = array();
		$generated[] = $firstNames[array_rand($firstNames)];
		$generated[] = $name1[array_rand($name1)];
			
		if ($chars > 1) {
			$generated[] = $name2[array_rand($name2)];
		}

		return join('', $generated);
	}

	/**
	 * 培训中心是否允许升级
	 *
	 * @param Integer	$uid	用户id
	 * @return Array
	 */
	public function isRecruitAbleUpgrade($uid) {
		$recruit = s7::get('recruit', $uid);
		$level = isset($recruit['level']) ? $recruit['level'] : 1;
        $nextLevel = $level + 1;

        $canUpgrade = TRUE;
        $upgradeConf = Configurator::get('module.frontend_recruit.misc.upgrades_config');
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

        return array(
            'can_upgrade' => $canUpgrade,
            'required' => $required,
            );
	}

	/**
	 * 升级招聘中心
	 *
	 * @param Integer	$uid	用户id
	 * @return Integer
	 *
	 * @throw notMeetException level/prop 公司等级或道具不满足
	 */
	public function upgradeRecruit($uid) {
		$ret = $this->isRecruitAbleUpgrade($uid);
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

		$recruit = s7::get('recruit', $uid);
		$level = isset($recruit['level']) ? $recruit['level'] : 1;
        $level++;
        $recruit['level'] = $level;
        s7::set('recruit', $uid, $recruit);

        return $level;
	}

    //For event processing
}
