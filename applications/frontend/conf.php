<?php
/**
 * @author greathqy@gmail.com
 * @file   应用层级配置文件
 */
$config = array(
	'star_types' => array(
		1 => '演技型',
		2 => '歌艺型',
		3 => '魅力型',
	),
	'star_types_en' => array(
		1 => 'acting',
		2 => 'sing',
		3 => 'charm',
	),
	'sexes' => array(
		1 => '男',
		2 => '女',
		3 => '不限',
	),
	'sex_en_map' => array(
		1 => 'male',
		2 => 'female',
	),
	'restore_power_span' => array(1, 1),	//每分钟恢复一点体力, array(时间, 分钟)
	'level_training_limit' => array( //各级别明星能达到的最终训练效果
		'd' => array('level' => 10, 'skill' => 100), //最高10级，单项技能100点
		'c' => array('level' => 10, 'skill' => 200),
		'b' => array('level' => 10, 'skill' => 300),
		'a' => array('level' => 10, 'skill' => 300),
		's' => array('level' => 10, 'skill' => 300),
	),
);


return $config;
