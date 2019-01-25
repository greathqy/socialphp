<?php
/**
 * @author greathqy@gmail.com
 * @file   抽象层简化内存数据存储读取操作
 */
//存储层接口类
//Storage == s7
class s7
{
    static public $schemas = array();
    static private $storage = NULL;

	static public $validateCache = array();

    /**
     * 获得到持久化对象的链接
     */
    static protected function getStorage() {
        if (!self::$storage) {
            self::$storage = Factory::create('MAO');
        }

        return self::$storage;
    }

	/**
	 * 验证传入的schemaName是否合法。最后一个模式名必须是引用别的结构
	 * 不支持直接取数组里的某个key对应的值
	 *
	 * @param String	$schemaName	模式名, 可能用.分割引用子模式
	 * @return 	Array	array('valid'=>true/false, 'last_is_ref' > true/false, 'extrakey' => 'xx')
	 */
	static public function validateSchema($schemaName) {
		if (isset(self::$validateCache[$schemaName])) {
			return self::$validateCache[$schemaName];
		}
		$arrSchemaName = explode('.', $schemaName);
		$first = array_shift($arrSchemaName);
		$refSchema = '';
		$schemaInfo = self::getSchema($first);	//fail immediately at dev stage when schema name not found
		if (!$arrSchemaName) { //one level
			$schema = $schemaInfo['struct'];
			$lastIsStruct = FALSE;
			if ($schema['type'] == 'hash' || $schema['type'] == 'array') {
				$lastIsStruct = TRUE;
			}
			$ret = array(
				'valid' => TRUE,
				'base_schema' => $first,
				'ref_schema' => $refSchema,
				'last_is_struct' => $lastIsStruct,
				'extrakey' => array(),
				);
		} else { //multiple levels
			$extraKey = array();;
			$lastIsStruct = TRUE;
			$valid = TRUE;
			while (TRUE && $field = array_shift($arrSchemaName)) { //userinfo.company.fame => array('company', 'fame')
				$extraKey[] = $field;
				if (isset($schemaInfo['struct']['defines'][$field]) && $schemaInfo['struct']['defines'][$field][0] == '&') {
					$subSchema = substr($schemaInfo['struct']['defines'][$field], 1);
					$refSchema = $subSchema;
					$schemaInfo = self::getSchema($subSchema);
					if ($schemaInfo['struct']['type'] != 'hash' && $schemaInfo['struct']['type'] != 'array') {
						$lastIsStruct = FALSE;
					}
				} else {
					$lastIsStruct = FALSE;
					$valid = FALSE;
				}
			}

			$ret = array(
				'valid' => $valid,
				'base_schema' => $first,
				'ref_schema' => $refSchema,
				'last_is_struct' => $lastIsStruct,
				'extrakey' => $extraKey,
				);
		}

		if (!$ret['valid']) {
			if (ENV == 'DEV' || ENV == 'TEST') {
				$msg = "schema名: $schemaName 不合法";
			} else {
				$msg = "非法操作, 请不要手工修改uri请求游戏!";
			}
			throw new Exception($msg);
		}

		$ret['descendant_schema'] = $ret['extrakey'];
		if ($ret['extrakey']) {
			$ret['extrakey'] = join('#', $ret['extrakey']);
		}
		if ($ret['descendant_schema']) {
			$ret['descendant_schema'] = join('.', $ret['descendant_schema']);
		}
		if (!$ret['last_is_struct']) {
			$ret['ref_schema'] = '';
		}
		if ($ret['last_is_struct'] && !$ret['ref_schema']) {
			$ret['ref_schema'] = $ret['base_schema'];
		}

		self::$validateCache[$schemaName] = $ret;

		return $ret;
	}

    /**
     * 返回schema对应的模式定义信息
     *
     * @param String $schemaName 模式定义名
     * @return Array
     */
    static public function & getSchema($schemaName) {
		$conf = Configurator::$generalConf['manifest']['schema_module_mapping'];
		if (isset($conf[$schemaName])) {
			$app = $conf[$schemaName][0];
			$module = $conf[$schemaName][1];
			$schema = $schemaName;
		} else {
			throw new Exception("schema [$schemaName] not found or not registered in manifest config");
		}

        $skey = "{$app}_{$module}_{$schema}";
        if (!isset(self::$schemas[$skey])) {
            Module::setApp($app);
            Module::load($module, array('config'));

            //获得模块配置信息
            $key = "module.{$app}_{$module}.schemas.{$schema}";
            $conf = Configurator::get($key);

            $schemaInfo = array(
                'struct' => $conf,    //schema描述数组
                'app' => $app,
                'module' => $module,
                'schema' => $schema,
                'storage_type' => $conf['storage'],
                'cluster_name' => $schemaName,
                );

            self::$schemas[$skey] = $schemaInfo;
        }

        return self::$schemas[$skey];
    }

