<?php
/**
 * @file   配置类, 可以取项目，应用，模块的配置文件
 *          当使用get获取时，把相应应用, 模块的配置文件加载进来
 *          将项目的配置文件按照环境默认加载进来
 * @author greathqy@gmail.com
 */
class Configurator
{
    static public $app;
    static public $module;
    static public $projDir;

    //保存原先的app和module名
    static public $stack = array();

    static public $env = 'DEV';

    //各类配置信息
    static public $projConf = array();
    static public $moduleConf = array();
    static public $appConf = array();
    static public $generalConf = array();

	//缓存
	static public $cache = array();

    /**
     * 获取配置
     *
     * @param String $key 逗号分割的配置key
     * @param Mixed  $default 默认值
     * @return Mixed
     */
    static public function get($key, $default = NULL) {
		if (isset(self::$cache[$key])) {
			return self::$cache[$key];
		}

        $keys = explode('.', $key);
        $domains = array(
            'proj', 'module', 'app', 'common',
            );
        if (!in_array($keys[0], $domains)) {
            exit('invalid config domain');
        }
        $domain = array_shift($keys);
        $config =& self::$projConf;
        if ('module' == $domain) {
            $config =& self::$moduleConf;
        } else if ('app' == $domain) {
            $config =& self::$appConf;
        } else if ('common' == $domain) {
            $config =& self::$generalConf;
        }
		if ($keys) {	//Load specific config file
			$scope = $keys[0]; 
			if ($domain == 'module') {
				if (!isset(self::$moduleConf[$scope])) {
					$arr = explode('_', $scope);
					if (sizeof($arr) != 2) {
						throw new Exception("模块名错误, 模块名格式为 appName_moduleName");
					}
					self::setApp($arr[0]);
					self::loadModuleConfig($arr[1]);
					self::restoreConf();
				}
			} else if ($domain == 'app') {
				if (!isset(self::$appConf[$scope])) {
					self::loadAppConfig($scope);
				}
			}
		}

        foreach ($keys as $index) {
            if (empty($config) || !isset($config[$index])) {
			    throw new Exception('The config item corresponding to key: ' . $key . ' is not exists!');
            }
            $config =& $config[$index];
        }

		self::$cache[$key] = $config;

        return $config;
    }

    /**
     * 设置当前要处理的app
     * 
     * @param String $app 应用名
     */
    static public function setApp($app) {
        if (self::$app != $app) {
            self::$stack['app'] = self::$app;
        }
        self::$app = $app;
        return TRUE;
    }

    /**
     * 设置当前要处理的module
     * 
     * @param String $app 模块名
     */
    static public function setModule($module) {
        if (self::$module != $module) {
            self::$stack['module'] = self::$module;
        }
        self::$module = $module;
        return TRUE;
    }

    /**
     * 恢复上一次的app和module设置
     */
    static public function restoreConf() {
        if (isset(self::$stack['app']) || isset(self::$stack['module'])) {
            if (isset(self::$stack['app'])) {
                self::$app = self::$stack['app'];
                unset(self::$stack['app']);
            }
            if (isset(self::$stack['module'])) {
                self::$module = self::$stack['module'];
                unset(self::$stack['module']);
            }
        }
        return TRUE;
    }

    //执行各初始化操作
    static public function init($options) {
        self::$env = $options['env'];
        $projDir = $options['proj_dir'];
        $appName = $options['app_name'];
        $module = $options['module'];
        $action = $options['action'];

        self::$app = $appName;
        self::$module = $module;
        self::$projDir = $projDir;

        self::loadProjectConfig();
    }

    /**
     * 加载项目配置文件
     */
    static public function loadProjectConfig() {
        if (empty(self::$projConf)) {
            $configFile = 'config_' . strtolower(self::$env) . '.php';
            $filePath = self::$projDir . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $configFile;
            if (file_exists($filePath)) {
                self::$projConf = include($filePath);
                if (!is_array(self::$projConf)) {
                    throw new Exception("load project config failed or config format invalid!");
                }
            }
        }

        return TRUE;
    }

    /**
     * 加载应用配置文件
     *
     * @param String $appName 应用名
     * @param String $confFile 配置文件路径
     * @return Boolean 
     */
    static public function loadAppConfig($app = NULL) {
        $app = $app ? $app : self::$app;
        if (!isset(self::$appConf[$app])) {
            $filePath = self::$projDir . DIRECTORY_SEPARATOR 
                . 'applications' . DIRECTORY_SEPARATOR 
                . $app . DIRECTORY_SEPARATOR . 'conf.php';
            $config =& self::loadConfig($filePath);

            self::$appConf[$app] = $config;
        }

        return TRUE;
    }

	/**
	 * 加载模块配置文件
	 *
	 * @param String	$module	模块名
	 * @return Boolean
	 */
    static public function loadModuleConfig($module = NULL) {
        $app = (string) self::$app;
        $module = $module ? $module : self::$module;
        $key = $app . '_' . $module;
        if (!isset(self::$moduleConf[$key])) {
			if (Module::exists($module)) {
				$filePath = self::$projDir . DIRECTORY_SEPARATOR 
					. 'applications' . DIRECTORY_SEPARATOR 
					. $app . DIRECTORY_SEPARATOR . 'modules' 
					. DIRECTORY_SEPARATOR . $module
					. DIRECTORY_SEPARATOR . 'conf.php';
				$config =& self::loadConfig($filePath);

				self::$moduleConf[$key] = $config;
			} else {
				return FALSE;
			}
        }

        return TRUE;
    }

    /**
     * 底层load函数，进行实际加载配置文件
     *
     * @param String $confFilePath 配置文件绝对路径
     * @return Array
     */
    static public function & loadConfig($confFilePath) {
        $config = include($confFilePath);
        if ($config === FALSE || !is_array($config)) {
            throw new Exception("load config file failed or invalid config format");
        }

        return $config;
    }

    /**
     * 加载有名配置文件
     *
     * @param String $confName  配置名
     * @param String $confFilePath 配置文件路径
     */
    static public function loadNamedConfig($confName, $confFilePath) {
        if (!isset(self::$generalConf[$confName])) {
            $config =& self::loadConfig($confFilePath);
            self::$generalConf[$confName] =& $config;
        }
        return TRUE;
    }
}
