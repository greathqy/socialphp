<?php
/**
 * @file	道具模块controller。购买食品服饰，查看背包等。
 * @author	greathqy@gmail.com
 */
class propController extends Controller
{
	//食品店
	public function index() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$storeType = $this->context->get('type');	//1道具店 3服饰店
		if ($storeType == 2) { //服饰店
			$this->redirectAction('prop', 'clothes');
			exit;
		}

		$page = $this->context->get('page');
		$goodType = $this->context->get('gtype');	//艺人用 or 老板用
		$page = $page ? $page : 1;
		$goodType = $goodType ? $goodType : 'star';

		$gb = s7::get('userinfo.gb', $uid);
		$db = Module::call('index', 'getUserDb', array($uid));
		$storeInfo = Module::call('prop', 'getStoreGoods', array($storeType, $goodType, $page));

		$options = array(
			'page_size' => $storeInfo['pagesize'],
			'item_total' => $storeInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $storeInfo['page'],
			);
		$pagination = Pagination::textual($options, 'prop', 'index', array('type' => $storeType, 'gtype' => $goodType));

		$storeTypeMap = Configurator::get('module.frontend_prop.module.storemap');

		$data['__title__'] = $storeTypeMap[$storeType]['name'];
		$data['userinfo'] = $userInfo;
		$data['props'] = $storeInfo['props'];
		$data['gb'] = $gb;
		$data['db'] = $db;
		$data['sel'] = $goodType;
		$data['store_type'] = $storeType;
		$data['pagination'] = $pagination;

