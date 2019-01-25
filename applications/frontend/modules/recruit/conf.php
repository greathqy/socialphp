<?php
/**
 * @file   招聘中心模块配置文件
 * @author greathqy@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '招聘中心模块',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'recruit' => array(
        'desc' => '招聘中心',
        'type' => 'hash',
        'haveref' => TRUE,
        'defines' => array(
            'level' => 'scalar',    //招聘中心等级
            'visited' => 'bool',     //是否第一次访问
            'nxtrefresh' => 'scalar', //下次刷新时间
            'star1' => '&star',
            'star2' => '&star',
            'star3' => '&star',
            ),
        'storage' => 'mem',
    ),
);

//模块功能相关配置
$config['module'] = array(
	'refresh_timer' => array(
		'interval' => 12, //间隔多少小时
		'start' => 10,	//初次刷新时间, 24小时制
		),
	'refresh_amount' => 1,		//多少宝石可以手动刷新一次
	'refresh_star' => array( //刷新新明星概率. cond: companyLevel x 1000 _ recruitLevel
		1001 => array(
			2 => 'c-',
			100 => 'd',
		),
		2002 => array(
			10 => 'c-',
			100 => 'd',
		),
		3002 => array(
			10 => 'c-',
			100 => 'd',
		),
		4002 => array(
			10 => 'c-',
			100 => 'd',
		),
		5002 => array(
			10 => 'c-',
			100 => 'd',
		),
		6002 => array(
			10 => 'c-',
			100 => 'd',
		),
		7002 => array(
			10 => 'c-',
			100 => 'd',
		),
		8002 => array(
			10 => 'c-',
			100 => 'd',
		),
		9002 => array(
			10 => 'c-',
			100 => 'd',
		),
	),
	'base_star_info' => array( //明星基础信息
		'd' => array(
			'extra' => 2,
			'acting' => 8,
			'sing' => 8,
			'charm' => 8,
			'confidence' => 100,
			),
		'c-' => array(
			'extra' => 2,
			'acting' => 8,
			'sing' => 8,
			'charm' => 8,
			'confidence' => 100,
			),
		'c' => array(
			'extra' => 4,
			'acting' => 8,
			'sing' => 8,
			'charm' => 8,
			'confidence' => 100,
			),
		'c+' => array(
			'extra' => 6,
			'acting' => 8,
			'sing' => 8,
			'charm' => 8,
			'confidence' => 100,
			),
		'b-' => array(
			'extra' => 2,
			'acting' => 10,
			'sing' => 10,
			'charm' => 10,
			'confidence' => 100,
			),
		'b' => array(
			),
		'b+' => array(
			),
		'a-' => array(
			),
		'a' => array(
			),
		'a+' => array(
			),
		's-' => array(
			),
		's' => array(
			),
		's+' => array(
			),
	),
);

//杂类配置
$config['misc'] = array(
	//升级配置
	'upgrades_config' => array(
		2 => array('level' => 2, 'props' => array(130000 => 3)),
		3 => array('level' => 3, 'props' => array(130000 => 3)),
		4 => array('level' => 4, 'props' => array(130000 => 3)),
		5 => array('level' => 5, 'props' => array(130000 => 3)),
		6 => array('level' => 6, 'props' => array(130000 => 3)),
	),
	//艺人取名
	'firstname_pool' => array(
		'王', '赵', '钱', '孙', '李', '陈', '许', '何', '孔', '曹', '周', '吴', '郑', '沈', '朱', '张', '苏', '潘',
		'姜', '杜', '林', '黄', '杨',
	),
	'name1_pool' => array(
		'male' => array(
			'晓', '启', '俊', '石', '希', '子', '致', '建', '世', '文', '凯',
		),
		'female' => array(
			'晓', '静', '嘉', '佳', '巧', '丹', '乐', '雪', '萱', '艺', '雯',
		),
	),
	'name2_pool' => array(
		'male' => array(
			'鹏', '哲', '帆', '波', '维', '海', '超', '远', '洁', '伟', '松',
		),
		'female' => array(
			'云', '涵', '妍', '淇', '琳', '蓉', '婵', '玲', '敏', '晴', '莹',
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
