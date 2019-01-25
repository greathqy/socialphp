<?php
/**
 * @file 线上环境配置文件
 * memcache 集群配置
 * 各类配置信息等
 */
$config = array(
    //平台相关
    'platform' => array(
        'name' => 'QQ无线',
        'app_id' => 600,
        'secret_key' => '',
    ),
    //版本号相关
    'version' => array(
    ),
    //持久化存储配置
    'persistent' => array(
        //数据库配置
        'db_physical' => array(
            'db001' => array('host' => '127.0.0.1', 'port' => 3306),
        ),
        'db_storage_cluster' => array(
        ),
        //内存存储 membase/memcache/tt配置
        'mem_physical' => array(
            'local001' => array('host' => '127.0.0.1', 'port' => 11211),
        ),
        'mem_storage_cluster' => array(
            '__compatible' => array('pack_type' => FALSE, 'mc_compress' => FALSE, 'mc_expire' => 0, 'check_field' => FALSE, 'fields' => array(), 'is_unserialize' => FALSE, 'db_type' => '', 'db_server' => array()),
            'userinfo' => array(
                'hosts' => array('local001'),
                'rule' => array(), // 1 => 'value:local001', // value:_valueString or key:_index)
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userinfo_',
            ),
            'user_gb' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'usergb_',
            ),
            'user_exp' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userexp_',
            ),
            'user_star' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userstar_',
            ),
        ),
        //日志服务器配置
        'logger_physical' => array(
        ),
        'logger_storage_cluster' => array(
        ),
    ),
);

return $config;
