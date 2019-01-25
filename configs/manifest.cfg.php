<?php
//类名文件路径映射关系
$config['class_file_mapping'] = array(
    'factory' => PROJ_LIB . DS . 'core' . DS . 'Factory.php',
    'configurator' => PROJ_LIB . DS . 'core' . DS . 'Configurator.php',
    'controller' => PROJ_LIB . DS . 'core' . DS . 'Controller.php',
    'dispatcher' => PROJ_LIB . DS . 'core' . DS . 'Dispatcher.php',
    'meditator' => PROJ_LIB . DS . 'core' . DS . 'Meditator.php',
    'router' => PROJ_LIB . DS . 'core' . DS . 'Router.php',
    'httprouter' => PROJ_LIB . DS . 'core' . DS . 'Router.php',
    'registry' => PROJ_LIB . DS . 'core' . DS . 'Registry.php',
    'context' => PROJ_LIB . DS . 'core' . DS . 'Context.php',
    'event' => PROJ_LIB . DS . 'core' . DS . 'Event.php',
    'module' => PROJ_LIB . DS . 'core' . DS . 'Module.php',
    'logic' => PROJ_LIB . DS . 'core' . DS . 'Module.php',
    'abstractrender' => PROJ_LIB . DS . 'render' . DS . 'AbstractRender.php',
    'jsonrender' => PROJ_LIB . DS . 'render' . DS . 'JsonRender.php',
    'wmlrender' => PROJ_LIB . DS . 'render' . DS . 'WmlRender.php',
    'htmlrender' => PROJ_LIB . DS . 'render' . DS . 'HtmlRender.php',
    'mao' => PROJ_LIB . DS . 'persistent' . DS . 'MAO.php',
    's7' => PROJ_LIB . DS . 'persistent' . DS . 'Memstore.php',
    'memstore' => PROJ_LIB . DS . 'persistent' . DS . 'Memstore.php',
    'mysqlstore' => PROJ_LIB . DS . 'persistent' . DS . 'MysqlStore.php',
    'mysqlbuilder' => PROJ_LIB . DS . 'persistent' . DS . 'MysqlBuilder.php',
    'sharding' => PROJ_LIB . DS . 'misc' . DS . 'Sharding.php',
    'validator' => PROJ_LIB . DS . 'misc' . DS . 'Validator.php',
    'debug' => PROJ_LIB . DS . 'misc' . DS . 'Debug.php',
    'pagination' => PROJ_LIB . DS . 'misc' . DS . 'Pagination.php',
    'util' => SYS_ROOT . DS . 'applications' . DS . 'frontend' . DS . 'lib' . DS . 'Util.php',

	//Exceptions
	'nomoneyexception' => PROJ_LIB . DS . 'core' . DS . 'Exception.php',
	'nospaceexception' => PROJ_LIB . DS . 'core' . DS . 'Exception.php',
	'notmeetexception' => PROJ_LIB . DS . 'core' . DS . 'Exception.php',
	'invalidseqexception' => PROJ_LIB . DS . 'core' . DS . 'Exception.php',
	'notfoundexception' => PROJ_LIB . DS . 'core' . DS . 'Exception.php',
);

