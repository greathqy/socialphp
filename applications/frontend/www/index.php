<?php
session_start();
$start = microtime(TRUE);
$curDir = getcwd();
include(dirname(dirname(dirname($curDir))) . DIRECTORY_SEPARATOR . 'startup.php');

$meditator = Factory::create('meditator');
$meditator->setDefaultRender('wml')
          ->setRouter('http');
$options = array(
    'proj_dir' => SYS_ROOT,
    'app_name' => 'frontend',
    );
$meditator->service($options);

$end = microtime(TRUE);
$total = $end - $start;
$total = array('total time' => $total);
Debug::dump($total);

define('IS_XHPROF_OPEN', FALSE);
##############  随机万分之一用户开启xhprof跟踪  #######################
$xhprof_switch = false;
if (defined("IS_XHPROF_OPEN") && IS_XHPROF_OPEN === true) {
	$rand_num = rand(1,10000);
	if ($rand_num === 1 && function_exists("xhprof_enable")) {
		$xhprof_switch = true;
	}
}
##############根据记录开关启动xhprof跟踪##########################
$xhprof_switch &&  xhprof_enable();

define("TIME_STAMP_NOW", time());
##############根据开关xhprof启动结束#######################################
if ($xhprof_switch) {
	$xhprof_data = xhprof_disable();
	require_once(APPLICATON_ROOT."/www/xhprof_lib/utils/xhprof_lib.php");
	require_once(APPLICATON_ROOT."/www/xhprof_lib/utils/xhprof_runs.php");
	$xhprof_runs = new XHProfRuns_Default();
	$xhprof_runs->save_run($xhprof_data, "king_prof_foo");
}