		$this->setData($data);
	}

	//服饰店
	public function clothes() {
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

	//翻楼层道具
	public function flipfloorprop() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$floorId = $this->context->get('fid');
		$floorId = $floorId ? $floorId : 1;
		$seq = $this->context->get('seq');

		if (is_null($seq)) {
			throw new Exception("参数错误，没有要翻的货物，请不要篡改url。");
		}

		try {
			$floorInfo = Module::call('prop', 'flipFloorProp', array($uid, $floorId, $seq));
			$propId = $floorInfo['props'][$seq]['pid'];
			$propInfo = Module::call('prop', 'getPropInfo', array($propId));

			$data['propid'] = $propId;
			$data['propinfo'] = $propInfo;
		} catch (noMoneyException $e) {
			$data['no_power'] = TRUE;
		}

		$userPower = s7::get('userinfo.power', $uid);
		$userDetail = s7::get('userinfo', $uid);
		$companyInfo = s7::get('user_company', $userDetail['company']);
		$companyLevel = $companyInfo['level'];
		$powerLimit = Util::getMyPowerLimit($companyLevel);

		$gb = Module::call('index', 'getUserGb', array($uid));
		$db = Module::call('index', 'getUserDb', array($uid));

		$data['__title__'] = '+服饰店+';
		$data['user_gb'] = $gb;
		$data['user_db'] = $db;
		$data['user_power'] = $userPower;
		$data['power_limit'] = $powerLimit;

		$this->setData($data);
	}

	//道具详情
	public function detail() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$id = $this->context->get('id');

		$gb = s7::get('userinfo.gb', $uid);
		$db = Module::call('index', 'getUserDb', array($uid));
		$propInfo = Module::call('prop', 'getPropInfo', array($id));

		$storeTypeMap = Configurator::get('module.frontend_prop.module.storemap');
		$storeType = $propInfo['id'][0];
		
		if ($storeType == 2) { //服饰店
			$data['is_decorate'] = TRUE;
		}
		$data['__title__'] = $storeTypeMap[$storeType]['name'];
		$data['userinfo'] = $userInfo;
		$data['prop'] = $propInfo;
		$data['gb'] = $gb;
		$data['db'] = $db;

		$this->setData($data);
	}

	//购买
	public function buy() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$num = $this->context->get('num');
		$num = $num ? $num : 1;
		$data = array();

		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		if (!$propInfo) {
			throw new Exception("道具编号非法");
		}
		if ($num < 0 || !is_numeric($num)) {
			throw new Exception("道具购买数量非法");
		}

		try {
			$ret = Module::call('prop', 'buyProp', array($uid, $propId, $num));
		} catch (noMoneyException $e) {
			$data['nomoney'] = TRUE;
		}

		$storeTypeMap = Configurator::get('module.frontend_prop.module.storemap');
		$storeType = $propId[0];

		$data['__title__'] = $storeTypeMap[$storeType]['name'];
		$data['type'] = $propInfo['id'][0];
		$data['prop'] = $propInfo;
		$data['num'] = $num;

		$this->setData($data);
	}

	//服饰店重新进货
	public function store_reload() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$floorId = $this->context->get('fid');
		$confirmed = $this->context->get('confirm');
		$floorId = $floorId ? $floorId : 1;

		$data = array();
		$amount = Configurator::get('module.frontend_prop.misc.clothes_store.refresh_amount');

		if (!Module::call('prop', 'isFloorValidForUser', array($uid, $floorId))) {
			throw new Exception("楼层号非法，请不要篡改uri");
		}

		if ($confirmed) { //已确认
			try {
				Module::call('index', 'subUserDb', array($uid, $amount));
				$data['subed'] = TRUE;
			} catch (Exception $e) {
				$data['nomoney'] = TRUE;
			}
		}
		if (isset($data['subed'])) {
			Module::call('prop', 'buildFloorProps', array($uid, $floorId));
			$data['reloaded'] = TRUE;
		}
		$data['__title__'] = '重新进货';
		$data['floorid'] = $floorId;
		$data['amount'] = $amount;

		$this->setData($data);
	}

	//上楼, 解锁楼层
	public function up() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$floorId = $this->context->get('fid');
		$confirmed = $this->context->get('confirm');

		if (!$floorId || $floorId < 1) {
			throw new Exception("请不要篡改uri");
		}

		$data = array();
		$userLevel = s7::get('user_clothesstore_level', $uid);
		$userLevel = $userLevel ? $userLevel : 1;
		if ($userLevel >= $floorId) { //已解锁楼层，直接上楼
			$this->redirectAction('prop', 'clothes', array('fid' => $floorId));
			exit;
		}

		$config = Configurator::get('module.frontend_prop.misc.clothes_store.unlock_floor');
		if (!isset($config[$floorId])) {
			throw new Exception("非法参数, 非法的解锁楼层号");
		} else {
			$config = $config[$floorId];
		}
		if ($config['props']) {
			foreach ($config['props'] as $pid => $count) {
				$propInfo = Module::call('prop', 'getPropInfo', array($pid));
				$config['props'][$pid] = array(
					'name' => $propInfo['name'],
					'count' => $count,
					);
			}
		}
		//解锁条件
		$data['require'] = $config;

		//解锁楼层
		if ($confirmed) {
			try {
				Module::call('prop', 'unlockFloor', array($uid, $floorId));
				$data['unlocked'] = TRUE;
			} catch (notMeetException $e) { //未符合升级条件
				$data['failed'] = TRUE;

				$msg = $e->getMessage();
				if ($msg == 'noprop') {
					$data['noprop'] = TRUE;
				} else if ($msg == 'nolevel') {
					$data['nolevel'] = TRUE;
				} else if ($msg == 'nogb') {
					$data['nogb'] = TRUE;
				}
			}
		}
		$data['floorid'] = $floorId;

		$this->setData($data);
	}

	//我的背包 1普通 2服饰
	public function bag() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$type = $this->context->get('type');
		$gtype = $this->context->get('gtype');	//star|boss|other
		$page = $this->context->get('page');
		$page = $page ? $page : 1;
		$type = $type ? $type : 1;
		if ($type == 1) {
			$gtype = $gtype ? $gtype : 'star';
		} else if ($type == 2) {
			$gtype = $gtype ? $gtype : 'top';
		}

		if (!in_array($type, array(1, 2))) {
			throw new Exception("参数类型不合法，请不要篡改uri");
		}
		if ($type == 1) {
			if (!in_array($gtype, array('boss', 'star', 'other'))) {
				throw new Exception("参数类型不合法，请不要篡改uri");
			}
		} else if ($type == 2) {
			if (!in_array($gtype, array('top', 'bottom', 'de'))) {
				throw new Exception("参数类型不合法，请不要篡改uri");
			}
		}

		$bagInfo = Module::call('prop', 'getBagInfo', array($uid, $type, $gtype, $page));
		$options = array(
			'page_size' => $bagInfo['pagesize'],
			'item_total' => $bagInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $bagInfo['page'],
			);
		$pagination = Pagination::textual($options, 'prop', 'bag', array('type' => $type, 'gtype' => $gtype));

		$data['__title__'] = '+背包+';
		$data['props'] = $bagInfo['props'];
		$data['type'] = $type;
		$data['gtype'] = $gtype;
		$data['pagination'] = $pagination;

		$this->setData($data);
	}

	//消耗道具
	public function consume() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		if (!$propInfo) {
			throw new Exception("非法的道具id");
		}
		if ($propId[0] != 1) { //非普通道具
			throw new Exception("不可使用此道具");
		}
		if ($propId[1] == 3) { //特殊道具
			throw new Exception("不可使用此道具");
		}
		$map = array(
			1 => 'star',
			2 => 'boss',
			);
		$type = 1;
		$gtype = $propId[1];
		$gtype = $map[$gtype];

		$allProps = s7::get('user_props', $uid);
		if (isset($allProps[$propId])) {
			$count = $allProps[$propId];
		} else {
			$count = 0;
		}

		$data = array();

		$data['__title__'] = '+背包+';
		$data['type'] = $type;
		$data['gtype'] = $gtype;
		$data['propinfo'] = $propInfo;
		$data['count'] = $count;

		$this->setData($data);
	}

	//选择明星
	public function selectstar() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('sid');
		$propId = $this->context->get('id');
		$data = array();

		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		if (!$propInfo) {
			throw new Exception("非法的道具id");
		}
		if ($propId[0] != 1) { //非普通道具
			throw new Exception("不可使用此道具");
		}
		if ($propId[1] == 3) { //特殊道具
			throw new Exception("不可使用此道具");
		}
		$workingStars = Module::call('star', 'getUserWorkingStars', array($uid));

		$data['__title__'] = '+使用道具+';
		$data['working_stars'] = $workingStars;
		$data['propinfo'] = $propInfo;

		$this->setData($data);
	}

	//正式消耗道具
	public function star_consume() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$starId = $this->context->get('sid');
		$propId = $this->context->get('id');

		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你的明星，不能对它使用道具。");
		}
		
		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		if (!$propInfo) {
			throw new Exception("非法的道具id");
		}
		if ($propId[0] != 1) { //非普通道具
			throw new Exception("不可使用此道具");
		}
		if ($propId[1] == 3) { //特殊道具
			throw new Exception("不可使用此道具");
		}

		$canConsume = FALSE;
		try {
			Module::call('prop', 'isUserAbleConsumeProp', array($uid, $starId, $propId));
			$canConsume = TRUE;
		} catch (OverflowException $e) {
			$data['cant_use'] = TRUE;	//超过当日使用次数限制
		} catch (notMeetException $e) {
			$data['star_not_in_job'] = TRUE;	//明星不在工作状态
		} catch (noMoneyException $e) {
			$data['prop_not_enough'] = TRUE;	//道具数量不够
		}

		$star = s7::get('star', $starId);
		$star['id'] = $starId;

		if ($canConsume) {
			Module::call('prop', 'starConsumeProp', array($uid, $starId, $propId));
			$star = s7::get('star', $starId);
			$star['id'] = $starId;
			$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));

			$data['consumed'] = TRUE;
		}

		$data['star'] = $star;
		$data['__title__'] = '+使用道具+';
		$data['propinfo'] = $propInfo;

		$this->setData($data);
	}

	//老板消耗道具
	public function boss_consume() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');	

		$propInfo = Module::call('prop', 'getPropInfo', array($propId));
		if (!$propInfo) {
			throw new Exception("非法的道具id");
		}
		if ($propId[0] != 1) { //非普通道具
			throw new Exception("不可使用此道具");
		}
		if ($propId[1] != 2) { //不是老板可用道具
			throw new Exception("不可使用此道具");
		}

		$canConsume = FALSE;
		try {
			Module::call('prop', 'isUserAbleConsumeProp', array($uid, NULL, $propId));
			$canConsume = TRUE;
		} catch (OverflowException $e) {
			$data['cant_use'] = TRUE;	//超过当日使用次数限制
		} catch (noMoneyException $e) {
			$data['prop_not_enough'] = TRUE;	//道具数量不够
		}

		if ($canConsume) {
			Module::call('prop', 'bossConsumeProp', array($uid, $propId));

			$data['consumed'] = TRUE;
		}

		$data['__title__'] = '+使用道具+';
		$data['propinfo'] = $propInfo;

		$this->setData($data);
	}

	//卸下装备
	public function undecorate() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$confirmed = $this->context->get('confirm');

		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));

		if ($confirmed) {
			Module::call('prop', 'undecorateUserProp', array($uid, $propId));
			$data['undecorated'] = TRUE;
		}

		$data['__title__'] = '+卸下装备+';
		$data['propinfo'] = $propInfo;

		$this->setData($data);
	}

	//装备道具 选择艺人
	public function decorate() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');

		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));
		$stars = Module::call('star', 'getStarsDecorateDetail', array($uid));

		$data['propinfo'] = $propInfo;
		$data['stars'] = $stars;

		$this->setData($data);
	}

	//进行装备服饰
	public function do_decorate() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('pid');
		$starId = $this->context->get('sid');
		$pos = $this->context->get('pos');
		$redirect = $this->context->get('redirect');

		if (!in_array($pos, array('top', 'bottom', 'd1', 'd2'))) {
			throw new Exception("非法装饰部位。");
		}
		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));
		$star = s7::get('star', $starId);
		if (!$star || !$propInfo) {
			throw new Exception("明星或道具参数非法，请不要篡改uri");
		}
		if ($pos != $propInfo['pos']) {
			throw new Exception("该道具不允许装备到这个部位。");
		}
		if (!Util::isStarBelongToUser($starId, $uid)) {
			throw new Exception("不是你自己的明星，不允许给ta装备。");
		}
		$canDecorate = TRUE;
		//名气要求是否达到
		if ($star['level'] < $propInfo['starlevel']) {
			$canDecorate = FALSE;
			$data['level_not_enough'] = TRUE;
		}
		//性别要求是否达到
		if ($propInfo['sex'] != 3) {
			if ($propInfo['sex'] != $star['sex']) {
				$canDecorate = FALSE;
				$data['sex_miss_match'] = TRUE;
			}
		}

		if ($canDecorate) {
			$ret = Module::call('prop', 'decoratePositionOfStar', array($uid, $starId, $pos, $propId));
			$data['removed'] = $ret['removed'];
			$data['decorated'] = $ret['decorated'];
			if($redirect) {
				$this->redirectAction('star', 'choose_de', array('id' => $starId, 'pos' => $pos));
				exit;
			}
		}

		$data['star'] = $star;

		$this->setData($data);
	}

	//装饰品信息
	public function dedetail() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));

		$position = $propInfo['pos'];
		if ($propInfo['pos'] == 'd1' || $propInfo['pos'] == 'd2') {
			$position = 'de';
		}
		$propInfo['sex_text'] = getSexTypeTextual($propInfo['sex']);

		$data['__title__'] = '+背包+';
		$data['propinfo'] = $propInfo;
		$data['type'] = 2;
		$data['gtype'] = $position;

		$this->setData($data);
	}

	//卖出, 回收服饰
	public function redeem() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$confirmed = $this->context->get('confirm');

		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));
		$redeemPrice = Module::call('prop', 'getDePropRedeemPrice', array($uid, $propId));

		if ($confirmed) {
			Module::call('prop', 'redeemUserDeProp', array($uid, $propId));
			$data['redeemed'] = TRUE;
		}

		$data['propinfo'] = $propInfo;
		$data['redeem_price'] = $redeemPrice;

		$this->setData($data);
	}

	//丢弃服饰
	public function drop() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$propId = $this->context->get('id');
		$confirmed = $this->context->get('confirm');

		$propInfo = Module::call('prop', 'getDePropInfo', array($uid, $propId));

		if ($confirmed) {
			Module::call('prop', 'dropUserDeProp', array($uid, $propId));
			$data['droped'] = TRUE;
		}

		$data['propinfo'] = $propInfo;

		$this->setData($data);
	}
}
