<?php
/**
 * @file   xx模块配置文件
 * @author xx
 */
//模块状态配置
$config['config'] = array(
    'enabled' => TRUE,
	'desc' => 'demo模块，作为新模块开发的基础',
    'lifetime_start' => -1,
    'function_end' => -1,
    'lifetime_end' => -1,
);

//数据结构定义配置
$config['schemas'] = array(
	'demo' => array(
		'type' => 'scalar',
		'storage' => 'mem',
	),
);

//模块功能相关配置
$config['module'] = array(
    'demo' => 1,
);

//杂类配置
$config['misc'] = array(
);

//验证规则
$config['dtds'] = array(
    '@demo' => array(
        'companyname' => array(
            'value' => '@post', 
            'rule' => array(
                "required\t公司名必须填写",
                "maxlength:7\t公司名称最大长度不允许超过7个字符",
                ),
        ),
    ),
);

//访问控制
$configs['acl'] = array(
);

return $config;
