<?php
/**
 * @file	培训模块controller
 * @author	greathqy@gmail.com
 */
class trainingController extends Controller
{
	//培训所首页
	public function index() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$training = Module::call('training', 'getTraining', array($uid));	

		$data['__title__'] = '+培训中心+';
		$data['training'] = $training;

		$this->setData($data);
	}

	//参加培训班
	public function attend() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$classRoomId = $this->context->get('id');	//培训班序号
		$class = $this->context->get('class');
		$class = $class ? $class : 'sing';
		if (!in_array($class, array('sing', 'acting', 'charm'))) {
			throw new Exception("培训项目不合法");
		}
		$classes = Module::call('training', 'getClassesICanAttend', array($uid, $class));

		$data['__title__'] = '+培训中心+';
		$data['classes'] = $classes;
		$data['class'] = $class;
		$data['class_id'] = $classRoomId;

		$this->setData($data);
	}

	//培训项目介绍
	public function classintro() {
		$class = $this->context->get('class');
		$class = $class ? $class : 'sing';
		$classId = $this->context->get('id');
		$classRoomId = $this->context->get('cid');

		$classInfo = Module::call('training', 'getClassInfoById', array($classId));

		$map = Configurator::get('module.frontend_training.module.nameid_map');
		$effect_text = $map[$classId[0]]['text']; 
		$now = time();
		$classInfo['time'] = getTimeSpan($now, $now + $classInfo['time'] * 60);

		$data['__title__'] = '+培训中心+';
		$data['classinfo'] = $classInfo;
		$data['classroom_id'] = $classRoomId;
		$data['class'] = $class;
		$data['effect'] = key($classInfo['effect']);
		$data['effect_text'] = $effect_text;
		$data['class_id'] = $classId;
		$data['enhance_price'] = Configurator::get('module.frontend_training.misc.enhanced_training_price');

		$this->setData($data);
	}

	//选择明星参加培训班
	public function selectstar() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$class = $this->context->get('class');
		$class = $class ? $class : 'sing';
		$classId = $this->context->get('id');
		$enhanced = $this->context->get('enhance');
		$enhanced = $enhanced ? $enhanced : 0;
		$classRoomId = $this->context->get('cid');
		$classRoomId = $classRoomId ? $classRoomId : 1;

		$starInfo = Module::call('star', 'getStarLists', array($uid, TRUE));
		$classInfo = Module::call('training', 'getClassInfoById', array($classId));

		$classIdFirst = $classId[0];
		$map = Configurator::get('module.frontend_training.module.nameid_map');

		foreach ($starInfo['stars'] as $i => &$star) {
			$star['type_text'] = getStarTypeTextual($star['type']);
		}

		$data['classroom_id'] = $classRoomId;
		$data['class_id'] = $classId;
		$data['classinfo'] = $classInfo;
		$data['stars'] = $starInfo['stars'];
		$data['effect'] = key($classInfo['effect']);
		$data['effect_text'] = $map[$classIdFirst]['text'];
		$data['enhanced'] = $enhanced;

		$this->setData($data);
	}

	//开始培训
	public function start() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('sid');
		$classId = $this->context->get('cid');
		$roomId = $this->context->get('rid');
		$roomId = $roomId ? $roomId : 1;
		$enhanced = $this->context->get('enhance');
		$enhanced = $enhanced ? $enhanced : 0;

		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星，不可以安排ta参加培训。");
		}

		try {
			$ret = Module::call('training', 'starAttendClass', array($uid, $starId, $roomId, $classId, $enhanced));
		} catch (noMoneyException $e) {
			$data['failed'] = TRUE;
			$type = $e->getMessage();
			if ($type == 'gb') {
				$data['nogb'] = TRUE;
			} else if ($type == 'db') {
				$data['nodb'] = TRUE;
			}
		}

		if (!isset($data['failed'])) { //加工ret
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
		}

		$star = s7::get('star', $starId);
		$star['id'] = $starId;

		$data['star'] = $star;
		$data['__title__'] = '+培训结果+';

		$this->setData($data);
	}

	//插班
	public function bruteforce() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$classRoomId = $this->context->get('id');
		$classRoomId = $classRoomId ? $classRoomId : 1;
		$confirmed = $this->context->get('confirm');

		$training = s7::get('training', $uid);
		if (!isset($training['classes'][$classRoomId])) {
			throw new Exception("培训班不存在, 请不要篡改uri");
		}

		$now = time();
		$training['classes'][$classRoomId]['still'] = getTimeSpan($now, $training['classes'][$classRoomId]['nxtrefresh']);
		$bruteforcePrice = Configurator::get('module.frontend_training.misc.bruteforce_price_per_hour');
		$multiples = ceil($training['classes'][$classRoomId]['nxtrefresh'] - $now) / 3600;
		$price = $bruteforcePrice * $multiples;

		if ($confirmed) {
			try {
				$ret = Module::call('training', 'bruteforceInClass', array($uid, $classRoomId));
				$data['succ'] = TRUE;
			} catch (noMoneyException $e) {
				$data['failed'] = TRUE;
			}
		}

		$data['__tittle__'] = '培训中心';
		$data['classroom_id'] = $classRoomId;
		$data['training'] = $training;
		$data['price'] = $price;

		$this->setData($data);
	}

	//开新培训班
	public function openclassroom() {
        $userInfo = $this->getUserInfo();
        $uid = $userInfo['uid'];

        $training = Module::call('training', 'getTraining', array($uid));

        $noInviteClass = TRUE;
        $noPayClass = TRUE;
        foreach ($training['classes'] as $class) {
            if (isset($class['type'])) {
                if ($class['type'] == 'invite') {
                    $noInviteClass = FALSE;
                } else if ($class['type'] == 'pay') {
                    $noPayClass = FALSE;
                }
            }
        }

        try {
            $condition = Module::call('training', 'getOpenClassRoomCondition', array($uid));
            $data['condition'] = $condition;
        } catch (OverflowException $e) {
            $data['cant_open_class'] = TRUE;
        }

        $data['__title__'] = '+培训中心+';
        $data['max_class_rooms'] = Configurator::get('module.frontend_training.misc.max_class_rooms');
        $data['max_class_invite_pay'] = Configurator::get('module.frontend_training.misc.max_class_invite_pay');
        $data['my_class_rooms'] = sizeof($training['classes']);
        $data['not_have_invite_class'] = $noInviteClass;
        $data['not_have_pay_class'] = $noPayClass;
        $data['pay'] = Configurator::get('module.frontend_training.misc.open_class_with_diamond');
        $data['invite'] = Configurator::get('module.frontend_training.misc.open_class_with_invite');

        $this->setData($data);
	}

    //正式开班, 普通
    public function openclass() {
        $userInfo = $this->getUserInfo();
        $uid = $userInfo['uid'];

        $condition = Module::call('training', 'getOpenClassRoomCondition', array($uid));
        $level = $condition['level'];
        $props = $condition['props'];

        //允许开办
        $allowed = TRUE;
        $training = Module::call('training', 'getTraining', array($uid));
        if ($training['level'] < $level) {
            $allowed = FALSE;
            $data['level_not_meet'] = TRUE;
        }

        if ($allowed) {
            $missProps = array();

            $allProps = s7::get('user_sprops', $uid);
            $allProps = $allProps ? $allProps : array();
            foreach ($props as $pid => $item) {
                $num = $item['num'];
                $infact = isset($allProps[$pid]) ? $allProps[$pid] : 0;
                if ($infact < $num) {
                    $missProps[$pid] = array('name' => $item['name'], 'required' => $num, 'infact' => $infact);
                }
            }

            if ($missProps) { //缺少道具
                $allowed = FALSE;
                $data['prop_not_meet'] = TRUE;
                $data['missed_props'] = $missProps;
            }
        }

        if ($allowed) { //扣道具，开班
            foreach ($props as $pid => $item) {
                Module::call('prop', 'subProp', array($uid, $pid, $item['num']));
            }
            Module::call('training', 'openClass', array($uid));
        }

        $data['__title__'] = '+培训中心+';
        $data['allowed'] = $allowed;

        $this->setData($data);
    }

    //钻石开班
    public function dbopen() {
        $userInfo = $this->getUserInfo();
        $uid = $userInfo['uid'];
        $confirmed = $this->context->get('confirm');

        $training = Module::call('training', 'getTraining', array($uid));
        $payConfig = Configurator::get('module.frontend_training.misc.open_class_with_diamond');

        $classRoom = Module::call('training', 'getSpecialClassRoom', array($uid, 'pay'));

        if ($confirmed) {
            try {
                Module::call('training', 'openClassWithDb', array($uid));
                if ($classRoom) {
                    $data['prolonged'] = TRUE;
                } else {
                    $data['opened'] = TRUE;
                }
            } catch (noMoneyException $e) {
                $data['failed'] = TRUE;
            }
        }

        $data['__title__'] = '+培训中心+';
        $data['payconfig'] = $payConfig;
        $data['classroom'] = $classRoom;

        $this->setData($data);
    }

    //升级
    public function upgrade() {
        $userInfo = $this->getUserInfo();
        $uid = $userInfo['uid'];

        $training = Module::call('training', 'getTraining', array($uid));
        $level = $training['level'];
        $nextLevel = $level + 1;
        $ret = Module::call('training', 'isTrainingAbleUpgrade', array($uid));
        $canUpgrade = $ret['can_upgrade'];
        $required = $ret['required'];

        $data['__title__'] = '+培训中心+';
        $data['can_upgrade'] = $canUpgrade;
        $data['cur_level'] = $level;
        $data['next_level'] = $nextLevel;
        $data['required'] = $required;

        $this->setData($data);
    }

    //进行升级
    public function doupgrade() {
        $userInfo = $this->getUserInfo();
        $uid = $userInfo['uid'];

        $ret = Module::call('training', 'isTrainingAbleUpgrade', array($uid));
        if (!$ret['can_upgrade']) {
            throw new Exception("你的培训中心不能再升级了，请不要篡改uri玩游戏。");
        }
		$data['upgraded'] = FALSE;
        try {
            Module::call('training', 'upgradeTraining', array($uid));
            $data['upgraded'] = TRUE;
        } catch (notMeetException $e) {
            $data['failed'] = TRUE;

            $msg = $e->getMessage();
            if ($msg == 'level') {
                $data['level_not_meet'] = TRUE;
            } else if ($msg == 'prop') {
                $data['prop_not_meet'] = TRUE;
            }
        }

 		$params = array(
			'uid' => $uid,
			);
		Event::trigger('training_upgrade_feed', $params);       
        
        $data['__title__'] = '+培训中心+';

        $this->setData($data);
    }
    
    //解锁培训项目
    public function unlock() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$freeUnlockChances = s7::get('training_free_unlockchance', $uid);
		$freeUnlockChances = $freeUnlockChances ? $freeUnlockChances : 0;

		$classes = Module::call('training', 'getAllClasses', array($uid));

		$data['__title__'] = '+培训中心+';
		$data['free_unlock_chances'] = $freeUnlockChances;
		$data['unlocks'] = $classes;

		$this->setData($data);
    }

	//解锁培训项目简介
	public function unlockintro() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$id = $this->context->get('id');

		try {
			$class = Module::call('training', 'isAbleToUnlockClass', array($uid, $id, FALSE));
		} catch (notMeetException $e) {
		}

		$data['__title__'] = '+培训中心+';
		$data['class'] = $class;

		$this->setData($data);
	}

	//进行解锁
	public function dounlock() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$id = $this->context->get('id');

		try {
			Module::call('training', 'unlockClass', array($uid, $id));
			$data['succ'] = TRUE;
		} catch (notMeetException $e) {
			$data['failed'] = TRUE;
			$msg = $e->getMessage();
			if ($msg == 'prop') {
				$data['prop_not_meet'] = TRUE;
			} else if ($msg == 'level') {
				$data['level_not_meet'] = TRUE;
			}
		}
		$class = Module::call('training', 'getClassInfoById', array($id));

		$data['__title__'] = '+培训中心+';
		$data['class'] = $class;

		$this->setData($data);
	}
}
