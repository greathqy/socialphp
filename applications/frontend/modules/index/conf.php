<?php
/**
 * @author greathqy@gmail.com
 * @file   模块配置文件, 格式基本可以自由书写
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '核心模块，实现sns信息注册，艺人招聘等主流程',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'new_user_status' => array(
		'type' => 'scalar',
		'storage' => 'mem',
	),
    'userinfo' => array(
        'desc' => '用户的基本信息',
        'type' => 'hash',
		'haveref' => TRUE,
        'defines' => array(
            'oid' => 'string',  //sns open id
            'nickname' => 'string',
            'age' => 'scalar',
            'sex' => 'scalar',
            'snsvip' => 'scalar',
			'company' => 'scalar',	//公司的id
            'gb' => '&user_gb', //gb数
			'power' => '&user_power',
            'db' => 'scalar',   //db 钻石数
            ),
        'storage' => 'mem',
    ),
	'user_power' => array(
		'desc' => '用户的体力',
		'type' => 'scalar',
		'storage' => 'mem',
	), 
    'user_gb' => array(
        'desc' => '用户的GB数',
        'type' => 'scalar',
        'storage' => 'mem',
    ),
    'uid_oid_mapping' => array(
        'desc' => '用户uid到sns oid的映射关系',
        'type' => 'string',
        'storage' => 'mem',
    ),
    'oid_uid_mapping' => array(
        'desc' => 'sns oid到用户uid的映射关系',
        'type' => 'string',
        'storage' => 'mem',
    ), 
    'user_company' => array(
        'desc' => '用户的公司资料',
        'type' => 'hash',
		'haveref' => TRUE,
        'defines' => array(
            'name' => 'string',
            'level' => 'scalar', //公司等级
            'fame' => '&company_fame',
            'cash' => '&company_cash',
            'hire_num' => 'scalar',   //雇佣的艺人数量
            'fired_num' => 'scalar',   //解雇了的艺人数量
            'starlimits' => 'scalar',
			'stars' => 'array', //array(1, 2, 3)	//在雇佣的明星列表
			'fired_stars' => 'array',	//array(1, 2, 3), //解雇了的明星列表
            'achieve' => '&company_achieve',
            ),
        'storage' => 'mem',
    ),
    'company_fame' => array(
        'desc' => '公司的名气',
        'type' => 'scalar',
        'storage' => 'mem',
    ),
    'company_cash' => array(
        'desc' => '公司的现金',
        'type' => 'scalar',
        'storage' => 'mem',
    ),
    'company_achieve' => array(
        'desc' => '公司成就信息', 
        'type' => 'hash',
        'defines' => array(
            'hires' => 'scalar',
            'films' => 'scalar',
            'tvs' => 'scalar',
            'ads' => 'scalar',
            'discs' => 'scalar',
            ),
        'storage' => 'mem',
    ),
    'star_achieve' => array(
        'desc' => '艺人个人成就信息',
        'type' => 'hash',
        'defines' => array(
            'films' => 'scalar',
            'tvs' => 'scalar',
            'ads' => 'scalar',
            'discs' => 'scalar',
            ),
        'storage' => 'mem',
    ),	
    'star' => array(
        'desc' => '明星资料',
        'type' => 'hash',
        'haveref' => TRUE,
        'defines' => array(
            'name' => 'string',
			'level' => 'scalar',		//等级
            'cnamefree' => 'scalar',    //免费改名次数
            'sex' => 'scalar',
            'type' => 'scalar', //艺人类型属性
			'talent' => 'string',	//a b- b b+ and such on
			'confidence' => 'scalar',	//信心值上限
            'attrs' => '&star_attrs',
            'jobing' => 'array',    //正在做什么 array('jobid' =>xx, 'end'=>xx, 'percent'=>收益比)
            'achieve' => '&star_achieve',
            'equip' => 'array', //array('top'=>xx,'bottom'=>xx,'d1'=>xx,'d2'=>xx)
            ),
        'storage' => 'mem',
    ),
    'star_attrs' => array(
        'desc' => '明星易变属性',
		'__!standalone__' => TRUE,	//该schema不单独出现在后台
        'type' => 'hash',
        'defines' => array(
            'fame' => 'scalar',	//名声
            'acting' => 'scalar',	//演技
            'sing' => 'scalar',	//歌艺
            'charm' => 'scalar',	//魅力
			'confidence' => 'scalar',	//信心
            ),
        'storage' => 'mem',
    ),
    'user_durable_stat' => array(
        'desc' => '用户相关的持久的状态',
        'type' => 'mixed', //使用mixed为不限制具体存储格式
        'storage' => 'mem',
    ),

	/*
	'job' => array(
        'desc' => '打工 普通工作',
        'type' => 'hash',
        'defines' => array(
            'doing' => 'scalar',    //目前有多少项目在干
            'detail' => 'array',    //array(array('ref'=>array('cid_sid', ...), 'jobid'=>'工作id','percent'=>收益,), ...)
            ),
        'storage' => 'mem',
    ), 
	 */
    'equipment' => array(
        'desc' => '升级过的装备信息',
        'type' => 'array',
        'defines' => array(
            'attrs' => 'array', //array('attr1' =>xx, 'attr2' => xx),
            ),
        'storage' => 'mem',
    ),	
	'uid_oid_dbmapping' => array(
        'desc' => 'uid到sns oid的数据库映射关系',
        'type' => 'hash',
        'defines' => array(
            '__mysql__' => array(
                'primary_key' => array('field' => 'uid', 'autoincrement' => TRUE),
                ),
            'oid' => 'string',
            ),
        'storage' => 'mysql',
    ),
	'company_list' => array(	
		'desc' => '公司创建时间登记表, 生成公司id',
		'type' => 'hash',
		'defines' => array(
			'__mysql__' => array(
				'primary_key' => array('field' => 'cid', 'autoincrement' => TRUE),
				),
			'uid' => 'scalar',
			'create_time' => 'scalar',
			),
		'storage' => 'mysql',
	),
	'star_list' => array(
		'desc' => '明星出生时间登记表',
		'type' => 'hash',
		'defines' => array(
			'__mysql__' => array(
				'primary_key' => array('field' => 'sid', 'autoincrement' => TRUE),
				),
			'create_time' => 'scalar',
			),
		'storage' => 'mysql',
	),
);

//模块功能相关配置
$config['module'] = array(
    'company_init_stars' => 1,	//初始公司可招人数限制
	'company_changename_price' => 1,	//一个宝石改一次名字
);

//杂类配置
$config['misc'] = array(
	'levels' => array( //公司升级配置
		1 => 0,
		2 => 10,	//等级=>积分
		3 => 20,
		4 => 30,
		5 => 40,
		6 => 50,
		7 => 60,
	),
	'powerlimits' => array( //公司老板体力上限
		1 => 100, //公司等级=>上限
		2 => 200,
		3 => 300,
		4 => 400,
		5 => 500,
		6 => 600,
	),
);

//验证规则
$config['dtds'] = array(
	'@changename' => array(
		'companyname' => array(
			'value' => '@post',
			'rule' => array(
				"required\t公司名字必须填写",
				"maxlength:7\t公司名称最大长度不允许超过7个字符",
			),
		),
	),
);

//访问控制
$configs['acl'] = array(
);

return $config;
