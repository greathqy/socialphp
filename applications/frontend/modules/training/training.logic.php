<?php
/**
 * @file	培训模块逻辑
 * @author	greathqy@gmail.com
 */
class trainingLogic extends Logic
{
    //For other module OR general encapsulation
	/**
	 * 获得培训所
	 *
	 * @param Integer	$uid	用户id
	 * @return Array
	 */
	public function getTraining($uid) {
		$training = s7::get('training', $uid);
		if (!$training) {
			Module::call('training', 'initTraining', array($uid));
			$training = s7::get('training', $uid);
		}

		$now = time();
		foreach ($training['classes'] as &$class) {
			if ($class['nxtrefresh'] <= $now) {
				$class['spare'] = TRUE;
				$class['still'] = '';
			} else {
				$class['spare'] = FALSE;
				$class['still'] = getTimeSpan($now, $class['nxtrefresh']);
			}
        }

		return $training;
	}

	/**
	 * 初始化培训所
	 *
	 * @param Integer	$uid	用户id
	 * @return Boolean
	 */
	public function initTraining($uid) {
		$training = array(
			'level' => 1,
			'classes' => array(),
			);
		s7::set('training', $uid, $training);
		$this->openClass($uid);

		return TRUE;
	}

	/**
	 * 开办培训班，普通。非邀请和钻石开办的 
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$classEnd	培训班截止时间
     * @param String    $type   培训班类型pay/invite/normal 普通班normal时不用传此参数
	 * @return Integer
	 */
	public function openClass($uid, $classEnd = -1, $type = NULL) {
		$training = s7::get('training', $uid);
		$classes = $training['classes'] ? $training['classes'] : array();
		$classes = Util::filterSchemaInfo($classes);
		$ids = array_keys($classes);
		if ($ids) {
			$maxId = max($ids);
		} else {
			$maxId = 0;
		}
		++$maxId;
		$training['classes'][$maxId] = array(
			'classend' => $classEnd,
			'nxtrefresh' => 0,
			'sid' => NULL,
			'cid' => NULL,
			);
        if ($type) {
            $training['classes'][$maxId]['type'] = $type;
        }
		s7::set('training', $uid, $training);

		return $maxId;
	}

	/**
	 * 获得课程信息
	 *
	 * @param Integer	$classId	课程id
	 * @return Array
	 */
	public function getClassInfoById($classId) {
		$allClasses = Configurator::get('module.frontend_training.module.classes');
		$classInfo = NULL;
		if (isset($allClasses['normal'][$classId])) {
			$classInfo = $allClasses['normal'][$classId];
		} else if (isset($allClasses['acting'][$classId])) {
			$classInfo = $allClasses['acting'][$classId];
		} else if (isset($allClasses['sing'][$classId])) {
			$classInfo = $allClasses['sing'][$classId];
		} else if (isset($allClasses['charm'][$classId])) {
			$classInfo = $allClasses['charm'][$classId];
		}
		if (!$classInfo) {
			throw new Exception("非法的课程编号{$classId}");
		}

		return $classInfo;
	}

	/**
	 * 获得我允许参加的课程列表
	 *
	 * @param Integer	$uid	用户id
	 * @param String	$type	课程类型 acting/sing/charm
	 * @return Array
	 */
	public function getClassesICanAttend($uid, $type = 'acting') {
		$allClasses = Configurator::get('module.frontend_training.module.classes');
		$levelLimit = Configurator::get('module.frontend_training.module.level_limit');

		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$companyLevel = $company['level'];
		$training = s7::get('training', $uid);
		$trainingLevel = isset($training['level']) ? $training['level'] : 1;
		$myLimit = $companyLevel * 1000 + $trainingLevel;
		$ables = array();
		foreach ($levelLimit as $key => $conf) {
			if ($myLimit >= $key) {
				$ables[] = $key;
			}
		}
		$classes = array();
		//Add all normal classes
		foreach ($allClasses['normal'] as $id => $item) {
			$classes[$id] = $item;
		}

		$unlockStats = s7::get('training_unlock', $uid);
		foreach ($ables as $able) {
			foreach ($levelLimit[$able][$type] as $cid) { //每门允许的课程
				//判断课程是否解锁成功
				if (isset($unlockStats[$cid]) && $unlockStats[$cid]) {
					$classes[$cid] = $allClasses[$type][$cid];
				}
			}
		}

		return $classes;
	}

