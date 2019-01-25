<?php
/**
 * @file	道具模块逻辑
 * @author	greathqy@gmail.com
 */
class propLogic extends Logic
{
    //For controller

    //For other module OR general encapsulation
	/**
	 * 获得道具信息
	 *
	 * @param Integer	$propId	道具Id
	 * @return Array
	 */
	public function getPropInfo($propId) {
		$allProps = Configurator::get('module.frontend_prop.module.props');
		$propInfo = NULL;
		if (isset($allProps['normal'][$propId])) {
			$propInfo = $allProps['normal'][$propId];
		} else if (isset($allProps['special'][$propId])) {
			$propInfo = $allProps['special'][$propId];
		} else if (isset($allProps['decorates'][$propId])) {
			$propInfo = $allProps['decorates'][$propId];
		}

		if ($propInfo) {
			$propInfo['id'] = $propId;
		}

		if (!$propInfo) {
			throw new Exception("编号为: {$propId}的道具不存在");
		}

		return $propInfo;
	}

	/**
	 * 获得用户某件装备的道具信息
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	用户的服饰id
	 * @return Array
	 *
	 * @throw notFoundException	用户没有此道具
	 */
	public function getDePropInfo($uid, $propId) {
		$userEquips = s7::get('user_equips', $uid);
		if (!isset($userEquips[$propId])) {
			throw new notFoundException("你没有编号为{$propId}的服饰");
		}
		$deInfo = $userEquips[$propId];
		$realPropId = $deInfo['pid'];
		$starId = $deInfo['sid'];
		$upgradeInfo = $deInfo['up'];
		$propInfo = $this->getPropInfo($realPropId);
		$propInfo['star_id'] = $starId;
		if ($starId) {
			$propInfo['star_detail'] = s7::get('star', $starId);
		}
		$propInfo['upgrade_info'] = $upgradeInfo;
		$propInfo['de_id'] = $propId;

		return $propInfo;
	}

	/**
	 * 获得店铺的商品列表
	 *
	 * @param Integer	$storeType	店铺类型
	 * @param String	$propType	star/boss
	 * @param Integer	$page		显示第几页商品
	 * @return Array	商品信息
	 */
	public function getStoreGoods($storeType, $propType, $page) {
		$storeTypeMap = Configurator::get('module.frontend_prop.module.storemap');
		if (!isset($storeTypeMap[$storeType])) {
			throw new Exception("店铺类型不合法，店铺不存在。");
		}
		$pageSize = Configurator::get('module.frontend_prop.misc.per_page_size');
		$storeType = $storeTypeMap[$storeType]['type'];
		$all = Configurator::get('module.frontend_prop.module.props.' . $storeType);
		$allProps = array();
		foreach ($all as $id => $good) {
			if ($good['type'] == $propType) {
				$good['id'] = $id;
				$allProps[$id] = $good;
			}
		}
		unset($all);
		$totalPropCount = sizeof($allProps);
		$totalPages =  ceil($totalPropCount / $pageSize);
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		$start = ($page - 1) * $pageSize;
		$props = array_slice($allProps, $start, $pageSize, TRUE);

		return array(
			'total' => $totalPages,
			'pagesize' => $pageSize,
			'page' => $page,
			'props' => $props,
			);
	}

	/**
	 * 购买道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @param Integer	$num	购买数量
	 * @return Boolean
	 *
	 * @throw noMoneyException 钱不够
	 */
	public function buyProp($uid, $propId, $num) {
		//substract money
		$propInfo = $this->getPropInfo($propId);
		$price = $propInfo['price'];
		$total = $price * $num;

		if ($propInfo['pricetype'] == 'gb') {
			Module::call('index', 'subUserGb', array($uid, $total));
		} else { //sub diamond
			Module::call('index', 'subUserDb', array($uid, $total));
		}

		$this->addProp($uid, $propId, $num);

		return TRUE;
	}

