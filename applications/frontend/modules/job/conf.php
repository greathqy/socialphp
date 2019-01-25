<?php
/**
 * @file   工作模块配置文件
 * @author greathqy@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '工作模块, 给艺人找工作等',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'jobing_stats' => array( //打工时好友与该艺人的交互 xx_starid
		'desc' => '哪些人与我打工中艺人交互过 帮助or损害',
		'type' => 'array', //array(jobid=>array(array('uid'=>谁发起动作, 'act'=>动作编号, 'tm'=>unixtimestamp, )), ....)
		'storage' => 'mem',
	),
	'job_steal_stats' => array(
		'desc' => '哪些人在我艺人工作完成后 进行过顺手牵羊',
		'type' => 'array', //array('jobid' => array('uid'=> unixtimestamp, ...), ...),),
		'storage' => 'mem',
	),
	'job_center' => array(
		'desc' => '中介中心等级',
		'type' => 'hash',
		'defines' => array(
			'ads_level' => 'scalar',
			'sing_level' => 'scalar',
			'act_level' => 'scalar',
		),
		'storage' => 'mem',
	),
	'same_level_pk' => array(
		'desc' => '同等级pk赛对手存储池',
		'type' => 'hash',
		'defines' => array(
			'level' => 'scalar',
			'members' => 'array',	//array(cid_sid => array('cid'=>, 'sid'=>, 'type'=>'charm/xx'), ...)
		),
		'storage' => 'mem',
	),
);

//模块功能相关配置
$config['module'] = array(
	'pk_fail_harvest' => 80,	//pk失败后工作的收益%
	'struggle_points' => 10,	//挑战一次需要的信心点数
	'typemap' => array(
		1 => array('name' => '广告公司', 'type' => 'ads'),
		2 => array('name' => '唱片公司', 'type' => 'sing'),
		3 => array('name' => '影视公司', 'type' => 'act'),
	),
	//1开头普通工作 2广告工作 3演出工作  4演唱工作
	'job_conf' => array( //所有工作类型的配置
		'all_normal_jobs' => array(
			10000 => array(
				'name' => '基础工作',
				'constraint' => 'charm',
				'constraints' => 60,
				'time' => 1,
				'company_cash' => 400,
				'company_fame' => 4,
				'star_fame' => 4,
			),
			20000 => array(
				'name' => '街头礼仪',
				'constraint' => 'charm',
				'constraints' => 60,
				'time' => 60,
				'company_cash' => 600,
				'company_fame' => 6,
				'star_fame' => 6,
			),
			30000 => array(
				'name' => '龙套演员',
				'constraint' => 'charm',
				'constraints' => 60,
				'time' => 60,
				'company_cash' => 600,
				'company_fame' => 6,
				'star_fame' => 6,
			),
			40000 => array(
				'name' => '街头卖唱',
				'constraint' => 'charm',
				'constraints' => 60,
				'time' => 60,
				'company_cash' => 600,
				'company_fame' => 6,
				'star_fame' => 6,
			),	
		),
		'general' => array(10000), //随时都可以做的工作
		'level_limit' => array( //等级限制工作配置
			2002 => array( //companyLevel * 1000 + jobLevel
				'ads' => array(20000),
				'act' => array(30000),
				'sing' => array(40000),
			),
			4002 => array(
				'ads' => array(20000),
				'act' => array(30000),
				'sing' => array(40000),
			),
		),
		'all_special_jobs' => array(
			50000 => array(
				'name' => '特殊工作',
			),
		),
		'special' => array( //特殊工作配置
		),
	),
	'interaction' => array( //好友互动
		'actions' => array( 	//amount
			1 => array(
				'name' => '给他买水',
				'desc' => '给好友的艺人买水，增加Gb收益',
				'effect' => array('type' => 'inc', 'amount' => 10),	//inc or dec or incratio or decratio
			),
			2 => array(
				'name' => '举报城管',
				'desc' => '举报城管，减少Gb收益',
				'effect' => array('type' => 'dec', 'amount' => 200),
			),
		),
		'steal' => array(
			'name' => '顺手牵羊',	//偷窃动作名称
		),
	),
);

//杂类配置
$config['misc'] = array(
	//好友互动 顺手牵羊
	'friend_steal' => array(
		'type' => 'amount',	//amount OR percent。amount方式表示一次偷多少，percent表示一次偷x%
		'amount' => 10,
	),
	'upgrades_config' => array(
		'ads' => array(
			2 => array('level' => 2, 'props' => array(130000 => 2)),
			3 => array('level' => 2, 'props' => array(130000 => 2)),
			4 => array('level' => 2, 'props' => array(130000 => 2)),
		),
		'sing' => array(
			2 => array('level' => 2, 'props' => array(130000 => 2)),
			3 => array('level' => 2, 'props' => array(130000 => 2)),
			4 => array('level' => 2, 'props' => array(130000 => 2)),
		),
		'act' => array(
			2 => array('level' => 2, 'props' => array(130000 => 2)),
			3 => array('level' => 2, 'props' => array(130000 => 2)),
			4 => array('level' => 2, 'props' => array(130000 => 2)),
		),
	),
);

//验证规则
$config['dtds'] = array(
);

//访问控制
$configs['acl'] = array(
);

return $config;