	/**
	 * 是否有资格参加该课程
	 *
	 * @param Integer	$uid		用户id
	 * @param Integer	$classId	课程id
	 * @return Boolean
	 */
	public function isAbleAttendClass($uid, $classId) {
		$normalClasses = Configurator::get('module.frontend_training.module.classes.normal');
		if (isset($normalClasses[$classId])) {
			return TRUE;
		}
		$map = Configurator::get('module.frontend_training.module.nameid_map');
		$type = $map[$classId[0]]['id'];
		$classes = $this->getClassesICanAttend($uid, $type);
		if (!isset($classes[$classId])) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 明星参加某课程
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$starId	明星id
	 * @param Integer	$classId	课程id
	 * @param Boolean	$enhanced	是否是强化培训
	 * @param Boolean 	$free		是否免费培训
	 * @return Array
	 *
	 * @throw noMoneyException	金钱不够扣
	 */
	public function starAttendClass($uid, $starId, $roomId, $classId, $enhanced = FALSE, $free = FALSE) {
		if (!$this->isAbleAttendClass($uid, $classId)) {
			throw new Exception("你的明星还不允许参加该课程");
		}
		//判断班级是有空
		$training = $this->getTraining($uid);
		if (!isset($training['classes'][$roomId]) || !$training['classes'][$roomId]['spare']) {
			throw new Exception("没有培训班可用，或者培训班在冷却中，请不要篡改uri。");
		}

		$classInfo = $this->getClassInfoById($classId);

		if ($enhanced) { //扣钻石
			$price = Configurator::get('module.frontend_training.misc.enhanced_training_price');
			$userDb = Module::call('index', 'getUserDb', array($uid));
			if ($userDb < $price) {
				if (!$free) {
					throw new noMoneyException('db');
				}
			}
		}

		$subGb = $classInfo['require']['gb'];
		if ($subGb) {
			$userGb = Module::call('index', 'getUserGb', array($uid));
			if ($userGb < $subGb) {
				if (!$free) {
					throw new noMoneyException('gb');
				}
			}
		}

		if (isset($price) && $price) {
			if (!$free) {
				Module::call('index', 'subUserDb', array($uid, $price));
			}
		}
		if ($subGb) {
			if (!$free) {
				Module::call('index', 'subUserGb', array($uid, $subGb));
			}
		}
		
		//设置参加班级信息
		$studyTime = $classInfo['time'] * 60;
		$training['classes'][$roomId]['nxtrefresh'] = time() + $studyTime;
		$training['classes'][$roomId]['sid'] = $starId;
		$training['classes'][$roomId]['cid'] = $classId;
		s7::set('training', $uid, $training);

		//给培训结果, 先判断先天属性是否符合
		$star = s7::get('star', $starId);
		s7::l($star, 'attrs');
		if ($classId[0] != $star['type']) {
			$effect = $classInfo['nomatch_effect'];
		} else {
			$effect = $classInfo['effect'];
		}
		//判断是否强化培训，加相应
		if ($enhanced) {
			$ratio = Configurator::get('module.frontend_training.misc.enhanced_training_ratio');
			foreach ($effect as $key => &$amount) {
				$amount = $amount + $amount * $ratio;
			}
		}

		//基础学习只加天赋属性
		if ($classId == 1) {
			foreach ($effect as $key => $amount) {
				if ($key != $star['type']) {
					unset($effect[$key]);
				}
			}
		}

		foreach ($effect as $key => $amount) {
			if (isset($star['attrs'][$key])) {
				$star['attrs'][$key] += $amount;
			} else {
				$star['attrs'][$key] = $amount;
			}
		}
		s7::set('star.attrs', $starId, $star['attrs']);

		return array(
			'effect' => $effect,
			'time' => $studyTime,
			);
	}

	/**
	 * 插班 扣钱清除班级培训信息
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$classRoomId	培训班id
	 * @return Boolean
	 *
	 * @throw noMoneyException	钻石不够扣
	 */
	public function bruteforceInClass($uid, $classRoomId) {
		$training = s7::get('training', $uid);

		$now = time();
		$nxtRefresh = $training['classes'][$classRoomId]['nxtrefresh'];
		if ($nxtRefresh <= $now) {
			throw new Exception("培训班空闲中，无需插班。");
		}

		$span = $nxtRefresh - $now;
		$multiples = ceil($span / 3600);
		$price = Configurator::get('module.frontend_training.misc.bruteforce_price_per_hour');
		$price *= $multiples;

		Module::call('index', 'subUserDb', array($uid, $price));

		$training['classes'][$classRoomId]['nxtrefresh'] = 0;
		$training['classes'][$classRoomId]['sid'] = NULL;
		$training['classes'][$classRoomId]['cid'] = NULL;

		s7::set('training', $uid, $training);

		return TRUE;
	}

    /**
     * 获得用户开新班的条件
     *
     * @param Integer   $uid    用户id
     * @return Array
     *
     * @throw OverflowException 不允许再开办普通班
     */
    public function getOpenClassRoomCondition($uid) {
        $training = $this->getTraining($uid);
        //已有普通班数
        $myClasses = 0;
        foreach ($training['classes'] as $classRoom) {
            if (!isset($classRoom['type']) || (isset($classRoom['type']) && ($classRoom['type'] != 'invite' && $classRoom['type'] != 'db'))) {
                $myClasses++;
            }
        }
        $nextClass = $myClasses + 1;
        $classOpenCondition = Configurator::get('module.frontend_training.misc.class_open_condition');
        if (!isset($classOpenCondition)) {
            throw new OverflowException("已达到班级最大数限制, 不能再开办新的培训班。");
        }
        $condition = $classOpenCondition[$nextClass];
        if (isset($condition['props'])) {
            foreach ($condition['props'] as $propId => & $num) {
                $propInfo = Module::call('prop', 'getPropInfo', array($propId));
                $propInfo['num'] = $num;
                $num = $propInfo;
            }
        }

        return $condition;
    }

    /**
     * 获得用户的钻石开办的班级和邀请开办的班级信息
     *
     * @param Integer   $uid    用户id
     * @param String    $type   班级类型 invite/pay
     * @return Array
     */
    public function getSpecialClassRoom($uid, $type = 'pay') {
        $training = $this->getTraining($uid);
        $classRoom = NULL;
        $classRoomId = NULL;

        foreach ($training['classes'] as $id => $class) {
            if (isset($class['type']) && $class['type'] == $type) {
                $classRoomId = $id;
                $classRoom = $class;
                break;
            }
        }

        if ($classRoom) {
            if ($classRoom['classend'] != -1) {
                $now = time();
                if ($classRoom['classend'] > $now) {
                    $classRoom['classend_text'] = '已过期';
                } else {
                    $classRoom['classend_text'] = getTimeSpan($now, $classRoom['classend']);
                }
            } else {
                $classRoom['classend_text'] = '永不过期';
            }
        }

        if ($classRoomId) {
            return array(
                'id' => $classRoomId,
                'room' => $classRoom,
                );
        }

        return $classRoomId;
    }

    /**
     * 使用钻石开通或者延长培训班
     *
     * @param Integer   $uid    用户id
     * @return Boolean
     *
     * @throw noMoneyException
     */
    public function openClassWithDb($uid) {
        $payConfig = Configurator::get('module.frontend_training.misc.open_class_with_diamond');
        $days = $payConfig['days'];
        $amount = $payConfig['price'];
        $now = time();
        $timeAdd = $days * 24 * 3600;

        Module::call('index', 'subUserDb', array($uid, $amount));
        
        $classRoom = $this->getSpecialClassRoom($uid, 'pay');
        if ($classRoom) {
            $id = $classRoom['id'];
            $room = $classRoom['room'];
            if ($room['classend'] > $now) {
                $room['classend'] = $room['classend'] + $timeAdd;
            } else {
                $room['classend'] = $now + $timeAdd;
            }
            $training = s7::get('training', $uid);
            $training['classes'][$id] = $room;
            s7::set('training', $uid, $training);
        } else { //新开
            $classEnd = $now + $timeAdd;
            $this->openClass($uid, $classEnd, 'pay');
        }

        return TRUE;
    }

    /**
     * 判断用户的培训中心是否可以升级
     *
     * @param Integer   $uid    用户id
     * @return Array
     */
    public function isTrainingAbleUpgrade($uid) {
        $training = Module::call('training', 'getTraining', array($uid));
        $level = $training['level'];
        $nextLevel = ++$level;

        $canUpgrade = TRUE;
        $upgradeConf = Configurator::get('module.frontend_training.misc.training_level_upgrade');
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
     * 升级培训中心
     *
     * @param Integer   $uid    用户id
     * @return Integer
     *
     * @throw notMeetException  msg=prop|level 公司等级或者道具不够
     * @throw noMoneyException
     */
    public function upgradeTraining($uid) {
        $ret = $this->isTrainingAbleUpgrade($uid);
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

        $training = s7::get('training', $uid);
        $level = $training['level'];
        $level++;
        $training['level'] = $level;
        s7::set('training', $uid, $training);

        return $level;
    }

	/**
	 * 获得所有课程解锁/待解锁信息
	 *
	 * @param Integer	$uid	用户id
	 * @return Array
	 */
	public function getAllClasses($uid) {
		$classes = array();

		$allClasses = Configurator::get('module.frontend_training.module.classes');
		$levelLimit = Configurator::get('module.frontend_training.module.level_limit');

		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$companyLevel = $company['level'];
		$training = s7::get('training', $uid);
		$trainingLevel = isset($training['level']) ? $training['level'] : 1;
		$myLimit = $companyLevel * 1000 + $trainingLevel;
		$unlockStats = s7::get('training_unlock', $uid);
		$ables = array();

		foreach ($levelLimit as $key => $conf) {
			if ($myLimit >= $key) {
				$ables[] = $key;
			}
		}

		foreach ($ables as $able) {
			if (isset($levelLimit[$able]['acting'])) {
				foreach ($levelLimit[$able]['acting'] as $cid) {
					$classes['acting'][$cid] = $allClasses['acting'][$cid];
					$classes['acting'][$cid]['id'] = $cid;

					if (isset($unlockStats[$cid]) && $unlockStats[$cid]) {
						$classes['acting'][$cid]['unlocked'] = TRUE;
					} else {
						$classes['acting'][$cid]['unlocked'] = FALSE;
					}
				}
			}
			if (isset($levelLimit[$able]['charm'])) {
				foreach ($levelLimit[$able]['charm'] as $cid) {
					$classes['charm'][$cid] = $allClasses['charm'][$cid];
					$classes['charm'][$cid]['id'] = $cid;

					if (isset($unlockStats[$cid]) && $unlockStats[$cid]) {
						$classes['charm'][$cid]['unlocked'] = TRUE;
					} else {
						$classes['charm'][$cid]['unlocked'] = FALSE;
					}
				}
			}
			if (isset($levelLimit[$able]['sing'])) {
				foreach ($levelLimit[$able]['sing'] as $cid) {
					$classes['sing'][$cid] = $allClasses['sing'][$cid];
					$classes['sing'][$cid]['id'] = $cid;

					if (isset($unlockStats[$cid]) && $unlockStats[$cid]) {
						$classes['sing'][$cid]['unlocked'] = TRUE;
					} else {
						$classes['sing'][$cid]['unlocked'] = FALSE;
					}
				}
			}
		}

		return $classes;
	}

	/**
	 * 是否有资格解锁某培训课程
	 *
	 * @param Integer	$uid	用户id
	 * @param Integer	$id		课程id
	 * @param Boolean	$throw	没资格解锁时是否抛出异常
	 * @return Array
	 *
	 * @throw notMeetException prop/level	道具不足或等级不够
	 */
	public function isAbleToUnlockClass($uid, $id, $throw = TRUE) {
		$classes = $this->getAllClasses($uid);
		if (!isset($classes['acting'][$id]) && !isset($classes['charm'][$id]) && !isset($classes['sing'][$id])) {
			throw new Exception("培训项目id非法，请不要篡改uri。");
		}
		if (isset($classes['acting'][$id])) {
			$class = $classes['acting'][$id];
		}
		if (isset($classes['charm'][$id])) {
			$class = $classes['charm'][$id];
		}
		if (isset($classes['sing'][$id])) {
			$class = $classes['sing'][$id];
		}
		if ($class['unlocked']) {
			throw new Exception("该课程你已经解锁过了，不要重复解锁。");
		}
		if (isset($class['unlock_require']['props'])) {
			foreach ($class['unlock_require']['props'] as $pid => &$amount) {
				$propInfo = Module::call('prop', 'getPropInfo', array($pid));
				$propInfo['id'] = $pid;
				$propInfo['num'] = $amount;

				$amount = $propInfo;
			}
		}

		//判断条件是否满足，抛出异常
		//判断培训所等级
		if (isset($class['unlock_require']['level'])) {
			$training = s7::get('training', $uid);
			$trainingLevel = isset($training['level']) ? $training['level'] : 1;
			if ($trainingLevel < $class['unlock_require']['level']) {
				if ($throw) {
					throw new notMeetException('level');
				}
			}
		}

		if (isset($class['unlock_require']['props'])) {
			$freeUnlockChances = s7::get('training_free_unlockchance', $uid);
			$freeUnlockChances = $freeUnlockChances ? $freeUnlockChances : 0;
			//判断道具
			if (!$freeUnlockChances) {
				$userProps = s7::get('user_sprops', $uid);
				foreach ($class['unlock_require']['props'] as $pid => $item) {
					if (!isset($userProps[$pid]) || $userProps[$pid] < $item['num']) {
						if ($throw) {
							throw new notMeetException('prop');
						}
					}
				}
			}
		}

		return $class;
	}

	/**
	 * 解锁培训项目
	 *
	 * @param Integer	$id			用户id
	 * @param Integer	$classId	课程id
	 * @return Boolean
	 *
	 * @throw notMeetException prop/level	道具不足或等级不够
	 */
	public function unlockClass($uid, $classId) {
		$class = $this->isAbleToUnlockClass($uid, $classId, FALSE);

		//Go ahead, 如果有免费机会使用之，否则使用扣道具
		$freeUnlockChances = s7::get('training_free_unlockchance', $uid);
		$freeUnlockChances = $freeUnlockChances ? $freeUnlockChances : 0;
		if ($freeUnlockChances) { //不扣道具, 扣次数
			$freeUnlockChances--;
			s7::set('training_free_unlockchance', $uid, $freeUnlockChances);
		} else { //扣道具
			foreach ($class['unlock_require']['props'] as $pid => $item) {
				$num = $item['num'];
				Module::call('prop', 'subProp', array($uid, $pid, $num));
			}
		}

		$unlockStats = s7::get('training_unlock', $uid);
		$unlockStats[$classId] = TRUE;
		s7::set('training_unlock', $uid, $unlockStats);

		return TRUE;
	}

    //For event processing
	//处理培训所等级升级
	public function _onEventTraining_upgrade(&$params) {
		$uid = $params['uid'];
		$freeUnlockChances = s7::get('training_free_unlockchance', $uid);
		$freeUnlockChances = $freeUnlockChances ? $freeUnlockChances : 0;
		$freeUnlockChances += 1;
		s7::set('training_free_unlockchance', $uid, $freeUnlockChances);

		return TRUE;
	}
}
