<?php
/**
 * @author greathqy@gmail.com
 * @file   模块配置文件, 格式基本可以自由书写
 */
//模块状态配置
$config['status'] = array(
    'lifetime_start' => '',
    'function_end' => '',
    'lifetime_end' => '',
    );

//数据结构定义配置
$config['schemas'] = array(
    'userinfo' => array(
        'desc' => 'user base info',
        'type' => 'hash',
        'haveref' => FALSE, //是否有引用字段
        'defines' => array(
            'gb' => 'scalar',
            'mb' => 'scalar',
            'star1' => '&frontend:index:user_star|>star1', // |后部分附加到cluster定义的prefix后面
            'star2' => '&frontend:index:user_star|>star2',
			//'star3' => '&frontend:index:user_star|>star3',
			//'extradata' => '&frontend:index:userextra|>star1',
            ),
        'storage' => 'mem:userinfo',
    ),
	'userextra' => array(
		'desc' => 'user extra data',
		'type' => 'hash',
		'defines' => array(
			'__mysql__' => array(
				'primary_key' => array('field' => 'id', 'auto_increment' => TRUE),
				'condition_key' => 'uid',
				'multirows' => FALSE,
				),
			'money' => 'scalar',
			'cash' => 'scalar',
			),
		'storage' => 'mysql',
	),
    'user_gb' => array(
        'desc' => 'user gb',
        'type' => 'scalar',
        'storage' => 'mem:user_gb',
    ),
    'user_exp' => array(
        'desc' => 'user exp',
        'type' => 'scalar',
        'storage' => 'mem:user_exp',
    ),
    'user_star' => array(
        'desc' => 'user star',
        'type' => 'hash',
        'haveref' => TRUE,
        'defines' => array(
            'age' => 'scalar',
            'birth' => 'string',
            'sex' => 'scalar',
            'points' => 'scalar',
            'salary' => 'float',
            'force' => 'scalar',
            'exp' => '&frontend:index:starexp|exp',
            ),
        'storage' => 'mem:userstar',
    ),
    'starexp' => array(
        'desc' => 'star exp',
        'type' => 'hash',
        'haveref' => TRUE,
        'defines' => array(
            'fuck' => 'scalar',
            'expchild' => '&frontend:index:starexpchild|expchild',
        ),
        'storage' => 'mem:starexp',
    ),
    'starexpchild' => array(
        'desc' => 'star exp child',
        'type' => 'scalar',
        'storage' => 'mem:starexpchild',
    ),
	'dbref' => array(
		'desc' => 'test db hash ref mem cache',
		'type' => 'scalar',
		'storage' => 'mem:dbref',
	),
);

//模块功能相关配置
$config['module'] = array(
    'max_companys' => 4,
    );

//杂类配置
$config['misc'] = array(
    //新手引导
    'tutorials' => array(
        1 => array(
            'required' => array(),
            'success' => array(),
            'next' => 2,
            ),
        2 => array(
            ),
        ),
    );

//验证规则类
$config['dtds'] = array(
    'index' => array(
        'field' => 'required|integer',
        ),
    );

//访问控制
$configs['acl'] = array(
    );

return $config;