    /**
     * 获得配置信息
     *
     * @param String $schemaName 模式名
     * @param Mixed  $shardId    分区Id, 
     *                           此ID一定是实际用来进行分区的id
     *                           最终为 clusterConfig['prefix'] .= '_' . $shardId
     * @param String $type   mem/db/redis
     * @return Array 返回MAO格式的配置信息
     */
    static public function get_config($schemaName, $shardId) {
        $schemaInfo = self::getSchema($schemaName);
        $clusterName = $schemaInfo['cluster_name'];
        $type = $schemaInfo['storage_type'];

        $key = "proj.persistent.{$type}_physical";
        $physicalInfo = Configurator::get($key);
        $key = "proj.persistent.{$type}_storage_cluster.{$clusterName}";
        $clusterInfo = Configurator::get($key);

        if ($type == 'mem') { 
			//$validateInfo = self::validateSchema($schemaName);

            $key = "proj.persistent.{$type}_storage_cluster.__compatible";
            $compatible = Configurator::get($key);
            if (isset($clusterInfo['__compatible'])) {
                $compatibleOverride = $clusterInfo['__compatible'];
            } else {
                $compatibleOverride = array();
            }
            if ($compatibleOverride) {
                $compatible = array_merge($compatible, $compatibleOverride);
            }
            $config = $compatible;

            //生成memcache key
            $config['mc_key'] = $clusterInfo['prefix'] . str_replace('#', '_', $shardId);
            //sharding函数自己决定哪些因数参与shard计算，也就是#之后的参数要不要参与可以由shard函数自己决定
            //这样分区时不一定按照传入信息中的用户id来分区，也可以用其他因素
            $farmId = call_user_func($clusterInfo['sharding'], $shardId);
            if (isset($clusterInfo['rule'][$farmId])) {
                $info = $clusterInfo['rule'][$farmId];
                $info = explode(':', $info);
                if ($info[0] == 'key') {
                    $physicalId = $info[1];
                    $physicalId = $clusterInfo['hosts'][$physicalId];
                } else if ($info[0] == 'value') {
                    $physicalId = $info[1];
                }
            } else {
                --$farmId;
                $physicalId = $clusterInfo['hosts'][$farmId];
            }

            $config['mc_server'] = $physicalInfo[$physicalId];
			//指示mao是否需要pack等
			//if ($validateInfo['last_is_struct']) {
				$config['pack_type'] = 'array';
			//}
		} else if ($type == 'mysql') {
			$config = array();
            $farmId = call_user_func($clusterInfo['sharding'], $shardId);
            if (isset($clusterInfo['rule'][$farmId])) {
                $info = $clusterInfo['rule'][$farmId];
                $info = explode(':', $info);
                if ($info[0] == 'key') {
                    $physicalId = $info[1];
                    $physicalId = $clusterInfo['hosts'][$physicalId];
                } else if ($info[0] == 'value') {
                    $physicalId = $info[1];
                }
            } else {
                $physicalId = $clusterInfo['hosts'][$farmId - 1];
            }
			//PhysicalInfo clusterInfo
			$dbName = $clusterInfo['prefix'] . str_pad($farmId, 3, '0', STR_PAD_LEFT);
			$tableName = $clusterInfo['tablename'];
			$host = $physicalInfo[$physicalId]['host'];
			$port = $physicalInfo[$physicalId]['port'];
			if (isset($physicalInfo[$physicalId]['user'])) {
				$user = $physicalInfo[$physicalId]['user'];
			} else {
				$user = $physicalInfo['__user'];
			}
			if (isset($physicalInfo[$physicalId]['pass'])) {
				$pass = $physicalInfo[$physicalId]['pass'];
			} else {
				$pass = $physicalInfo['__pass'];
			}

			$config['db_server'] = array(
				'host' => $host,
				'port' => $port,
				'user' => $user,
				'passwd' => $pass,
				'dbname' => $dbName,
				'tblname' => $tableName,
				);
		}

        return $config;
    }

