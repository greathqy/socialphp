<?php
/**
 * @author greathqy@gmail.com
 * @file   模块的逻辑封装模块
 */
class indexLogic extends Logic
{
    //for controller
    public function index() {
    }
  
    //For other module OR general encapsulation
	/**
	 * @todo add real logic
	 * 减少用户的db数
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer 	$amount	扣除数目
	 * @return Boolean
	 *
	 * @throw noMoneyException 钱不够
	 */
	public function subUserDb($uid, $amount) {
		//throw new noMoneyException("db");
		return TRUE;
	}

	/**
	 * 获得用户的钻石数
	 *
	 * @param Integer	$uid	用户id
	 * @return Integer
	 */
	public function getUserDb($uid) {
		return 1000;
	}

	/**
	 * 减少用户的gb数
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$amount	减少金额
	 * @return Boolean
	 *
	 * @throw noMoneyException	钱不够
	 */
	public function subUserGb($uid, $amount) {
		$num = $this->getUserGb($uid);

		if ($num < $amount) {
			throw noMoneyException('gb');
		}

		//$num -= $amount;
		//s7::set('userinfo.gb', $uid, $num);
		s7::dec('userinfo.gb', $uid, $amount);

		return TRUE;
	}

	/**
	 * 增加用户的gb数
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$amount	增加金额
	 * @return Boolean
	 */
	public function addUserGb($uid, $amount) {
		$gb = s7::get('userinfo.gb', $uid);
		$gb = $gb ? $gb : 0;
		$gb += $amount;

		s7::set('userinfo.gb', $uid, $gb);

		return TRUE;
	}

	/**
	 * 获得用户的GB数
	 *
	 * @param Integer	$uid	用户id
	 * @return Integer
	 */
	public function getUserGb($uid) {
		$num = s7::get('userinfo.gb', $uid);

		return $num;
	}

	/**
	 * 减少老板的行动力
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$amount	数量
	 * @return Boolean
	 *
	 * @throw noMoneyException	体力不够
	 */
	public function subUserPower($uid, $amount) {
		$power = s7::get('userinfo.power', $uid);
		if ($amount > $power) {
			throw noMoneyException("体力不够");
		}
		$power -= $amount;
		s7::set('userinfo.power', $uid, $power);

		return TRUE;
	}
	
    /**
     * 创建公司
     *
     * @param Array $arrUserInfo   用户信息
     * @return Boolean
     */
    public function createCompany($arrUserInfo) {
        $uid = $arrUserInfo['uid'];
        $nickname = $arrUserInfo['nickname'];
        $companyName = $nickname . '演艺公司';
        $starLimits = Configurator::get("module.frontend_index.module.company_init_stars");

		$sql = "INSERT INTO `company_list` (`uid`, `create_time`) VALUES ({uid}, {create_time})";
		$sql = str_replace(array('{uid}', '{create_time}'), array($uid, time()), $sql);
		$newCompanyId = s7::exec('company_list', $uid, 'sql', $sql, 'insertid');

        $companyInfo = array(
            'name' => $companyName,
			'level' => 1,
            'fame' => 0,
            'cash' => 0,
            'hire_num' => 0,
			'fired_num' => 0,
            'starlimits' => $starLimits,
			'stars' => array(),
			'fired_stars' => array(),
			'achieve' => array(
				'hires' => 0,
				'films' => 0,
				'tvs' => 0,
				'ads' => 0,
				'discs' => 0,
				),
            );
        s7::set('user_company', $newCompanyId, $companyInfo);
		$userInfo = s7::get('userinfo', $uid);
		$userInfo['company'] = $newCompanyId;
		$userInfo['power'] = Util::getUserPowerLimit($companyInfo['level']);
		$userInfo['name'] = $arrUserInfo['nickname'];
		s7::set('userinfo', $uid, $userInfo);

        return $companyInfo;
    }

	/**
	 * 增加老板体力
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$power	用户体力
	 * @return Boolean
	 */
	public function addBossPower($uid, $power) {
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$companyLevel = $company['level'];

		$self = s7::get('userinfo.power', $uid);
		$self = $self ? $self : 0;
		$add = $power;

		$limitConf = Configurator::get('module.frontend_index.misc.powerlimits');
		$limit = $limitConf[$companyLevel];
		if ($add + $self > $limit) {
			$add = $limit - $self;
		}
		$self += $add;
		s7::set('userinfo.power', $uid, $self);

		return TRUE;
	}

	//Event processing
	//处理老板恢复体力
	public function _onEventBoss_restore_power(& $params) {
		$now = time();
		$uid = $params['uid'];
		$restoreConf = Configurator::get('app.frontend.restore_power_span');
		$period = $restoreConf[0];
		$amount = $restoreConf[1];

		$lastRestore = s7::get('user_durable_stat', $uid . "#bossrestorepower");
		$lastRestore = $lastRestore ? $lastRestore : 0;

		if ($lastRestore + $period * 60 <= $now) {
			$userInfo = s7::get('userinfo', $uid);
			s7::l($userInfo, 'power');
			$company = s7::get('user_company', $userInfo['company']);
			$companyLevel = $company['level'];
			$limit = Util::getMyPowerLimit($companyLevel);
			if ($userInfo['power'] < $limit) {
				$multiples = floor(($now - $lastRestore) / ($period * 60));
				$restoreAmount = $multiples * $amount;
				$userInfo['power'] += $restoreAmount;
				if ($userInfo['power'] > $limit) {
					$userInfo['power'] = $limit;
				}
				s7::set('userinfo.power', $uid, $userInfo['power']);

				s7::set('user_durable_stat', $uid . "#bossrestorepower", $now);
			}
		}

		return TRUE;
	}

    //处理明星完成工作
    public function _onEventStar_complete_work(& $params) {
		$controller = Registry::get('__CURRENT_CONTROLLER');

		$uid = $params['uid'];
		$companyId = $params['company_id'];
		$companyGb = $params['company_gb'];
		$companyExp = $params['company_exp'];
		//加gb
		$userGb = s7::get('userinfo.gb', $uid);
		$userGb = $userGb ? $userGb : 0;
		$userGb += $companyGb;
		s7::set('userinfo.gb', $uid, $userGb);
		$data['gb_added'] = $companyGb;
		//判断公司是否升级
		$company = s7::get('user_company', $companyId);
		s7::l($company, 'fame');
		$company['fame'] += $companyExp;
		$companyLevel = $company['level'];

	  	$data['company_fame_added'] = $companyExp;
		//若升级了
		$ret = Util::getCompanyUpgradeRequirement($company, $companyLevel + 1);
		$upgraded = FALSE;
		//modify by liujp
		//if ($ret['need'] <= $companyExp) {
		if ($ret['need'] <= 0) {
			$upgraded = TRUE;
		}
		if ($upgraded) {
			$companyLevel++;
			$company['level'] = $companyLevel;

			$data['company_upgraded_to'] = $companyLevel;
		}

		if ($companyExp || $upgraded) {
			s7::set('user_company', $companyId, $company);
		}
		
		if($upgraded){
			//触发事件 add by liujp
			$feed_params = array(
				'uid' => $uid,
				'company_name' => $company['name'],
				'company_level' => $company['level'],
			);
			Event::trigger('company_upgrade', $feed_params);
		}

		if ($controller) {
			$controller->setData($data);
		}

		return TRUE;
	}
}