	/**
	 * 添加道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @param Integer	$num	购买数量
	 * @return Boolean
	 */
	public function addProp($uid, $propId, $num = 1) {
        $propId = (string) $propId;
		$type = $propId[0];	//1普通道具 2服饰
		$subType = $propId[1]; //普通道具时 1艺人用 2老板用 3为特殊道具。为服饰时 1上身 2下身 3配饰

		$cacheKey = 'user_props';
		if ($type == 1) {
			if ($subType == 3) {
				$cacheKey = 'user_sprops';
			} else {
				$cacheKey = 'user_props';
			}
		} else if ($type == 2) {
			$cacheKey = 'user_equips';
		}
		$ret = s7::get($cacheKey, $uid);
		$ret = $ret ? $ret : array();
		if ($type == 1) {
			if (isset($ret[$propId])) {
				$ret[$propId] += $num;
			} else {
				$ret[$propId] = $num;
			}
		} else { //decorates. ignore $num parameter
			$ids = array_keys($ret);
			$ids = array_filter($ids, 'is_numeric'); //ids contains schema information
			if ($ids) {
				$maxId = max($ids);
			} else {
				$maxId = 0;
			}
			$maxId += 1;

			$ret[$maxId] = array(
				'pid' => $propId,
				'sid' => NULL,
				'up' => FALSE,	//升级信息, 该装备是否被升级过
			);
		}

		s7::set($cacheKey, $uid, $ret);

		return TRUE;
	}

	/**
	 * 扣除用户普通道具/特殊道具 但不适用于服饰
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @param Integer	$num	数量
	 * @return Boolean
	 *
	 * @throw noMoneyException	道具数不够
	 */
	public function subProp($uid, $propId, $num = 1) {
        $propId = (string) $propId;
		if ($propId[0] != 1) {
			throw new Exception("subProp只能用来减少道具");
		}
		$isSpecial = FALSE;
		if ($propId[1] == 3) {
			$isSpecial = TRUE;
		}
		if ($isSpecial) {
			$allProps = s7::get('user_sprops', $uid);
		} else {
			$allProps = s7::get('user_props', $uid);
		}
		if (!isset($allProps[$propId]) || $allProps[$propId] < $num) {
			throw new noMoneyException("道具数量不够, 无法扣除");
		}
		$allProps[$propId] -= $num;
		if ($isSpecial) {
			s7::set('user_sprops', $uid, $allProps);
		} else {
			s7::set('user_props', $uid, $allProps);
		}

		return TRUE;
	}

	/**
	 * 删除用户的服饰道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @return Boolean
	 *
	 * @throw notMeetException	用户没有此道具
	 */
	public function subDeProp($uid, $propId) {
		$allProps = s7::get('user_equips', $uid);
		if (!isset($allProps[$propId])) {
			throw new notMeetException("用户没有这个道具");
		}

		//删除道具  删除明星身上的装备信息  删除升级信息
		$prop = $allProps[$propId];
		if ($prop['sid']) {
			$star = s7::get('star', $prop['sid']);
			if ($star) {
				foreach ($star['equip'] as $position => &$pid) {
					if ($pid == $propId) {
						$pid = 0;
					}
				}

				s7::set('star', $prop['sid'], $star);
			}
		}
		if ($prop['up']) {	//do nothing @todo need more logic?
		}

		unset($allProps[$propId]);
		s7::set('user_equips', $uid, $allProps);

		return TRUE;
	}

	/**
	 * 批量增加用户的道具  不适用于服饰装备
	 *
	 * @param Integer	$uid	用户id
	 * @param Array	$arrProp	道具信息
	 * @return Boolean
	 */
	public function addUserMultiProps($uid, $arrProp) {
		$propIds = array_keys($arrProp);
		$haveSpecial = FALSE;
		foreach ($propIds as $pid) {
            $pid = (string) $pid;
			if ($pid[1] == 3) {
				$haveSpecial = TRUE;
			}
			if ($pid[0] == 2) {
				throw new Exception("服饰道具不能用addUserMultiProps方法添加");
			}
		}
		$normalProps = s7::get('user_props', $uid);
		if ($haveSpecial) {
			$specialProps = s7::get('user_sprops', $uid);
			$specialProps = $specialProps ? $specialProps : array();
		}
		$normalProps = $normalProps ? $normalProps : array();
		foreach ($arrProp as $pid => $count) {
            $pid = (string) $pid;
			if ($pid[1] != 3) { //第二位为3表示特殊道具
				if (isset($normalProps[$pid])) {
					$normalProps[$pid] += $count;
				} else {
					$normalProps[$pid] = $count;
				}
			} else {
				if (isset($specialProps[$pid])) {
					$specialProps[$pid] += $count;
				} else {
					$specialProps[$pid] = $count;
				}
			}
		}
		s7::set('user_props', $uid, $normalProps);
		if ($haveSpecial) {
			s7::set('user_sprops', $uid, $specialProps);
		}

		return TRUE;
	}