    /**
     * 获得引用信息
     *
     * @param Array $schema 模式描述数组
     * @return Array
     */
    static public function extractRefInfo($schema) {
        $info = array();
        if (isset($schema['defines'])) {
	        foreach ($schema['defines'] as $field => $spec) {
	            if ($field != '__mysql__' && $spec[0] == '&') {
	                $schema = substr($spec, 1);
	
	                $info[$field] = array(
	                    'schema' => $schema,
	                    );
	            }
	        }
        }

        return $info;
    }
    
    /**
     * 操作是否成功
     */
    static public function succ($ret) {
        if ($ret !== FALSE)
            return TRUE;

        return FALSE;
    }

	/**
	 * 获得最初用来分区的主键，对于social来说一般是用户id
	 *
	 * @param String $shardId  分区主键 
	 * @return String
	 */
	static public function getOriginalShardId($shardId) {
		$pos = strpos($shardId, '#');
		if ($pos !== FALSE) {
			$shardId = substr($shardId, 0, $pos);
		}

		return $shardId;
	}

	/**
	 * 获得要被使用存储配置信息的schema名字
	 *
	 * @param String $schemaName	模式名
	 * @return String
	 */
	static public function getBaseSchemaName($schemaName) {
		$arr = explode('.', $schemaName);

		return $arr[0];
	}

