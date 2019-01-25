<?php
/**
 * @author greathqy@gmail.com
 * @file   入口文件，初始化游戏数据
 */
class testController extends Controller
{
    public $responseType = 'wml';

    static public $__mapping__ = array(
        'test' => array('indextest.ctl.php', 'indextest.logic.php'),
        );

	public function functional() {
		$this->testRefreshTimer();
		$this->testSubStarConfidence();
	}

	//测试刷新函数
	public function testRefreshTimer() {
		$refreshConf = Configurator::get('module.frontend_prop.misc.clothes_store.refresh_timer');
		$nxt = strtotime('2011-09-08 11:49:50');
		$inst = array(
			'nxtrefresh' => $nxt,
			);
		$ret = Util::isRefreshable($refreshConf, $inst);
		$ret['nxtrefresh'] = date('Y-m-d H:i:s', $ret['nxtrefresh']);
		Debug::dump($ret);
	}

	//测试减少艺人的信心
	public function testSubStarConfidence() {
		$starId = 8877;
		$starAttrs = s7::get('star.attrs', $starId);
		$starAttrs['confidence'] -= 25;
		s7::set('star.attrs', $starId, $starAttrs);
	}

	/**
	 * 公告功能
 	 * @author cmworld@gmail.com
 	 */
	public function testSysNotice(){
		
	}
	
    public function index() {
		s7::set('userinfo.gb', 8848, 88888888);
		/*
		$userinfo = s7::get('userinfo', 8848);
		$companyInfo = s7::get('user_company', $userinfo['company']);
		$companyInfo['level'] = 4;
		s7::set('user_company', $userinfo['company'], $companyInfo);
		Debug::dump($userinfo);
		 */
    }
}
