<?php
/**
 * @file   star模块配置文件
 * @author greathqy@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => 'star模块，看明星列表，属性，改名等功能',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'star_pkinfo' => array(
		'desc' => '艺人的pk成就',
		'type' => 'hash',
		'defines' => array(
			'normaljobs' => 'array', //array('id'=>'w,l', ...)
		),
		'storage' => 'mem',
	),
	'star_durable_stat' => array(
        'desc' => '艺人相关的持久的状态',
        'type' => 'mixed', //使用mixed为不限制具体存储格式
        'storage' => 'mem',
    ),
);

//模块功能相关配置
$config['module'] = array(
	'change_name_fee' => 1,	//改名花费一个宝石
	'confidence_restore' => array(10, 1), //十分钟恢复1点
);

//杂类配置
$config['misc'] = array(
	'levels' => array( //明星升级配置
		1 => 0,
		2 => 20,	//等级=>积分
		3 => 40,
		4 => 50,
		5 => 60,
		6 => 70,
		7 => 80,
	),
	'confidencelimits' => array( //明星信心上限
		1 => 100, //明星等级=>上限
		2 => 200,
		3 => 300,
		4 => 400,
		5 => 500,
	),
);


//验证规则
$config['dtds'] = array(
	'@cname' => array(
		'name' => array(
			'value' => '@post',
			'rule' => array(
				"required\t明星名字必须填写",
				"maxlength:20\t明星名字最大长度不超过20个字",
			),
		),
	),
);

//访问控制
$configs['acl'] = array(
);

return $config;
