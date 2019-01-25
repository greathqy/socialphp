<?php
/**
 * @file   培训模块配置文件
 * @author greathqy@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '培训模块',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'training' => array(
		'desc' => '培训所',
		'type' => 'hash',
		'defines' => array(
			'level' => 'scalar',	//培训中心等级
			'classes' => 'array',	//array(id=>array('type'=>'pay/invite/normal', 'classend'=>-1/班级到期时间, 'nxtrefresh' => 冷却时间, sid=>在培训哪个兔崽子, cid=>课程id)),
		),
		'storage' => 'mem',
	),
	'training_unlock' => array(
		'desc' => '培训课程解锁信息',
		'type' => 'array',	//array('id'=>true/false)
		'storage' => 'mem',
	),
	'training_free_unlockchance' => array(
		'desc' => '可用免费解锁次数信息',
		'type' => 'scalar',
		'storage' => 'mem',
	),
);

//模块功能相关配置
$config['module'] = array(	
	'nameid_map' => array( //名字id对应表
		1 => array('id' => 'acting', 'text' => '演技'),
		2 => array('id' => 'sing', 'text' => '歌艺'),
		3 => array('id' => 'charm', 'text' => '魅力'),
	),
	'skill_id_map' => array(
		'acting' => array('id' => 1, 'text' => '演技'),
		'sing' => array('id' => 2, 'text' => '歌艺'),
		'charm' => array('id' => 3, 'text' => '魅力'),
	),
	'classes' => array( //课程列表
		'normal' => array(
			1 => array(
				'name' => '基础学习',
				'time' => 60,
				'require' => array('gb' => 1000,),
				'effect' => array('charm' => 6, 'acting' => 6, 'sing' => 6),
				'nomatch_effect' => array('charm' => 6, 'acting' => 6, 'sing' => 6),	//属性不匹配时的收益
				'desc' => '增加6点魅力',
			),
		),
		'acting' => array(
			1000001 => array(
				'name' => '基本表演',
				'time' => 60,
				'require' => array('gb' => 1000,),
				'effect' => array('acting' => 10),
				'nomatch_effect' => array('acting' => 8),
				'unlock_require' => array('level' => 1, 'props' => array(130000 => 1)),
				'desc' => '增加10点演技',
			),
		),
		'sing' => array(
			2000001 => array(
				'name' => '基本唱功',
				'time' => 60,
				'require' => array('gb' => 1000,),
				'effect' => array('sing' => 10),
				'nomatch_effect' => array('sing' => 8),
				'unlock_require' => array('level' => 1, 'props' => array(130000 => 1)),
				'desc' => '增加10点唱功',
			),
		),
		'charm' => array(
			3000001 => array(
				'name' => '基本礼仪',
				'time' => 60,
				'require' => array('gb' => 1000,),
				'effect' => array('charm' => 10,),
				'nomatch_effect' => array('charm' => 8),
				'unlock_require' => array('level' => 1, 'props' => array(130000 => 1)),
				'desc' => '增加10点魅力',
			),
		),
	),
	'level_limit' => array( //等级对应可参加培训班
		4002 => array(
			'acting' => array(1000001),
			'sing' => array(2000001),
			'charm' => array(3000001),
		),
	),
);

//杂类配置
$config['misc'] = array(
	'enhanced_training_price' => 1,	//强化培训消耗一颗钻石
	'enhanced_training_ratio' => 0.5,	//强化培训加强比率
	'bruteforce_price_per_hour' => 1,	//插班每小时花费1个钻石

    'max_class_rooms' => 6, //最大班级数
    'max_class_invite_pay' => 2,    //邀请和支付总共可开班级数
    'training_level_upgrade' => array(
        2 => array('level' => 10, 'props' => array(130000 => 3)), //公司等级，道具
        3 => array('level' => 20, 'props' => array(130000 => 4)),
        4 => array('level' => 30, 'props' => array(130000 => 6)),
    ),
    'level_classes_limit' => array(   //培训所等级=>可开班数
        1 => 1,
		2 => 2,	
		3 => 3,
		4 => 4,
	),
    'class_open_condition' => array(
        1 => array(), //首班默认开通
        2 => array('level' => 2, 'props' => array(130000 => 3)), //需要培训中心等级, 道具
        3 => array('level' => 3, 'props' => array(130000 => 4)),
        4 => array('level' => 4, 'props' => array(130000 => 5)),
    ),
    //钻石开班, 10钻石7天班
	'open_class_with_diamond' => array('limit' => 1, 'price' => 10, 'days' => 7),
    //俩好友7天班
    'open_class_with_invite' => array('limit' => 1, 'invite' => 2, 'days' => 7),
);

//验证规则
$config['dtds'] = array(
);

//访问控制
$configs['acl'] = array(
);

return $config;