    /**
     * 按照存储类型，路由到对应的Memstore类 // Mysqlstore类读取
     *
     * @param String $schemaName 模式名
     * @param Mixed  $shardId    分区键
     * @return Array
     */
    static public function get($schemaName, $shardId) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
        $schemaInfo = self::getSchema($realSchemaName);
        $storageType = $schemaInfo['storage_type'];
        $storageType = ucfirst($storageType);
        $class = $storageType . 'store';
        $ret = call_user_func(array($class, 'doget'), $schemaName, $shardId);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
    }

	/**
	 * 递归get，将所有引用到的子结构都load出来
	 *
	 * @param String	$schemaName	模式名
	 * @param String	$shardId	分区键
	 * @param Array	
	 */
	static public function rget($schemaName, $shardId) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
        $schemaInfo = self::getSchema($realSchemaName);
        $storageType = $schemaInfo['storage_type'];
        $storageType = ucfirst($storageType);
        $class = $storageType . 'store';
        $ret = call_user_func(array($class, 'dorget'), $schemaName, $shardId);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}
	
		return $ret;
	}

    /**
     * 按照存储类型，路由到对应的Memstore类 // Mysqlstore类写入
     *
     * @param String $schemaName 模式名
     * @param Mixed  $shardId    分区键
     * @param Mixed  $value      存储值
     * @param Array  $lazySet    指定哪些被引用的模式不需要被写入
     * @return Boolean
     */
    static public function set($schemaName, $shardId, $value, $lazySet = array()) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
        $schemaInfo =& self::getSchema($realSchemaName);
        $storageType = $schemaInfo['storage_type'];
        $storageType = ucfirst($storageType);
        $class = $storageType . 'store';
   		if (is_array($value) && isset($value['__schema__!'])) { //去掉get时附加的schema信息
            unset($value['__schema__!']);
        }
        if (is_array($value) && isset($value['__shardid__!'])) {
            unset($value['__shardid__!']);
        }
        if (is_array($value) && isset($value['__loaded__!'])) {
            unset($value['__loaded__!']);
		}

        $ret = call_user_func(array($class, 'doset'), $schemaName, $shardId, $value, $lazySet);
		if ($ret === FALSE) {
			
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}	

		return $ret;
    }

	/**
	 * 替换原先存储的内容，如果原先内容不存在 则add
	 *
	 * @param String $schemaName 模式名
     * @param Mixed  $shardId    分区键
     * @param Mixed  $value      存储值
     * @param Array  $lazySet    指定哪些被引用的模式不需要被写入
     * @return Boolean
	 */
	static public function replaceinto($schemaName, $shardId, $value, $lazySet = array()) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';
		
		if (is_array($value) && isset($value['__schema__!'])) { //去掉get时附加的schema信息
            unset($value['__schema__!']);
        }
        if (is_array($value) && isset($value['__shardid__!'])) {
            unset($value['__shardid__!']);
        }
        if (is_array($value) && isset($value['__loaded__!'])) {
            unset($value['__loaded__!']);
		}

		$ret = call_user_func(array($class, 'doreplaceinto'), $schemaName, $shardId, $value, $lazySet);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 添加
	 */
	static public function add($schemaName, $shardId, $value, $lazyAdd = array()) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';

		$ret = call_user_func(array($class, 'doadd'), $schemaName, $shardId, $value, $lazyAdd);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 自增, 标量有效only
	 */
	static public function inc($schemaName, $shardId, $value = 1) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';
		$ret = call_user_func(array($class, 'doinc'), $schemaName, $shardId, $value);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 自减, scalar olny
	 */
	static public function dec($schemaName, $shardId, $value = 1) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';
		$ret = call_user_func(array($class, 'dodec'), $schemaName, $shardId, $value);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 删除
	 */
	static public function del($schemaName, $shardId, $lazyDelete = array()) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';
		$ret = call_user_func(array($class, 'dodel'), $schemaName, $shardId, $lazyDelete);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 执行存储介质个性化操作
	 */
	static public function exec($schemaName, $shardId, $op, $data, $attrs = array()) {
		$realSchemaName = self::getBaseSchemaName($schemaName);
		$schemaInfo =& self::getSchema($realSchemaName);
		$storageType = $schemaInfo['storage_type'];
		$storageType = ucfirst($storageType);
		$class = $storageType . 'store';
		$ret = call_user_func(array($class, 'doexec'), $schemaName, $shardId, $op, $data, $attrs);
		if ($ret === FALSE) {
			throw new Exception(Error::$errMessages[Error::ERROR_PERSISTENT_ERROR], Error::ERROR_PERSISTENT_ERROR);
		}

		return $ret;
	}

	/**
	 * 加载结构体的子结构
	 *
	 * @param Array $arr	结构体
	 * @param String $key	要加载的字段, .号分隔, 最后一位必须是引用字段
	 * @return Mixed
	 */
	static public function l(&$arr, $key) { //$arr: __schema__!, __shardid__!, __loaded__!
		$schemaName = isset($arr['__schema__!']) ? $arr['__schema__!'] : '';
		$shardId = isset($arr['__shardid__!']) ? $arr['__shardid__!'] : '';
		if (!$schemaName || !$shardId) {
			if (ENV == 'DEV') {
				Debug::backtrace();
			}
			throw new Exception("s7::l 函数期望第一个参数是一个精心构造的数组!");
		}
		$childSchemaName = $schemaName . '.' . $key; //if need load we use this key

		$fields = explode('.', $key);
		$ret = NULL;
		$pointer = & $arr;
		foreach ($fields as $index) { //company | company achieve
			if (isset($pointer[$index]) && !is_array($pointer[$index])) { //Just a common value and found
				$ret = $pointer[$index];
				break;
			} else if (isset($pointer[$index]) && isset($pointer[$index]['__loaded__!'])) { //Is an array and already loaded
				$ret = $pointer[$index];
				break;
			} else {
				$ret = NULL;
				break;
			}
		}
		if ($ret) {
			return $ret;
		} else {
			$ret = self::get($childSchemaName, $shardId);
			
			//Save it to original array
			$pointer = & $arr;
			$maxIdx = sizeof($fields) - 1;
			foreach ($fields as $i => $index) {
				if ($maxIdx != $i) { //Not the last element
					if (!array_key_exists($index, $pointer)) {
						$pointer[$index] = array();
						$pointer = & $pointer[$index];
					}
				} else {
					if (isset($pointer[$index]) && $pointer[$index]) {
						$pointer[$index] = array_merge($pointer[$index], $ret);
					} else {
						$pointer[$index] = $ret;
					}
				}
			}
		}

		return $ret;
	}
}

