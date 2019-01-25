<?php
/**
 * @file   mysql数据库存储层。值得注意的是mysql类的schema不支持子引用. 自身间和与memcache类
 * 		   schema间都不可以相互引用
 * @author greathqy@gmail.com
 */
//Mysql操作封装类
class Mysqlstore extends s7
{
	/**
	 * 从shardId和 schema描述里构造出set数据的sql
	 *
	 * @param String	$shardId	分区键
	 * @param Array		$schema		schema信息
	 * @param Array		$arrVal		要存的值
	 */
	static public function convertSetSqlFromValue($shardId, & $schema, $arrVal, $insertOnly = FALSE) {
		if ($insertOnly) {
			$sql = "INSERT INTO {table} ({keys}) VALUES ({values})";
		} else {
			$sql = "INSERT INTO {table} ({keys}) VALUES ({values}) ON DUPLICATE KEY UPDATE {update}";
		}
		$keys = array();
		if (!isset($schema['defines']['__mysql__']['condition_key'])) { //Primary key即是条件键
			if (!is_array($schema['defines']['__mysql__']['primary_key'])) {
				$keys[] = $schema['defines']['__mysql__']['primary_key'];
			} else {
				$keys[] = $schema['defines']['__mysql__']['primary_key']['field'];
			}
		} else {
			$keys[] = $schema['defines']['__mysql__']['condition_key'];
		}
		$arrVal[$keys[0]] = $shardId;
		$arrKeys = array_keys($schema['defines']);
		foreach($arrKeys as $key) {
			if ($key != '__mysql__' && $schema['defines'][$key][0] != '&') {
				$keys[] = $key;
			}
		}	
		$values = array();
		foreach($keys as & $key) {
			$v = isset($arrVal[$key]) ? $arrVal[$key] : '';
			$values[] = mysql_escape_string($v);
			$key = '`' . $key . '`';
		}
		if (!$insertOnly) {
			$kvPair = array_combine($keys, $values);
			$update = '';
			foreach ($kvPair as $k => $v) {
				$update .= "$k=$v, ";
			}
			$update = trim($update, ', ');
		}
		$keys = join(',', $keys);
		$values = join(',', $values);
		$search = array('{keys}', '{values}');
		$replace = array($keys, $values);
		if (!$insertOnly) {
			$search[] = '{update}';
			$replace[] = $update;
		}
		$sql = str_replace($search, $replace, $sql);

		return $sql;
	}

	/**
	 * 构造取一条数据的sql
	 */
	static public function convertGetSqlFromValue($shardId, & $schema) {
		$sql = "SELECT {fields} FROM {table} WHERE {condkey} = '" . mysql_escape_string($shardId) . "'";

		$condKey = '';
		if (!isset($schema['defines']['__mysql__']['condition_key'])) { //primary key即是条件键
			if (!is_array($schema['defines']['__mysql__']['primary_key'])) {
				$condKey = $schema['defines']['__mysql__']['primary_key'];
			} else {
				$condKey = $schema['defines']['__mysql__']['primary_key']['field'];
			}
		} else {
			$condKey = $schema['defines']['__mysql__']['condition_key'];
		}

		$fields = array();
		$arrKeys = array_keys($schema['defines']);
		foreach($arrKeys as $key) {
			if ($key != '__mysql__' && $schema['defines'][$key][0] != '&') {
				$fields[] = '`' . $key . '`';
			}
		}	
		$fields = join(',', $fields);
		$sql = str_replace(array('{fields}', '{condkey}'), array($fields, $condKey), $sql);

		return $sql;
	}

	//构造一条删除数据的sql
	static public function convertDelSqlFromShardId($shardId, &$schema) {
		$sql = "DELETE FROM {table} WHERE {condkey} = '" . mysql_escape_string($shardId) . "'";

		$condKey = '';
		if (!isset($schema['defines']['__mysql__']['condition_key'])) { //primary key即是条件键
			if (!is_array($schema['defines']['__mysql__']['primary_key'])) {
				$condKey = $schema['defines']['__mysql__']['primary_key'];
			} else {
				$condKey = $schema['defines']['__mysql__']['primary_key']['field'];
			}
		} else {
			$condKey = $schema['defines']['__mysql__']['condition_key'];
		}
		if (!$condKey) {
			throw new Exception("Invalid condition key when construct delete sql statement!");
		}
		$sql = str_replace('{condkey}', $condKey, $sql);

		return $sql;
	}

	//获得数据 $schemaName = extradata
	static public function doget($schemaName, $shardId) {
		$schemaInfo =& self::getSchema($schemaName);
        $schema = $schemaInfo['struct'];
        $storage = self::getStorage();
        
		$shardId = self::getOriginalShardId($shardId);
       
		//Load db row self one row
		$sql = self::convertGetSqlFromValue($shardId, $schema);
		$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $sql, 'row');
		if (!self::succ($ret)) {
			return FALSE;
        } 

