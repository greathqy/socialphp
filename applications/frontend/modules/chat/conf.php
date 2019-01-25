<?php
/**
 * @file   咖啡屋配置文件
 * @author cmworld@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '咖啡屋模块',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
 	'user_chat_lasttime' => array(
        'desc' => '用户上次发言时间 ',
        'type' => 'scalar',
        'storage' => 'mem',
    ),
    'chatroom' => array(
        'desc' => '所有用户的发言',
        'type' => 'array', // array('uid','nickname','msg','timestamp')
        'storage' => 'mem',
    ),
);

//模块功能相关配置
$config['module'] = array(
);

//杂类配置
$config['misc'] = array(
	'time_limit' => 10,		//发言间隔时间
	'message_limit' => 30,		//发言内容字符长度
	'page_size' => 10,	//每页显示10条消息
	'display_pages' => 20,	//显示几个分页
);

//验证规则
$config['dtds'] = array(
	'@index' => array(
		'msg' => array(
			'value' => '@post',
			'rule' => array(
				"required\t发言内容必须填写",
				//"maxlength:30\t发言内容最大长度不允许超过30个字符",
			),
		),
	),
);

//访问控制
$configs['acl'] = array(
);

return $config;
