<?php
/**
 * @author cmworld@gmail.com
 * @file   模块配置文件, 格式基本可以自由书写
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => '系统消息模块，公告，通知 ，动态',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'notify' => array(
        'desc' => '系统信息',
        'type' => 'hash',
        'defines' => array(
            'last_sys' => 'string',
			'last_alert' => 'string',
            'last_feed' => 'string',
            ),
        'storage' => 'mem',
	),
    'notify_sys' => array(
        'desc' => '系统公告',
        'type' => 'array', //array(array('uid'=>用户id,'msg'=>内容,'time'=>发布时间))
        'storage' => 'mem',
    ),
    'notify_alert' => array(
        'desc' => '通知信息',
        'type' => 'array', //array(array('uid'=>用户id,'msg'=>内容,'time'=>发布时间,'hash'=>信息标示))
        'storage' => 'mem',
    ),
    'notify_feed' => array(
        'desc' => '动态信息',
        'type' => 'array', //array(array('uid'=>用户id,'msg'=>内容,'time'=>发布时间,'hash'=>信息标示,'checked'=>是否已查看))
        'storage' => 'mem',
    ),
);

//模块功能相关配置
$config['module'] = array(
	'sys' => array(
		'title'	  		=> '系统公告',
		'pageSize' 		=> 5,     		//显示条数
		'storeKeep'	  	=> 10,			//保留条数
	),
	'alert' => array(
		'title'	  		=> '用户通知',
		'pageSize' 		=> 5,     		//显示条数
		'storeKeep'	  	=> 15,			//保留条数
	),
	'feed' => array(
		'title'	  		=> '动态信息',
		'pageSize' 		=> 5,     		//显示条数
		'storeKeep'	  	=> 30,			//保留条数
	),
);

//杂类配置
$config['misc'] = array(
	'notity_template' => array(
		'company_upgrade' 		=> '经过旗下艺人的努力，%s 的公司升到 %u 级。',
		'star_upgrade' 			=> '经过 %s 的辛勤栽培，旗下艺人 %s 升到 %u 级。',		
		'jobcenter_upgrade' 	=> '',
		'star_work_alert'    => '旗下艺人 %s 完成 %s 工作，快去<a href="%s">验收工作</a>吧。'
	),
);

//验证规则
$config['dtds'] = array(
);

//访问控制
$configs['acl'] = array(
);

return $config;
