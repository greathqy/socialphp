<?php
/**
 * @author greathqy@gmail.com
 * @file  开发环境配置文件
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
        //MYSQL数据库配置
        'mysql_physical' => array(
			'__user' => 'tomorrow',
			'__pass' => 'tomorrow.pass1',
			//'__user' => 'root',
			//'__pass' => '',
            'db001' => array('host' => '192.168.1.249', 'port' => 3306), //user=>xx, pass=>xx
            //'db001' => array('host' => '127.0.0.1', 'port' => 3306), //user=>xx, pass=>xx
        ),
        'mysql_storage_cluster' => array(
            'uid_oid_dbmapping' => array(
                'hosts' => array('db001'),
                'rule' => array(),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'tomorrow_uid_oid_dbmapping_',
                //'prefix' => 'uid_oid_dbmapping_',
                'tablename' => 'uid_oid_dbmapping',
            ),
			'company_list' => array(
                'hosts' => array('db001'),
                'rule' => array(),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'tomorrow_company_list_',
                //'prefix' => 'company_list_',
                'tablename' => 'company_list',
			),
			'star_list' => array(
                'hosts' => array('db001'),
                'rule' => array(),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'tomorrow_star_list_',
                //'prefix' => 'star_list_',
                'tablename' => 'star_list',
			),
        ),
        //内存存储 membase/memcache/tt配置
        'mem_physical' => array(
            'local001' => array('host' => '192.168.1.249', 'port' => 49001),
            //'local001' => array('host' => '127.0.0.1', 'port' => 11211),
        ),
        'mem_storage_cluster' => array(
            '__compatible' => array('pack_type' => FALSE, 'mc_compress' => FALSE, 'mc_expire' => 0, 'check_field' => FALSE, 'fields' => array(), 'is_unserialize' => FALSE, 'db_type' => '', 'db_server' => array()),
			'new_user_status' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'newuserstatus_',
			),
            'userinfo' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userinfo_',
            ),
            'user_props' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userprops_',
            ),
            'user_sprops' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'usersprops_',
            ),
            'user_equips' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userequips_',
            ),
            'user_gb' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'usergb_',
            ),
            'uid_oid_mapping' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'uidoidmapping_',
            ),
            'oid_uid_mapping' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'oiduidmapping_',
            ),
            'user_company' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'usercompany_',
            ),
            'company_fame' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'companyfame_',
            ),
            'company_cash' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'companycash_',
            ),
            'company_achieve' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'companyachieve_',
            ),
            'star_achieve' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'starachieve_',
            ),
            'chatroom' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'chatroom_',
            ),
            'star' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'star_',
            ),
            'star_attrs' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'starattrs_',
            ),
            'user_durable_stat' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'userdurablestat_',
            ),
            'sys_notice' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'sysnotice_',
            ),
            'user_notify' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'usernotify_',
            ),
            'recruit' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'recruit_',
            ),
            'training' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'training_',
            ),
			'training_unlock' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'trainingunlock_',
			),
			'training_free_unlockchance' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'trainingfreeunlockchance_',
			),
            'job' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'job_',
            ),
            'equipment' => array(
                'hosts' => array('local001'),
                'sharding' => array('Sharding', 'byOne'),
                'prefix' => 'equipment_',
            ),
			'same_level_pk' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'samelevelpk_',
			),
			'job_center' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'jobcenter_',
			),
			'jobing_stats' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'jobingstats_',
			),
			'star_pkinfo' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'starpkinfo_',
			),
			'star_durable_stat' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'stardurablestat_',
			),
			'user_power' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'userpower_',
			),
			'user_clothesstore_floor' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'userclothesstorefloor_',
			),
			'user_clothesstore_level' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'userclothesstorelevel_',
			),
			'job_steal_stats' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'jobstealstats_',
			),
			'new_user_tutorial' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'newusertutorial_',
			),
			'chatroom' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'chatroom_',
			),
			'user_chat_lasttime' => array(
				'hosts' => array('local001'),
				'sharding' => array('Sharding', 'byOne'),
				'prefix' => 'userchatlasttime_',
			),
        ),
        //日志服务器配置
        'logger_physical' => array(
        ),
        'logger_storage_cluster' => array(
        ),
    ),
);

defined("LOG_LEVEL") || define("LOG_LEVEL", 'LOG_ALL');
error_reporting(E_ALL);

return $config;
