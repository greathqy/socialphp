<?php
/**
 * @author greathqy@gmail.com
 * @file   入口文件，初始化游戏数据
 */
class indexController extends Controller
{
    public $responseType = 'wml';

    //应用入口
    public function index() {

		$uid = $this->context->get('seluid');
		if ($uid) {
			$_SESSION['seluid'] = $uid;
		}

        $userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		
        $isNewUser = s7::get('new_user_status', $userInfo['uid']);
        if ($isNewUser === NULL || $isNewUser) { 
            Module::call('index', 'createCompany', array($userInfo));
            s7::set('new_user_status', $userInfo['uid'], 0);

            //$this->redirectAction('index', 'first_changename');
        } 
		//判断是否要走新手导航教程
		$needTutorial = Module::call('tutorial', 'isUserNeedApplyTutorial', array($uid));
		if ($needTutorial) {
			$step = 'step' . $needTutorial;
			$this->redirectAction('tutorial', $step);
			exit;
		}

		$userDetail = s7::get('userinfo', $uid);
		$params = array('uid' => $uid);
		Event::trigger('boss_restore_power', $params);

		$userDetail['db'] = 10000;
		s7::l($userDetail, 'gb');
		$userDetail['gb'] = (int) $userDetail['gb'];
        $companyInfo = s7::get('user_company', $userDetail['company']);
		s7::l($companyInfo, 'fame');
		//加载用户体力
		s7::l($userDetail, 'power');
		$userDetail['powerlimit'] = Util::getMyPowerLimit($companyInfo['level']);
		$upgradeInfo = Util::getCompanyUpgradeRequirement($companyInfo, $companyInfo['level'] + 1);

		$links = '';
		if ($companyInfo['hire_num']) {
			$links .= "<a href='" . linkwml('star', 'index') . "'>查看</a>";
		}
		if ($companyInfo['starlimits'] > $companyInfo['hire_num']) {
			if ($links) {
				$links .= '|';
			}
			$links .= "<a href='" . linkwml('recruit', 'candidates') . "'>招聘</a>";
		}
		//显示明星列表
		$starLists = Module::call('star', 'getStarLists', array($uid));
		$starsDisplay = array();
		$moreThanListed = FALSE;
		foreach ($starLists['stars'] as $star) {
			if (sizeof($starsDisplay) < 2) {
				$star['jobinfo'] = Module::call('star', 'buildStarWorkDetail', array($star));

				//完成工作通知 add by liujp
				if($star['jobinfo']['expire'] === TRUE){
					$params = array(
						'uid'    => $uid,
						'star_id' => $star['id'],
						'star_name' => $star['name'],
						'job_id' => $star['jobinfo']['jobid'],
						'job_name' => $star['jobinfo']['jobname'],
					);					
					Module::call('notify', 'starJobFinishedAlert', array($params));
				}

				$starsDisplay[] = $star;
			} else {
				$moreThanListed = TRUE;
				break;
			}
		}
		
		//显示公告通知动态 add by liujp
		$data['notify_sys'] = Module::call('notify', 'getNotifyLast',array('sys',$uid));
		$data['notify_alert'] = Module::call('notify', 'getNotifyLast',array('alert',$uid));
		$data['notify_feed'] = Module::call('notify', 'getNotifyLast',array('feed',$uid));

        $data['__title__'] = '首页';
		$data['restore_power'] = Configurator::get('app.frontend.restore_power_span');
		$data['links'] = $links;
		$data['company'] = $companyInfo;
		$data['userinfo'] = $userDetail;
		$data['upgradeinfo'] = $upgradeInfo;
		$data['stars'] = $starsDisplay;
		$data['more_than_listed'] = $moreThanListed;

        $this->setData($data);
    }

	//公司改名, 花费宝石
	public function changename() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];

		$data = array();
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);

		$this->handleActionSubmit();

		$price = Configurator::get('module.frontend_index.module.company_changename_price');

		$data['__title__'] = '+公司改名+';
		$data['company'] = $company;
		$data['userinfo'] = $userInfo;
		$data['changename_price'] = $price;

		$this->setData($data);
	}

	//公司改名提交
	public function _submit_changename() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$userInfo = s7::get('userinfo', $uid);
		$company = s7::get('user_company', $userInfo['company']);
		$data = array();

		$newName = $this->context->post('companyname');

		$company['name'] = $newName;
		//扣费
		$amount = Configurator::get('module.frontend_index.module.company_changename_price');
		try {
			Module::call('index', 'subUserDb', array($uid, $amount));
			$subed = TRUE;
		} catch (noMoneyException $e) {
			$subed = FALSE;
		}

		if ($subed) {
			s7::set('user_company', $userInfo['company'], $company);
			$data['changed'] = TRUE;
		} else {
			$data['nomoney'] = TRUE;
		}

		$this->setData($data);
	}

	//使用恢复体力道具
	public function useprop() {
		$userInfo = $this->getUserInfo();
		$uid = $userInfo['uid'];
		$page = $this->context->get('page');
		$page = $page ? $page : 1;

		$bagInfo = Module::call('prop', 'getBagInfo', array($uid, 1, 'boss', $page, 'power'));

		$options = array(
			'page_size' => $bagInfo['pagesize'],
			'item_total' => $bagInfo['total'],
			'display_pages' => Configurator::get('module.frontend_prop.misc.display_pages'),
			'current_page' => $page,
			);
		$pagination = Pagination::textual($options, 'index', 'useprop', array());

		$data['__title__'] = '+使用道具+';
		$data['props'] = $bagInfo['props'];
		$data['pagination'] = $pagination;

		$this->setData($data);
	}

    //上街
    public function street() {
        $data = array('__title__' => '街道');

        $this->setData($data);
    }

	//访问朋友公司 .. get:id
	public function cvisit() {
	}
}
