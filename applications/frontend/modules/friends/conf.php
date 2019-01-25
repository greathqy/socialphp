<?php
/**
 * @file   咖啡屋配置文件
 * @author cmworld@gmail.com
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '好友模块',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
 	'user_friends' => array(
        'desc' => '用户好友信息',
        'type' => 'hash',
        'defines' => array(
			'requesting' => 'array', //好友请求 array( fid => time() )
			'friends' => 'array', //好友列表  array( fid => array( 'refresh' => time()))
            ),
        'storage' => 'mem',
    ),


);

//模块功能相关配置
$config['module'] = array(
	'friend_refresh_time' => 7200,
);

//杂类配置
$config['misc'] = array(
	'friend_page_size' => 10,	//每页显示10条消息
);

//验证规则
$config['dtds'] = array(
);

//访问控制
$configs['acl'] = array(
);

return $config;