	/**
	 * 批量减少用户的道具  不适用于服饰装备
	 *
	 * @param Integer	$uid	用户id
	 * @param Array	$arrProp	道具信息
	 * @return Boolean
	 *
	 * @throw noMoneyException	道具不足不够扣除
	 */
	public function subUserMultiProps($uid, $arrProp) {
		$propIds = array_keys($arrProp);
		$haveSpecial = FALSE;
		foreach ($propIds as $pid) {
            $pid = (string) $pid;
			if ($pid[1] == 3) {
				$haveSpecial = TRUE;
			}
			if ($pid[0] == 2) {
				throw new Exception("服饰道具不能用addUserMultiProps方法减少");
			}
		}
		$normalProps = s7::get('user_props', $uid);
		$normalProps = $normalProps ? $normalProps : array();
		if ($haveSpecial) {
			$specialProps = s7::get('user_sprops', $uid);
			$specialProps = $specialProps ? $specialProps : array();
		}

		foreach ($arrProp as $pid => $count) {
            $pid = (string) $pid;
			if ($pid[1] != 3) { //第二位3时是特殊道具
				if (!isset($normalProps[$pid]) || $normalProps[$pid] < $count) {
					throw new noMoneyException($pid);
				} else {
					$normalProps[$pid] -= $count;
				}
			} else {
				if (!isset($specialProps[$pid]) || $specialProps[$pid] < $count) {
					throw new noMoneyException($pid);
				} else {
					$specialProps[$pid] -= $count;
				}
			}
		}

		s7::set('user_props', $uid, $normalProps);
		if ($haveSpecial) {
			s7::set('user_sprops', $uid, $specialProps);
		}

		return TRUE;
	}

	/**
	 * 获得用户的某层楼的服饰数据
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$floorId	楼层号
	 * @return Boolean
	 *
	 * @throw OverflowException	超出最大楼层号
	 * @throw notMeetException	还没有解锁该楼层
	 */
	public function getFloorProps($uid, $floorId) {
		$userLevel = s7::get('user_clothesstore_level', $uid);
		$userLevel = $userLevel ? $userLevel : 1;

		if ($floorId > $userLevel) {
			throw new notMeetException("你还没有解锁该楼层");
		}
		$maxFloor = Configurator::get('module.frontend_prop.misc.clothes_store.max_floors');
		if ($floorId > $maxFloor) {
			throw OverflowException("超出系统最大楼层数");
		}
		$floorInfo = s7::get('user_clothesstore_floor', $uid . "#$floorId");
		if (is_null($floorInfo)) {	//refresh
			$floorInfo = $this->buildFloorProps($uid, $floorId);
		} else { //Detect if need refresh
			$refreshTimer = Configurator::get('module.frontend_prop.misc.clothes_store.refresh_timer');
			$refreshable = Util::isRefreshable($refreshTimer, $floorInfo);
			if ($refreshable['refreshable']) {
				$this->buildFloorProps($uid, $floorId);

				//Get The newly builds
				$floorInfo = s7::get('user_clothesstore_floor', $uid . "#$floorId");
			}
		}

		//加载已翻开的道具信息
		$total = $opened = 0;
		foreach ($floorInfo['props'] as $i => & $item) {
			$total++;
			if ($item['flag']) {
				$opened++;
				$item['prop'] = $this->getPropInfo($item['pid']);
			}
		}

		$floorInfo['total'] = $total;
		$floorInfo['opened'] = $opened;

		return $floorInfo;
	}