//内存操作封装
class Memstore extends s7
{
	/**
     * 获得一列数据
     * 
     * @param String $schemaName 逗号分割的模式名 userinfo / userinfo.company
     * @param Mixed  $shardId    分区键
     * @return Array
     */
	static public function doget($schemaName, $shardId) {
		$info = self::validateSchema($schemaName);	//验证所使用的schema是否合法
        $schemaInfo =& self::getSchema($info['base_schema']);
        $storage = self::getStorage();	
      
		$orgShardId = $shardId;
		if ($info['extrakey']) {
			$shardId .= '#' . $info['extrakey'];
		}
		$schemaName = $schemaInfo['schema'];
        $ret = $storage->get_cache('makenosense', $schemaName, $shardId);
        if (!self::succ($ret)) {
			return FALSE;
        }
		if ($ret && $info['last_is_struct']) {
			if ($info['descendant_schema']) {
				$ret['__schema__!'] = $info['base_schema'] . '.' . $info['descendant_schema'];
			} else {
				$ret['__schema__!'] = $info['base_schema'];
			}
			$ret['__shardid__!'] = $orgShardId;
			$ret['__loaded__!'] = TRUE;
		}

        return $ret;
    }

	/**
	 * 递归获取结构
	 *
	 * @param String	$schemaName	模式名
	 * @param String	$shardId	分区键
	 * @return Mixed
	 */
	static public function dorget($schemaName, $shardId) {
		$info = self::validateSchema($schemaName);
		$schemaGet = $info['base_schema'];
        $storage = self::getStorage();
		if ($info['ref_schema']) {
			$schemaInfo =& self::getSchema($info['ref_schema']);
			$schema =& $schemaInfo['struct'];
		}

		$parentShardId = $shardId;
		if ($info['extrakey']) {
			$parentShardId .= '#' . $info['extrakey'];
		}

        $ret = $storage->get_cache('makenosense', $schemaGet, $parentShardId);

        //If it is a hash strucutre and have reference field
		if (self::succ($ret)) {
			if (!is_null($ret)) {
				if (isset($schema['haveref']) && $schema['haveref'] && $schema['type'] == 'hash') {
					$refInfo = self::extractRefInfo($schema);
					foreach ($refInfo as $field => $specs) {
						$childSchemaName = $schemaName . '.' . $field;
						$op = self::rget($childSchemaName, $shardId);
						if (!self::succ($op)) {
							return FALSE;
						}
					
						//Don't interfere original data structure
						$ret[$field] = $op;
					}
				}
			}
		}

        return $ret;
	}

    /**
     * Store data
     *
     * @param String $schemaName schema name
     * @param Mixed  $shardId    sharding key
     * @param Mixed  $value      data
     * @param Array  $lazySet    which referenced field need ignore when store data
     * @return Boolean
     */
    static public function doset($schemaName, $shardId, $value, $lazySet = array()) {
		$info = self::validateSchema($schemaName);
		$schemaSet = $info['base_schema'];
        $storage = self::getStorage();
		if ($info['ref_schema']) {
			$schemaInfo =& self::getSchema($info['ref_schema']);
			$schema =& $schemaInfo['struct'];
		}

		$parentShardId = $shardId;
		if ($info['extrakey']) {
			$parentShardId .= '#' . $info['extrakey'];
		}

        //If it is a hash strucutre and have reference field
        if (isset($schema['haveref']) && $schema['haveref'] && $schema['type'] == 'hash') {
            $refInfo = self::extractRefInfo($schema);
            foreach ($refInfo as $field => $specs) {
                if (!in_array($field, $lazySet)) { //First let store child field
                    if (array_key_exists($field, $value)) {
                        $v = $value[$field];

						$childSchemaName = $schemaName . '.' . $field;
                        $ret = self::set($childSchemaName, $shardId, $v);
                        if (!self::succ($ret)) {
                            return FALSE;
                        }
                    }
                }
                
                //Don't interfere original data structure
                if (isset($value[$field])) {
                    unset($value[$field]);
                }
            }
        }

        $ret = $storage->set_cache('makenosense', $schemaSet, $parentShardId, $value);

        return $ret;
    }