//Schema和模块的映射关系
$config['schema_module_mapping'] = array(
	//Testing purpose
	'userextra' => array('frontend', 'test'),

	//Production 
	'new_user_status' => array('frontend', 'index'),
	'userinfo' => array('frontend', 'index'),
	'user_props' => array('frontend', 'prop'),
	'user_sprops' => array('frontend', 'prop'),
	'user_equips' => array('frontend', 'prop'),
	'user_gb' => array('frontend', 'index'),
	'uid_oid_mapping' => array('frontend', 'index'),
	'oid_uid_mapping' => array('frontend', 'index'),
	'uid_oid_dbmapping' => array('frontend', 'index'),
	'company_list' => array('frontend', 'index'),
	'star_list' => array('frontend', 'index'),
	'user_company' => array('frontend', 'index'),
	'company_fame' => array('frontend', 'index'),
	'company_cash' => array('frontend', 'index'),
	'company_achieve' => array('frontend', 'index'),
	'star_achieve' => array('frontend', 'index'),

	'star' => array('frontend', 'index'),
	'star_attrs' => array('frontend', 'index'),
	'user_durable_stat' => array('frontend', 'index'),

	//'job' => array('frontend', 'index'),
	'equipment' => array('frontend', 'index'),
	'recruit' => array('frontend', 'recruit'),
	'job_center' => array('frontend', 'job'),
	'jobing_stats' => array('frontend', 'job'),
	'job_steal_stats' => array('frontend', 'job'),
	'same_level_pk' => array('frontend', 'job'),
	'star_pkinfo' => array('frontend', 'star'),
	'star_durable_stat' => array('frontend', 'star'),
	'user_power' => array('frontend', 'index'),
	'user_clothesstore_floor' => array('frontend', 'prop'),
	'user_clothesstore_level' => array('frontend', 'prop'),
	'training' => array('frontend', 'training'),
	'training_unlock' => array('frontend', 'training'),
	'training_free_unlockchance' => array('frontend', 'training'),
	'new_user_tutorial' => array('frontend', 'tutorial'),
	
	//咖啡厅
	'chatroom' => array('frontend', 'chat'),
	'user_chat_lasttime' => array('frontend', 'chat'),
	//系统消息
	'notify'  => array('frontend', 'notify'),
	'notify_sys' => array('frontend', 'notify'),
	'notify_alert' => array('frontend', 'notify'),
	'notify_feed' => array('frontend', 'notify'),
	//好友
	'user_friends' => array('frontend', 'friends'),
);

//哪些模块注册了哪些事件的配置
/**
 * =======================在此写下所有事件附带的参数内容====================
 * 系统初始化			init					array()
 * 明星完成工作			star_complete_work		array('uid'=>, 'star_id'=>, 'company_id'=>, 'star_exp'=>, 'company_exp'=>, 'company_gb'=>, )
 * 工作挑战pk结果		pkresult				array()
 * 明星恢复信息			star_restore_confidence	array('star_id'=>xx,)
 * 老板恢复体力			boss_restore_power		array('uid'=>xx,)
 * 培训所升级			training_upgrade		array('uid'=>xx,)
 * 公司升级				company_upgrade			array('uid'=>, 'company_name'=>, 'company_level'=>)
 * 明星升级				star_upgrade			array('uid'=>, )
 */
$config['events'] = array(
    'frontend' => array(
        'index' => array(
            'star_complete_work', 'boss_restore_power',
        ),
		'job' => array(
			'pkresult',
		),
		'star' => array(
			'star_complete_work', 'star_restore_confidence',
		),
		'training' => array(
			'training_upgrade',
		),
		'notify' => array(
			'company_upgrade', 'star_upgrade', 'training_upgrade','star_complete_work'
		)
    ),
);

//常量定义
class Error
{
    const ERROR_NO_LOGIN = 10000;
    const ERROR_COMMON_ERROR = 10001;
    const ERROR_DB_FAILURE = 10002;
    const ERROR_CACHE_FAILURE = 10003;
    const ERROR_PARAM_INVALID = 10004;
	const ERROR_INVALID_OP = 10005;
	const ERROR_404_NOT_FOUND = 10006;
	const ERROR_PERSISTENT_ERROR = 10007;

    static public $errMessages = array(
        self::ERROR_NO_LOGIN => '你尚未登录, 请先登录.',
        self::ERROR_COMMON_ERROR => '普通错误',
        self::ERROR_DB_FAILURE => '数据类错误',
        self::ERROR_CACHE_FAILURE => '缓存类错误',
        self::ERROR_PARAM_INVALID => '参数类错误',
		self::ERROR_INVALID_OP => '非法的操作',
		self::ERROR_404_NOT_FOUND => '你请求的资源不存在',
		self::ERROR_PERSISTENT_ERROR => '存储层出错',
        );
}

return $config;