	/**
	 * 用户是否已经可以合法在某楼层翻货
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$floorId	楼层id
	 * @return Boolean
	 */
	public function isFloorValidForUser($uid, $floorId) {
		$userLevel = s7::get('user_clothesstore_level', $uid);
		$userLevel = $userLevel ? $userLevel : 1;

		if ($floorId > $userLevel) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 服饰店楼层进货
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$floorId	楼层id
	 * @return Boolean
	 */
	public function buildFloorProps($uid, $floorId) {
		$floorConfigs = Configurator::get('module.frontend_prop.misc.clothes_store.floor_config');
		$config = $floorConfigs[$floorId];
		$slots = $config['max_props'];
		$candidates = $config['possible'];
		$props = array();

		$i = 0;
		while ($i < $slots) {
			//@todo 或许需要替换生成算法
			$prop = $candidates[array_rand($candidates)];
			$props[] = array(
				'pid' => $prop,
				'flag' => FALSE,	//状态未翻牌
				);
			$i++;
		}

		$floor = s7::get('user_clothesstore_floor', $uid . "#$floorId");
		$refreshTimer = Configurator::get('module.frontend_prop.misc.clothes_store.refresh_timer');
		$refreshable = Util::isRefreshable($refreshTimer, $floor);
		$nextRefresh = $refreshable['nxtrefresh'];

		$data = array(
			'level' => $floorId,
			'nxtrefresh' => $nextRefresh,
			'props' => $props,
		);
		s7::set('user_clothesstore_floor', $uid . "#$floorId", $data);

		return $data;
	}

	/**
	 * 翻开用户楼层的某个道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$floorId	楼层id
	 * @param Integer	$seqId	要翻的道具序号id
	 * @return Array
	 *
	 * @throw noMoneyException	power 体力不够
	 */
	public function flipFloorProp($uid, $floorId, $seqId) {
		$userLevel = s7::get('user_clothesstore_level', $uid);
		$userLevel = $userLevel ? $userLevel : 1;

		if ($floorId > $userLevel) {
			throw Exception("你尚未解锁该楼层，请不要篡改uri");
		}
		$maxFloor = Configurator::get('module.frontend_prop.misc.clothes_store.max_floors');
		if ($floorId > $maxFloor) {
			throw Exception("超出系统最大楼层数, 请不要篡改uri");
		}
		$floorConfig = Configurator::get('module.frontend_prop.misc.clothes_store.floor_config');
		$floorConfig = $floorConfig[$floorId];
		$maxSeq = $floorConfig['max_props'];
		if ($seqId >= $maxSeq) {
			throw new Exception("翻货架序号错误, 请不要篡改uri");
		}

		$floorInfo = s7::get('user_clothesstore_floor', $uid . "#$floorId");
		if (!$floorInfo['props'][$seqId]['flag']) {
			//减少老板体力
			$amount = Configurator::get('module.frontend_prop.misc.flip_card_power');
			Module::call('index', 'subUserPower', array($uid, $amount));

			$floorInfo['props'][$seqId]['flag'] = TRUE;

			s7::set('user_clothesstore_floor', $uid . "#$floorId", $floorInfo);
		}

		return $floorInfo;
	}

	/**
	 * 解锁用户服饰店楼层
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$floorId	楼层id
	 * @param Boolean	$subNothing	是否不扣任何东西即可解锁
	 * @return Boolean
	 *
	 * @throw invalidSeqException	非法解锁次序
	 * @throw notMeetException	noprop/nogb/nolevel
	 */
	public function unlockFloor($uid, $floorId, $subNothing = FALSE) {
		$userLevel = s7::get('user_clothesstore_level', $uid);
		$userLevel = $userLevel ? $userLevel : 1;
		if ($floorId != ($userLevel + 1)) {
			throw new invalidSeqException("必须依次解锁楼层");
		}
		if (!$subNothing) {
			$unlockConfig = Configurator::get('module.frontend_prop.misc.clothes_store.unlock_floor');
			if (!isset($unlockConfig[$floorId])) {
				throw new Exception("请不要篡改uri来解锁楼层");
			}
			$require = $unlockConfig[$floorId];
			if (isset($require['company_level'])) { //公司级别要求
				$userInfo = s7::get('userinfo', $uid);
				$company = s7::get('user_company', $userInfo['company']);
				if ($company['level'] < $require['company_level']) {
					throw new notMeetException('nolevel');
				}
			}
			if (isset($require['gb'])) { //扣gb数x个
				try {
					Module::call('index', 'subUserGb', array($uid, $require['gb']));
				} catch (noMoneyException $e) {
					unset($e);
					throw new notMeetException('nogb');
				}
			}
			if (isset($require['props']) && $require['props']) {	//扣道具若干
				try {
					$this->subUserMultiProps($uid, $require['props']);
				} catch (noMoneyException $e) {
					unset($e);
					throw new notMeetException('noprop');
				}
			}
		}
		//解锁楼层
		$userLevel++;
		s7::set('user_clothesstore_level', $uid, $userLevel);

		return TRUE;
	}

	/**
	 * 获得用户背包内容
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$type	道具类型 1普通 2服饰
	 * @param String	$gtype	细分类型, star/boss/other top/bottom/de
	 * @param Integer	$page	第几页
	 * @param String	$effect	按食物类道具作用过滤 all/confidence/time/power
	 * @param Boolean	$excludeOtherStarDecorated 如果道具已经被装饰上，是否排除装备在其他明星身上的情况
	 * @return Array
	 */
	public function getBagInfo($uid, $type, $gtype, $page, $effect = 'all', $excludeOtherStarDecorated = FALSE) {
		$pageSize = Configurator::get('module.frontend_prop.misc.bag_per_page_size');
		$config = Configurator::get('module.frontend_prop.module.props');
		if ($type == 1) {
			if ($gtype == 'star' || $gtype == 'boss') {
				$allProps = s7::get('user_props', $uid);
			} else if ($gtype == 'other') {
				$allProps = s7::get('user_sprops', $uid);
			}
		} else if ($type == 2) {
			$allProps = s7::get('user_equips', $uid);
		}
		$allProps = $allProps ? $allProps : array();
		foreach ($allProps as $key => $val) {
			if (!is_numeric($key)) {
				unset($allProps[$key]);
			}
		}

		if ($excludeOtherStarDecorated) {
			foreach ($allProps as $id => $propInfo) {
				if (isset($propInfo['sid']) && $propInfo['sid'] != $excludeOtherStarDecorated) {
					unset($allProps[$id]);
				}
			}
		}

		//过滤不合格道具
		if ($type == 1) { //filter by role, star or boss
			if ($gtype != 'other') { //非特殊道具 boss or star
				foreach ($allProps as $pid => $count) {
					$ptype = $config['normal'][$pid]['type'];
					if ($ptype != $gtype) {
						unset($allProps[$pid]);
					} else { //加载道具信息
						$pinfo = $this->getPropInfo($pid);
						$item = array(
								'id' => $pid,
								'name' => $pinfo['name'],
								'desc' => $pinfo['desc'],
								'count' => $count,
								);
						$allProps[$pid] = $item;

						if ($effect != 'all') {
							if (!isset($pinfo['effect'][$effect])) {
								unset($allProps[$pid]);
							}
						}
					}
				}
			} else {
				foreach ($allProps as $pid => $count) { //加载道具信息
					$pinfo = $this->getPropInfo($pid);
					$allProps[$pid] = array(
						'id' => $pid,
						'name' => $pinfo['name'],
						'desc' => $pinfo['desc'],
						'count' => $count,
						);
				}
			}
		} else { //2, filter by position, top/bottom/de
			$map = array(
				1 => 'top',
				2 => 'bottom',
				3 => 'de',
			);
			foreach ($allProps as $pid => & $item) {
				$ptype = $item['pid'][1];
				$ptype = $map[$ptype];
				if ($ptype != $gtype) {
					unset($allProps[$pid]);
				} else { //加载明星信息
					$item['psid'] = $pid;	//seq id in my own decorates
					$item['prop'] = $this->getPropInfo($item['pid']);
					if ($item['sid']) {
						$item['starinfo'] = s7::get('star', $item['sid']);
					}
				}
			}
		}

		$totalPropCount = sizeof($allProps);
		$totalPages =  ceil($totalPropCount / $pageSize);
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		$start = ($page - 1) * $pageSize;

		$props = array_slice($allProps, $start, $pageSize, TRUE);

		return array(
			'total' => $totalPages,
			'pagesize' => $pageSize,
			'page' => $page,
			'props' => $props,
			);
	}

	/**
	 * 判断用户的该明星是否允许使用某道具  只能用于食品类消耗道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @param Integer	$propId	道具id
	 * @return Boolean
	 *
	 * @throw OverflowException	不允许使用该道具了
	 * @throw notMeetException	明星不在工作中
	 * @throw noMoneyException	道具数量不够消耗
	 */
	public function isUserAbleConsumeProp($uid, $starId, $propId) {
		$isSpecial = FALSE;
        $propId = (string) $propId;
		if ($propId[1] == 3) {
			$isSpecial = TRUE;
		}
		if ($isSpecial) {
			$allProps = s7::get('user_sprops', $uid);
		} else {
			$allProps = s7::get('user_props', $uid);
		}
		$allProps = $allProps ? $allProps : array();
		if (!isset($allProps[$propId]) || $allProps[$propId] < 1) {
			throw new noMoneyException("道具数量不够消耗");
		}
		if (!$isSpecial) {
			$pinfo = $this->getPropInfo($propId);
			//如果不是恢复明星信心或者老板体力的道具的话, 明星必须在工作中才能使用道具
			if (!isset($pinfo['effect']['confidence']) && !isset($pinfo['effect']['power'])) {
				$star = s7::get('star', $starId);
				if (!$star || !$star['jobing']) {
					throw new notMeetException("明星不在工作中");
				}
			}
		}
		//@todo 恢复信心道具是否要有限制?
		if (!$isSpecial) {
			$dailyLimit = Configurator::get('module.frontend_prop.misc.single_prop_daily_limit');
			$today = date('Ymd');
			$stats = s7::get('user_durable_stat', $uid . "#useproplimit");	//array('day'=>xx, stats=>array('pid_xx'=>xx))
			$stats = $stats ? $stats : array();
			$allow = TRUE;
			if (isset($stats['day']) && $stats['day'] == $today) {
				if (isset($stats['stats'][$propId]) && $stats['stats'][$propId] >= $dailyLimit) {
					$allow = FALSE;
				}
			}

			if (!$allow) {
				throw new OverflowException("overflow");
			}
		}

		return TRUE;
	}

	/**
	 * 明星消耗某道具 不适用于服饰类道具
	 *
	 * @param	Integer	$uid	用户id
	 * @param	Integer	$starId	明星id
	 * @param	Integer	$propId	道具id
	 * @return Array
	 *
	 * @throw noMoneyException	道具不足不够扣
	 */
	public function starConsumeProp($uid, $starId, $propId) {
		$star = s7::get('star', $starId);
		$propInfo = $this->getPropInfo($propId);
		$effect = $propInfo['effect'];
		$timeReduce = 0;
		$recoverConfidence = 0;
		foreach ($effect as $key => $val) {
			if ($key == 'time') {
				$timeReduce += abs($val);
			} else if ($key == 'confidence') {
				$recoverConfidence += abs($val);
			}
		}
		if ($timeReduce) {
			$timeReduce *= 60;
			$star['jobing']['end'] -= $timeReduce;
			s7::set('star', $starId, $star);
		}
		if ($recoverConfidence) { //恢复信心
			Module::call('star', 'addStarConfidence', array($starId, $recoverConfidence));
		}

		//减少道具数量
		$this->subProp($uid, $propId, 1);

		//记录该日道具消耗情况
		$stats = s7::get('user_durable_stat', $uid . "#useproplimit");
		$today = date('Ymd');
		$stats['day'] = $today;
		if (isset($stats['stats'][$propId])) {
			$stats['stats'][$propId]++;
		} else {
			$stats['stats'][$propId] = 1;
		}
		s7::set('user_durable_stat', $uid . "#useproplimit", $stats);

		return array(
			'time' => $timeReduce,
			'confidence' => $recoverConfidence,
			);
	}

	/**
	 * 老板消耗某道具 恢复体力 不适用于服饰道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @return Integer
	 *
	 * @throw noMoneyException 道具不足扣异常
	 */
	public function bossConsumeProp($uid, $propId) {
		$propInfo = $this->getPropInfo($propId);
		$effect = $propInfo['effect'];
		$recoverPower = 0;
		foreach ($effect as $key => $val) {
			if ($key == 'power') {
				$recoverPower += abs($val);
			}
		}

		if ($recoverPower) {
			Module::call('index', 'addBossPower', array($uid, $recoverPower));
		}

		//减少道具数量
		$this->subProp($uid, $propId, 1);

		//记录该日道具消耗情况
		$stats = s7::get('user_durable_stat', $uid . "#useproplimit");
		$today = date('Ymd');
		$stats['day'] = $today;
		if (isset($stats['stats'][$propId])) {
			$stats['stats'][$propId]++;
		} else {
			$stats['stats'][$propId] = 1;
		}
		s7::set('user_durable_stat', $uid . "#useproplimit", $stats);

		return $recoverPower;
	}

	/**
	 * 给明星装备服饰
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @param String	$position	装饰部位 top bottom d1 d2等
	 * @param Integer	$propId	用户的道具服饰id
	 * @return Array
	 */
	public function decoratePositionOfStar($uid, $starId, $position, $propId) {
		$propInfo = $this->getDePropInfo($uid, $propId);
		$star = s7::get('star', $starId);
		$removed = NULL;
		$decorated = $propInfo;
		if (isset($star['equip'][$position]) && $star['equip'][$position]) {
			$removed = $star['equip'][$position];
		}
		if ($removed) { //Get removed prop info
			$removedProp = $this->getDePropInfo($uid, $removed);
			$equips = s7::get('user_equips', $uid);
			if (isset($equips[$removed])) {
				$equips[$removed]['sid'] = NULL;
				s7::set('user_equips', $uid, $equips);
			}

			$removed = $removedProp;
		}
		//Mark the prop deocrated to star
		$equips = s7::get('user_equips', $uid);
		if (isset($equips[$propId])) {
			$equips[$propId]['sid'] = $starId;
			s7::set('user_equips', $uid, $equips);
		}
		//Mark the user decorated the prop
		$star['equip'][$position] = $propId;
		s7::set('star', $starId, $star);

		return array(
			'removed' => $removed,
			'decorated' => $decorated,
			);
	}

	/**
	 * 判断某件服饰是否合适艺人的某个部位
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	服饰道具id
	 * @param String	$position top/bottom/d1/d2
	 * @return Boolean
	 */
	public function isDePropSuitableForPosition($uid, $propId, $position) {
		$propInfo = $this->getDePropInfo($uid, $propId);

		return ($propInfo['pos'] == $position);
	}

	/**
	 * 获得服饰道具卖出价格
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	服饰id
	 * @return Integer
	 */
	public function getDePropRedeemPrice($uid, $propId) {
		$propInfo = $this->getDePropInfo($uid, $propId);
		$ratio = Configurator::get('module.frontend_prop.misc.decorate_redeem_price');

		$price = ceil($propInfo['price'] * $ratio);

		return $price;
	}

	/**
	 * 用户回收服饰道具 xx%价格回收
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	服饰道具id
	 * @return Boolean
	 */
	public function redeemUserDeProp($uid, $propId) {
		$propInfo = $this->getDePropInfo($uid, $propId);
		$price = $this->getDePropRedeemPrice($uid, $propId);

		Module::call('index', 'addUserGb', array($uid, $price));	
		//丢弃服饰
		$this->dropUserDeProp($uid, $propId);

		return $price;
	}

	/**
	 * 丢弃用户服饰道具
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	服饰道具id
	 * @return Boolean
	 */
	public function dropUserDeProp($uid, $propId) {
		//是否被艺人装备， 摘下 and so on
		$propInfo = $this->getDePropInfo($uid, $propId);
		if ($propInfo['star_id']) {
			$star = s7::get('star', $propInfo['star_id']);
			if ($star) {
				foreach ($star['equip'] as $pos => &$prop) {
					if ($prop == $propId) {
						$prop = NULL;
					}
				}
				s7::set('star', $propInfo['star_id'], $star);
			}
		}
		$userEquips = s7::get('user_equips', $uid);
		if (isset($userEquips[$propId])) {
			unset($userEquips[$propId]);
			s7::set('user_equips', $uid,$userEquips);
		}

		return TRUE;
	}

	/**
	 * 卸下用户服饰装备
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$propId	道具id
	 * @return Boolean
	 */
	public function undecorateUserProp($uid, $propId) {
		$propInfo = $this->getDePropInfo($uid, $propId);
		if ($propInfo['star_id']) {
			$star = s7::get('star', $propInfo['star_id']);
			if ($star) {
				foreach ($star['equip'] as $pos => &$prop) {
					if ($prop == $propId) {
						$prop = NULL;
					}
				}
				s7::set('star', $propInfo['star_id'], $star);
			}
		}
		$userEquips = s7::get('user_equips', $uid);
		if (isset($userEquips[$propId])) {
			$userEquips[$propId]['sid'] = NULL;
			s7::set('user_equips', $uid, $userEquips);
		}

		return TRUE;
	}

	/**
	 * 卸载一个艺人的所有装备
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @return Boolean
	 */
	public function undecorateStarProp($uid, $starId) {
		$userEquips = s7::get('user_equips', $uid);
		$star = s7::get('star', $starId);
		if ($star) {
			$undeProps = array();
			foreach ($star['equip'] as $pos => $propId) {

				$star['equip'][$pos] = NULL;
				$undeProps[] = $propId;
			}

			$modified = FALSE;
			foreach ($undeProps as $propId) {
				if (isset($userEquips[$propId])) {
					$modified = TRUE;
					$userEquips[$propId]['sid'] = NULL;
				}
			}
			if ($modified) {
				s7::set('user_equips', $uid, $userEquips);
			}

			s7::set('star', $starId, $star);
		}

		return TRUE;
	}

    //For event processing
}
