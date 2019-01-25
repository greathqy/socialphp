<?php
/**
 * @file   道具模块配置文件
 * @author greathqy@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '道具模块',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
 	'user_props' => array(
        'desc' => '用户的道具信息',
        'type' => 'array', //array('propid' => count, ...),
        'storage' => 'mem',
    ),
    'user_sprops' => array(
        'desc' => '用户的特殊道具',
        'type' => 'array', //array('propid' => count, ...),
        'storage' => 'mem',
    ),
    'user_equips' => array(
        'desc' => '用户的装备信息',
        'type' => 'array', //array(id[道具id,用户唯一]=>array('pid'=>道具id, 'sid'=>装备在哪个明星id, 'up'=>false/array()), ...),
        'storage' => 'mem',
    ),
	'user_clothesstore_level' => array(
		'desc' => '用户的服装店解锁到的楼层数',
		'type' => 'scalar',
		'storage' => 'mem',
	),
	'user_clothesstore_floor' => array(
		'desc' => '用户的服装店的一层信息',
		'type' => 'hash',
		'defines' => array(
			'level' => 'scalar',
			'nxtrefresh' => 'scalar',	//下次刷新时间
			'props' => 'array',	//array(array(pid=>xx, flag=>true/false), ...)
		),
		'storage' => 'mem',
	),
);

//模块功能相关配置
$config['module'] = array(
	//道具配置 1开头为道具 2为服饰
	//当为道具时第二位1表示为艺人用，2为老板用 3为特殊道具 
	//当为服饰时1为下身 2为下身 3为配饰
	//后面加4位数字编码, 总共6位
	'storemap' => array(
		1 => array('name' => '食品店', 'type' => 'normal'),
		2 => array('name' => '服饰店', 'type' => 'decorates'),
	),
	'props' => array(
		'normal' => array( //普通道具
			110000 => array(
				'name' => '红牛',
				'desc' => '减少打工时间1.5小时',
				'funny_desc' => '喝了红牛, 提神醒脑，效率大大提升，工作时间减少1.5小时。',
				'effect' => array('time' => -90),	//时间减少1.5小时
				'pricetype' => 'db',	//宝石购买
				'price' => 10,
				'type' => 'star',	//star艺人用 boss老板用
			),
			110001 => array(
				'name' => '脑白金',
				'desc' => '减少打工时间2.5小时',
				'effect' => array('time' => -150),	//时间减少2.5小时
				'pricetype' => 'db',
				'price' => 100,
				'type' => 'star',
			),
			110002 => array(
				'name' => '恢复信心',
				'desc' => '恢复10点信心',
				'effect' => array('confidence' => 10),
				'pricetype' => 'db',
				'price' => 10,
				'type' => 'star',
			),
			120000 => array(
				'name' => '力保健',
				'desc' => '减少打工时间2.5小时',
				'effect' => array('time' => -150),	//时间减少2.5小时
				'pricetype' => 'db',
				'price' => 100,
				'type' => 'boss',
			),		
			120001 => array(
				'name' => '力保健老板版',
				'desc' => '恢复老板体力20点',
				'effect' => array('power' => 20),
				'pricetype' => 'db',
				'price' => 5,
				'type' => 'boss',
			),
		),
		'special' => array(
			130000 => array(
				'name' => '升级石',
				'desc' => '用来升级建筑物',
				'effect' => array(),
				'pricetype' => 'gb',
				'price' => 100,
			),
		),
		'decorates' => array( //翻牌
			210000 => array(
				'name' => '劳力士手表',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 100),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+100',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'd1',
			),
			210001 => array(
				'name' => 'nick鞋',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 30),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+30',
				'pricetype' => 'gb',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'bottom',
			),
			210002 => array(
				'name' => '真维斯毛巾',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 40),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+40',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210003 => array(
				'name' => '阿迪王',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 60),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+60',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'bottom',
			),
			210004 => array(
				'name' => 'qq手机',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 40),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+40',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'd2',
			),
			210005 => array(
				'name' => '瑞士军刀',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 10),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+10',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'd1',
			),
			210006 => array(
				'name' => '登山镐',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 70),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+70',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210007 => array(
				'name' => '跑鞋',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'bottom',
			),
			210008 => array(
				'name' => '火龙果',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210009 => array(
				'name' => '仙人掌',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210010 => array(
				'name' => '腕表',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'd2',
			),
			210011 => array(
				'name' => '游艇',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210012 => array(
				'name' => '玉柱',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210013 => array(
				'name' => '手电筒',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
			210014 => array(
				'name' => '蜡烛',
				'starlevel' => 1,	//明星等级要求
				'sex' => 3,			//1男性，2女性，3无要求
				'effect' => array('charm' => 200),	//魅力+10 | charm, sing, acting
				'desc' => '魅力+200',
				'pricetype' => 'gb',
				'price' =>	10,
				'pos' => 'top',
			),
		),
	),	
);

//杂类配置
$config['misc'] = array(
	'single_prop_daily_limit' => 999,		//每样食品道具每天最多可以消耗x个
	'decorate_redeem_price' => 0.5,		//服饰道具卖出价格
	'bag_per_page_size' => 5,	//背包每页显示几个道具
	'per_page_size' => 8,	//每页显示8个商品
	'display_pages' => 5,	//显示几个分页
	'flip_card_power' => 1,	//翻牌一次需要一点体力

	//服饰店游戏配置
	'clothes_store' => array(
		/*
		'refresh_timer' => array(
			'type' => 'regular',
			'interval' => 12, //间隔多少小时
			'start' => 10,	//初次刷新时间, 24小时制
		),
		 */
		'refresh_timer' => array(
			'type' => 'discrete',
			'hours' => array(11, 12, 15, 17, 23),
		),
		'refresh_amount' => 1,		//多少宝石可以手动刷新一次
		'max_floors' => 5,	//最高楼层数
		'unlock_floor' => array(	//解锁楼层
			2 => array('company_level' => 2, 'props' => array(130000 => 5), 'gb' => 100),
			3 => array('company_level' => 3, 'props' => array(130000 => 5), 'gb' => 100),
			4 => array('company_level' => 4, 'props' => array(130000 => 5), 'gb' => 100),
			5 => array('company_level' => 5, 'props' => array(130000 => 5), 'gb' => 100),
		),
		'floor_config' => array( //楼层信息配置
			1 => array(
				'max_props' => 6,	//最多6个货物
				'possible' => array(
					210000, 210001, 210002, 210003, 210004, 210005, 210006, 210007, 210008, 210009, 210010, 210011, 210012, 210013, 210014,
				),
			),
			2 => array(
				'max_props' => 6,
				'possible' => array(
					210000, 210001, 210002, 210003, 210004, 210005, 210006, 210007, 210008, 210009, 210010, 210011, 210012, 210013, 210014,
				),
			),
			3 => array(
				'max_props' => 6,
				'possible' => array(
					210000, 210001, 210002, 210003, 210004, 210005, 210006, 210007, 210008, 210009, 210010, 210011, 210012, 210013, 210014,
				),
			),
			4 => array(
				'max_props' => 6,
				'possible' => array(
					210000, 210001, 210002, 210003, 210004, 210005, 210006, 210007, 210008, 210009, 210010, 210011, 210012, 210013, 210014,
				),
			),
			5 => array(
				'max_props' => 6,
				'possible' => array(
					210000, 210001, 210002, 210003, 210004, 210005, 210006, 210007, 210008, 210009, 210010, 210011, 210012, 210013, 210014,
				),
			),
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
