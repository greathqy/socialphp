<?php
/**
 * @author greathqy@gmail.com
 * @file   新手引导模块配置文件
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '该模块实现玩家的新手引导流程',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'new_user_tutorial' => array(
		'type' => 'scalar',
		'storage' => 'mem',
	),
);

//模块功能相关配置
$config['module'] = array(
	'max_steps' => 24,	//总共步骤
);

//杂类配置
$config['misc'] = array(
	'prizes' => array(
		1 => array('gb' => 1000), //公司成立
		3 => array('gb' => 1000), //刊登招聘广告成功
		5 => array('gb' => 100, 'props' => array(130000 => 10)), //雇佣艺人成功
		6 => array('gb' => 100),  //修改艺人名字成功
		11 => array('gb' => 1000),  //培训成功
		14 => array('gb' => 1000),	//购买服饰成功
		16 => array('gb' => 1000),	//装备服饰成功
		20 => array('gb' => 1000),	//接下工作
		23 => array('gb' => 1000),  //使用加速道具
		24 => array('gb' => 200),
	),
	'send_time_reduce_prop' => 110000,	//赠送一个缩短时间道具
);

//验证规则
$config['dtds'] = array(
	'@step1' => array(
        'companyname' => array(
            'value' => '@post', 
            'rule' => array(
                "required\t公司名必须填写",
                "maxlength:7\t公司名称最大长度不允许超过7个字符",
                ),
        ),
    ),
	'@step6' => array(
		'name' => array(
			'value' => '@post',
			'rule' => array(
				"required\t艺人名不得为空",
				"maxlength:7\t艺人名最大长度不允许超过7个字符",
			),
		),
	),
);

//访问控制
$configs['acl'] = array(
);

return $config;