    /**
     * 替换记录 替换不成则add
	 *
	 * @param String $schemaName 模式名
	 * @param String $shardId    分区键
	 * @param Mixed  $value      存储值
	 * @param Mixed  $lazyReplace 忽略不replaceinto的字段
     */
	static public function doreplaceinto($schemaName, $shardId, $value, $lazyReplace = array()) {
		$info = self::validateSchema($schemaName);
		$schemaReplace = $info['base_schema'];
        $storage = self::getStorage();
		if ($info['ref_schema']) {
			$schemaInfo =& self::getSchema($info['ref_schema']);
			$schema =& $schemaInfo['struct'];
		}

		$parentShardId = $shardId;
		if ($info['extrakey']) {
			$parentShardId .= '#' . $info['extrakey'];
		}

        //If it is a hash strucutre and have reference field
        if (isset($schema['haveref']) && $schema['haveref'] && $schema['type'] == 'hash') {
            $refInfo = self::extractRefInfo($schema);
            foreach ($refInfo as $field => $specs) {
                if (!in_array($field, $lazyReplace)) { //First let store child field
                    if (array_key_exists($field, $value)) {
                        $v = $value[$field];

						$childSchemaName = $schemaName . '.' . $field;
                        $ret = self::replaceinto($childSchemaName, $shardId, $v);
                        if (!self::succ($ret)) {
                            return FALSE;
                        }
                    }
                }
                
                //Don't interfere original data structure
                if (isset($value[$field])) {
                    unset($value[$field]);
                }
            }
        }

        $ret = $storage->replace_set_cache('makenosense', $schemaReplace, $parentShardId, $value);

        return $ret;
    }

    /**
     * 添加记录
     */
	static public function doadd($schemaName, $shardId, $value, $lazyAdd = array()) {
		$info = self::validateSchema($schemaName);
		$schemaAdd = $info['base_schema'];
        $storage = self::getStorage();
		if ($info['ref_schema']) {
			$schemaInfo =& self::getSchema($info['ref_schema']);
			$schema =& $schemaInfo['struct'];
		}
		$parentShardId = $shardId;
		if ($info['extrakey']) {
			$parentShardId .= '#' . $info['extrakey'];
		}

        //If it is a hash strucutre and have reference field
        if (isset($schema['haveref']) && $schema['haveref'] && $schema['type'] == 'hash') {
            $refInfo = self::extractRefInfo($schema);
            foreach ($refInfo as $field => $specs) {
                if (!in_array($field, $lazyAdd)) { //First let store child field
                    if (array_key_exists($field, $value)) {
                        $v = $value[$field];

						$childSchemaName = $schemaName . '.' . $field;
                        $ret = self::add($childSchemaName, $shardId, $v);
                        if (!self::succ($ret)) {
                            return FALSE;
                        }
                    }
                }
                
                //Don't interfere original data structure
                if (isset($value[$field])) {
                    unset($value[$field]);
                }
            }
        }

        $ret = $storage->add_cache('makenosense', $schemaAdd, $parentShardId, $value);

        return $ret;
    }

	//自增记录
	static public function doinc($schemaName, $shardId, $value = 1) {
		$info = self::validateSchema($schemaName);
        $storage = self::getStorage();
		if ($info['extrakey']) {
			$shardId .= '#' . $info['extrakey'];
		}

        $ret = $storage->increment_cache('makenosense', $info['base_schema'], $shardId, $value);

        return $ret;
	}

	//自减记录
	static public function dodec($schemaName, $shardId, $value = 1) {
		$info = self::validateSchema($schemaName);
        $storage = self::getStorage();
		if ($info['extrakey']) {
			$shardId .= '#' . $info['extrakey'];
		}

        $ret = $storage->decrement_cache('makenosense', $info['base_schema'], $shardId, $value);

        return $ret;
	}

    /**
     * 删除记录
     */
	static public function dodel($schemaName, $shardId, $lazyDelete = array()) {
		$info = self::validateSchema($schemaName);
		$schemaDel = $info['base_schema'];
        $storage = self::getStorage();
		if ($info['ref_schema']) {
			$schemaInfo =& self::getSchema($info['ref_schema']);
			$schema =& $schemaInfo['struct'];
		}
		$parentShardId = $shardId;
		if ($info['extrakey']) {
			$parentShardId .= '#' . $info['extrakey'];
		}

        //If it is a hash strucutre and have reference field
        if (isset($schema['haveref']) && $schema['haveref'] && $schema['type'] == 'hash') {
            $refInfo = self::extractRefInfo($schema);
            foreach ($refInfo as $field => $specs) {
                if (!in_array($field, $lazyDelete)) { //First let store child field
					$childSchemaName = $schemaName . '.' . $field;
					$ret = self::del($childSchemaName, $shardId);
					if (!self::succ($ret)) {
						return FALSE;
					}
                }
            }
        }

        $ret = $storage->del_cache('makenosense', $schemaDel, $parentShardId);

        return $ret;
    }

    /**
     * 底层操作 
     * 内存没啥好操作的
     */
    static public function doexec() {
        return FALSE;
    }
}