		if (is_array($ret) && !$ret) {
			$ret = NULL;  //make it behave like memcache
		}

        return $ret;
	}

	//递归获取数据
	static public function dorget($schemaName, $shardId) {
		return self::doget($schemaName, $shardId);
	}

	/**
	 * 存数据库
	 *
	 * @param String	$schemaName	模式名
	 * @param String	$shardId	分区id
	 * @param Mixed		$value		存储值
	 */
	static public function doset($schemaName, $shardId, $value) {
		$schemaInfo =& self::getSchema($schemaName);
        $schema =& $schemaInfo['struct'];
        $storage = self::getStorage();
		$shardId = self::getOriginalShardId($shardId);

		//Mysql definition don't support references
		//So we don't check to see if here are any referenced field in $value
        
		$sql = self::convertSetSqlFromValue($shardId, $schema, $value);
		$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $sql, 'result');

        return $ret;
	}

    /**
     * Replace else add
     */
	static public function doreplaceinto($schemaName, $shardId, $value) {
		return self::doset($schemaName, $shardId, $value);
	}

    /**
     * 添加数据
     */
	static public function doadd($schemaName, $shardId, $value) {
		$schemaInfo =& self::getSchema($schemaName);
        $schema =& $schemaInfo['struct'];
        $storage = self::getStorage();
		$shardId = self::getOriginalShardId($shardId);

		$sql = self::convertSetSqlFromValue($shardId, $schema, $value, TRUE);
		$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $sql, 'insertid');

        return $ret;
	}

    //自增对数据库没意义
	static public function doinc($schemaName, $shardId) {
		return FALSE;
	}

    //自减对数据库没意义
	static public function dodec($schemaName, $shardId) {
		return FALSE;
	}

    /**
     * 删除数据
     */
	static public function dodel($schemaName, $shardId) {
		$schemaInfo =& self::getSchema($schemaName);
        $schema =& $schemaInfo['struct'];
        $storage = self::getStorage();
		$shardId = self::getOriginalShardId($shardId);

		$sql = self::convertDelSqlFromShardId($shardId, $schema);
		$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $sql, 'result');

        return $ret;
	}

    //执行自定义规则查询
	/*
	 * $rule = array(
	 * 'op' => insert/delete/update/select/selectOne/count
	 * 'data' => array('start'=>xxx,'limit'=>xx), 
	 * @see MysqlBuilder.php
	 * )
	 */
	static public function doexec($schemaName, $shardId, $op, $data, $attrs = array()) {
		
		$schemaInfo =& self::getSchema($schemaName);

        $schema =& $schemaInfo['struct'];
        $storage = self::getStorage();
		$shardId = self::getOriginalShardId($shardId);

		$config = array(
			'sql' => array('prefix' => '', 'type' => ''),
			'insert' => array('prefix' => 'INSERT INTO ', 'type' => 'insertid'),
			'update' => array('prefix' => 'UPDATE ', 'type' => 'result'),
			'select' => array('prefix' => 'SELECT {fields} FROM ', 'type' => 'rows'),
			'selectOne' => array('prefix' => 'SELECT {fields} FROM ', 'type' => 'row'),
			'count' => array('prefix' => 'SELECT COUNT(*) FROM ', 'type' => 'value'),
			'delete' => array('prefix' => 'DELETE FROM ', 'type' => 'result'),
			);

		if (!array_key_exists($op, $config)) {
			throw new Exception("mysql操作exec函数收到的rule参数格式不对!");
		}
		if ($op == 'sql') { //sql类型查询
			if (!$attrs || is_array($attrs)) {
				$attrs = 'result';
			}
			$allowed = array('insertid', 'result', 'rows', 'row', 'value');
			if (!in_array($attrs, $allowed)) {
				$attrs = 'result';
			}
			
			$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $data, $attrs);
		} else {
			if ($op == 'selectOne' || $op == 'selectone') {
				$attrs['limit'] = 1;
				$attrs['offset'] = 0;
			}
			$fields = isset($attrs['select']) ? $attrs['select'] : '*';
			$config['select']['prefix'] = str_replace('{fields}', $fields, $config['select']['prefix']);
			$config['selectOne']['prefix'] = str_replace('{fields}', $fields, $config['selectOne']['prefix']);

			$sql = MysqlBuilder::buildSql($op, $data, $attrs);
			$sql = $config[$op]['prefix'] . ' {table}' . $sql;
			$ret = $storage->excute_sql('makenosense', $schemaName, $shardId, $sql, $config[$op]['type']);
		}

		if (!self::succ($ret)) {
			
			return FALSE;
		}

        return $ret;
	}
}
