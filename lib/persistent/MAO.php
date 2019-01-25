<?php
/**
 * Memcache连接及封装数据的基类
 *
 * @package core
 */
class MAO
{
	protected $conf = NULL;

	function __construct() {
	}

	function __destruct() {
	}

	/**
	 * 过滤返回字段数据
	 * @param Array $fdarr	字段数组
	 * @param Array $maparr 	固有字段数组
	 * @param Array $row 	数据行
	 * @return Array 过滤完毕的信息数组
	 */
	protected function &filterReturnRow(&$fdarr, &$maparr, &$row) {
		if(!$fdarr) return $row;

		$ret = array();
		foreach($fdarr as $f) {
			if(!in_array($f, $maparr))
				continue;
			else
				$ret[$f] = $row[$f];
		}

		return $ret;
	}

	protected function &filterInputRow(&$row, &$maparr) {
		$ret = array();
		foreach($row as $key => $value) {
			if(in_array($key, $maparr))
				$ret[$key] = $value;
		}

		return $ret;
	}

	protected function isCompleteField(&$row, &$maparr) {
		foreach($maparr as $key => $value) {
			if(!array_key_exists($value, $row)) return false;
		}

		return true;
	}

	protected function load_mao_config($vhost = "") {
		return __load_core('pub:'.$vhost.'.mao.cfg', 'mao/configs');
	}

	protected function get_mao_config($vhost, $vkey, $params) {
		$this->load_mao_config($vhost);
		$mao_config = call_user_func('mao_cfg_'.$vhost, $vkey, $params);
		return $mao_config;
	}

	/**
	 * 读取db
	 * param	array	fields	查询字段，格式 array('key', 'value')
	 * param	array	clause	查询条件，格式 array('key' => 'gb_1234')
	 * param	string	type	查询类型，result: 返回一个结果
	 * param	array	orderby	排序条件，格式 array('uid' => 'desc', 'time' => 'asc')
	 * param	array	limit	本次查询的页数以及每页记录数，格式 array('key' => 'gb_1234')
	 */
	public function fetch_db($vhost, $vkey, $paras, $fields = array(), $clause=array(), $type='value', $orderby=array(), $limit=array('page'=>1, 'limit'=>10)) {
		if(!$vhost || !$vkey || !$fields || !is_array($fields) || !$clause || !is_array($clause)) {
			$this->error_msg($vhost, $vkey, "[fetch_db] params error (vhost=".json_encode($vhost).", vkey=".json_encode($vkey).", fields=".json_encode($fields).", clause=".json_encode($clause).")");
			return false;
		}

		if(!$this->load_mao_config($vhost)) {
			$this->error_msg($vhost, $vkey, "[fetch_db] load config error (vhost=".json_encode($vhost).")");
			return false;
		}
		$mc_config = call_user_func('mao_cfg_' . $vhost, $vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[fetch_db] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user || !$db_dbname || !$db_tblname) {
			$this->error_msg($vhost, $vkey, "[fetch_db] get config error (arr_db_config=".json_encode($arr_db_config).")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$limit_clause = "";
		if($type === "rows") {
			if(!is_array($limit) || !$limit || !isset($limit['page']) || !isset($limit['limit'])) {
				$this->error_msg($vhost, $vkey, "[fetch_db] limit params error (limit=".json_encode($limit).")");
				return false;
			}
			$limit_start = ($limit['page'] - 1) * $limit['limit'];
			$limit_count = $limit['limit'];
			if($limit_start < 0 || $limit_count < 0) {
				$this->error_msg($vhost, $vkey, "[fetch_db] limit params error (limit_start=".json_encode($limit_start).", limit_count=".json_encode($limit_count).")");
				return false;
			}
			$limit_clause = "limit ".$limit_start.", ".$limit_count;
		}

		// 查下条件
		$db_clause = "";
		foreach($clause as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_clause) {
				$db_clause .= " and ";
			}
			$db_clause .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		if(!$db_clause) {
			$this->error_msg($vhost, $vkey, "[fetch_db] clause error (db_clause=".json_encode($db_clause).")");
			return false;
		}

		// 排序
		$order_clause = "";
		if($orderby && is_array($orderby)) {
			foreach($orderby as $field => $order) {
				if(!$field) {
					continue;
				}
				if($order === "desc") {
					$order = "desc";
				}else {
					$order = "asc";
				}
				if($order_clause) {
					$order_clause .= ", ";
				}
				$order_clause .= "`".$field ."` ".$order;
			}
			if($order_clause) {
				$order_clause = "order by ".$order_clause;
			}
		}

		$db_field = "";
		for($i = 0; $i < count($fields); $i++) {
			if(!$fields[$i]) {
				continue;
			}
			if($db_field) {
				$db_field .= ", ";
			}
			$db_field .= "`".$fields[$i] ."`";
		}
		if(!$db_field) {
			$this->error_msg($vhost, $vkey, "[fetch_db] fetch field error (db_field=".json_encode($db_field).")");
			return false;
		}

		$fetch_result = array();
		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			$this->error_msg($vhost, $vkey, "[fetch_db] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$sql = "select ".$db_field." from ".$db_dbname.".".$db_tblname." where ".$db_clause." ".$order_clause." ".$limit_clause;
		$res = mysql_query($sql);

		if($res) {
			if($type === "value") {
				$row = mysql_fetch_row($res);
				$fetch_result = $row[0];
			}else if($type === "row") {
				$fetch_result = mysql_fetch_assoc($res);
			}else if($type === "rows") {
				while($row = mysql_fetch_assoc($res)) {
					array_push($fetch_result, $row);
				}
			}
		}else {
			$fetch_result = false;
			$err_msg = "[fetch_db] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		return $fetch_result;
	}

	/**
	 * 统计记录数量
	 * param	array	fields	查询字段，格式 array('key', 'value')
	 * param	array	clause	查询条件，格式 array('key' => 'gb_1234')
	 */
	public function fetch_db_count($vhost, $vkey, $paras, $clause=array()) {
		if(!$vhost || !$vkey || !$clause || !is_array($clause)) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] params error (vhost=".json_encode($vhost).", vkey=".json_encode($vkey).", clause=".json_encode($clause).")");
			return false;
		}
		if(!$this->load_mao_config($vhost)) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] load config error (vhost=".json_encode($vhost).")");
			return false;
		}
		$mc_config = call_user_func('mao_cfg_' . $vhost, $vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user || !$db_dbname || !$db_tblname) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] get config error (arr_db_config=".json_encode($arr_db_config).")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$db_clause = "";
		foreach($clause as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_clause) {
				$db_clause .= " and ";
			}
			$db_clause .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		if(!$db_clause) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] clause error (db_clause=".json_encode($db_clause).")");
			return false;
		}

		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			$this->error_msg($vhost, $vkey, "[fetch_db_count] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$sql = "select count(1) from ".$db_dbname.".".$db_tblname." where ".$db_clause;
		$res = mysql_query($sql);
		if($res) {
			$row = mysql_fetch_row($res);
		}else {
			$row = false;
			$err_msg = "[fetch_db_count] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		if($row === false) {
			return false;
		}
		return $row[0];
	}

	/**
	 * 设置db
	 * param	array	value	修改值，格式 array('key' => 'gb_1234', 'val' => 'value')
	 * param	array	clause	修改条件，格式 array('key' => 'gb_1234')
	 */
	public function update_db($vhost, $vkey, $paras, $set_value=array(), $clause=array()) {
		if(!$vhost || !$vkey || !$set_value || !is_array($set_value) || !$clause || !is_array($clause)) {
			$this->error_msg($vhost, $vkey, "[update_db] params error (vhost=".json_encode($vhost).", vkey=".json_encode($vkey).", set_value=".json_encode($set_value).", clause=".json_encode($clause).")");
			return false;
		}
		if(!$this->load_mao_config($vhost)) {
			$this->error_msg($vhost, $vkey, "[update_db] load config error (vhost=".json_encode($vhost).")");
			return false;
		}
		$mc_config = call_user_func('mao_cfg_' . $vhost, $vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[update_db] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user || !$db_dbname || !$db_tblname) {
			$this->error_msg($vhost, $vkey, "[update_db] get config error (arr_db_config=".json_encode($arr_db_config).")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$db_clause = "";
		foreach($clause as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_clause) {
				$db_clause .= " and ";
			}
			$db_clause .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		if(!$db_clause) {
			$this->error_msg($vhost, $vkey, "[update_db] clause error (db_clause=".json_encode($db_clause).")");
			return false;
		}

		$db_value = "";
		foreach($set_value as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_value) {
				$db_value .= ", ";
			}
			$db_value .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		if(!$db_value) {
			$this->error_msg($vhost, $vkey, "[update_db] set value error (db_value=".json_encode($db_value).")");
			return false;
		}
		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			$this->error_msg($vhost, $vkey, "[update_db] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$sql = "update ".$db_dbname.".".$db_tblname." set ".$db_value." where ".$db_clause;
		$res = mysql_query($sql);
		if(!$res) {
			$err_msg = "[update_db] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		return $res;
	}

	/**
	 * 删除db
	 * param	array	clause	删除条件，格式 array('key' => 'gb_1234')
	 */
	public function del_db($vhost, $vkey, $paras, $clause=array()) {
		if(!$vhost || !$vkey || !$clause || !is_array($clause)) {
			$this->error_msg($vhost, $vkey, "[del_db] params error (vhost=".json_encode($vhost).", vkey=".json_encode($vkey).", clause=".json_encode($clause).")");
			return false;
		}
		if(!$this->load_mao_config($vhost)) {
			$this->error_msg($vhost, $vkey, "[del_db] load config error (vhost=".json_encode($vhost).")");
			return false;
		}
		$mc_config = call_user_func('mao_cfg_' . $vhost, $vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[del_db] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user || !$db_dbname || !$db_tblname) {
			$this->error_msg($vhost, $vkey, "[del_db] get config error(".$db_host.":".$db_port.")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$db_clause = "";
		foreach($clause as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_clause) {
				$db_clause .= " and ";
			}
			$db_clause .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		if(!$db_clause) {
			$this->error_msg($vhost, $vkey, "[del_db] clause error (db_clause=".json_encode($db_clause).")");
			return false;
		}
		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			$this->error_msg($vhost, $vkey, "[del_db] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$sql = "delete from ".$db_dbname.".".$db_tblname." where ".$db_clause;
		$res = mysql_query($sql);
		if(!$res) {
			$err_msg = "[del_db] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		return $res;
	}

	/**
	 * 删除db
	 * param	array	set_value	插入数据，格式 array('key' => 'gb_1234', 'val' => 'value')
	 * param	bool	is_insert_id	是否需要返回自增ID
	 */
	public function insert_db($vhost, $vkey, $paras, $set_value=array(), $is_insert_id=false) {
		if(!$set_value || !is_array($set_value)) {
			$this->error_msg($vhost, $vkey, "[insert_db] params error (set_value=".json_encode($set_value).")");
			return false;
		}
		if(!$vhost || !$vkey) {
			$this->error_msg($vhost, $vkey, "[insert_db] params error (vhost=".json_encode($vhost).", vkey=".json_encode($vkey).")");
			return false;
		}
		if(!$this->load_mao_config($vhost)) {
			$this->error_msg($vhost, $vkey, "[insert_db] load config error (vhost=".json_encode($vhost).")");
			return false;
		}
		$mc_config = call_user_func('mao_cfg_' . $vhost, $vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[insert_db] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user || !$db_dbname || !$db_tblname) {
			$this->error_msg($vhost, $vkey, "[insert_db] get config error(".$db_host.":".$db_port.")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$db_value = "";
		foreach($set_value as $field => $value) {
			if(!$field) {
				continue;
			}
			if($db_value) {
				$db_value .= ", ";
			}
			$db_value .= "`".$field ."`"." = '".mysql_escape_string($value)."'";
		}
		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			$this->error_msg($vhost, $vkey, "[insert_db] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$sql = "insert into ".$db_dbname.".".$db_tblname." set ".$db_value;
		$res = mysql_query($sql);
		if($res) {
			if($is_insert_id) {
				$insert_id = mysql_insert_id($dbh);
			}
		}
		if(!$res) {
			$err_msg = "[insert_db] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		if($is_insert_id) {
			return $insert_id;
		}else {
			return $res;
		}
	}

	/**
	 * 读取db
	 * param	array	fields	查询字段，格式 array('key', 'value')
	 * param	array	clause	查询条件，格式 array('key' => 'gb_1234')
	 * param	string	type	查询类型，result: 返回一个结果
	 * param	array	limit	本次查询的页数以及每页记录数，格式 array('key' => 'gb_1234')
	 */
	public function excute_sql($vhost, $vkey, $paras, $sql, $type="row") {
        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[excute_sql] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_db_config = $mc_config['db_server'];
		$db_host = $arr_db_config['host'];
		$db_port = $arr_db_config['port'];
		$db_user = $arr_db_config['user'];
		$db_pwd = $arr_db_config['passwd'];
		$db_dbname = $arr_db_config['dbname'];
		$db_tblname = $arr_db_config['tblname'];
		if(!$arr_db_config || !is_array($arr_db_config) || !$db_host || !$db_user) {
			$this->error_msg($vhost, $vkey, "[excute_sql] get config error (arr_db_config=".json_encode($arr_db_config).")");
			return false;
		}
		if($db_port) {
			$db_host = $db_host.":".$db_port;
		}

		$fetch_result = array();
		$dbh = mysql_connect($db_host, $db_user, $db_pwd);
		if(!$dbh) {
			
			$this->error_msg($vhost, $vkey, "[excute_sql] connect failed(".$db_user.":".$db_pwd."@".$db_host.")");
			return false;
		}
		$seldb = mysql_select_db($db_dbname, $dbh);
		if (!$seldb) {
			$this->error_msg($vhost, $vkey, "[excute_sql] select db failed user:$db_user, pass:$db_pwd, host:$db_host, db:$db_dbname");
			return FALSE;
		}

		//将sql中的表名改为schema和shardid对应的表名
		$sql = str_replace('{table}', $db_tblname, $sql);
		$res = mysql_query($sql);
		//Debug::dump($sql, $res, $type);
		if($res) {
			if($type === "value") {
				$row = mysql_fetch_row($res);
				if ($row) {
					$fetch_result = $row[0];
				}
			}else if($type === "row") {
				$v = mysql_fetch_assoc($res);
				if ($v) {
					$fetch_result = $v;
				}
			}else if($type === "rows") {
				while($row = mysql_fetch_assoc($res)) {
					array_push($fetch_result, $row);
				}
			}else if($type === "result") {
				$fetch_result = $res;
			} else if ($type === 'insertid') {
				$fetch_result = mysql_insert_id($dbh);
			}
		}else {
			$fetch_result = false;
			$err_msg = "[excute_sql] ". mysql_errno() . ": " . mysql_error();
			$this->error_msg($vhost, $vkey, $err_msg);
		}
		mysql_close($dbh);
		return $fetch_result;
	}

	/**
	 * 获取缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @return mixed 查询成功返回信息数组，失败则返回false
	 */
	public function get_cache($vhost, $vkey, $paras=array()) {
		global $__MC_PHP_CACHE;
		$__MC_PHP_CACHE = array();

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[get_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[get_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key || is_array($mc_key)) {
			$this->error_msg($vhost, $vkey, "[get_cache] mc_key error (mc_key=".json_encode($mc_key).")");
			return false;
		}

		/**
		 * 打包格式
		 */
		$pack_type = $mc_config['pack_type'];
		/**
		 * 是否需要反序列化
		 */
		$is_unserialize = $mc_config['is_unserialize'];
		/**
		 * db类型
		 */
		$db_type = $mc_config['db_type'];
		/**
		 * db服务器配置
		 */
		$db_config = $mc_config['db_server'];

		$get_mc_result = null;			// 查询结果
		if(isset($__MC_PHP_CACHE[$mc_key]) && $__MC_PHP_CACHE[$mc_key]) {
			$get_mc_result = $__MC_PHP_CACHE[$mc_key];
		}

		/**
		 * 查询memcache
		 */
		if(!$get_mc_result) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
			if(!$mc_connect) {
				$mc_connect = memcache_connect($mc_host, $mc_port);
			}
			if(!$mc_connect) {
				$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
				_send_udp_log(1, "mc_conn_2", $err_msg);
				$this->error_msg($vhost, $vkey, "[get_cache] mc connect error(".$mc_host.":".$mc_port.")");
				return false;
			}
			$get_mc_result = memcache_get($mc_connect, $mc_key);

			memcache_close($mc_connect);
			if($get_mc_result === false) {
				return null;
			}
			$__MC_PHP_CACHE[$mc_key] = $get_mc_result;
		}
		if(defined("CORE_DAEMON") && CORE_DAEMON === true) {
			unset($__MC_PHP_CACHE);
		}
		if($is_unserialize) {
			$unpack_result = unserialize($get_mc_result);
		}else {
			$unpack_result = _kingnet_unpack($pack_type, $get_mc_result);
		}
		if($unpack_result === false) {
			$this->error_msg($vhost, $vkey, "[get_cache] data unpack error");
		}
		return $unpack_result;
	}

	/**
	 * 获取缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @return mixed 查询成功返回信息数组，失败则返回false
	 */
	public function &get_multi_cache($vhost, $vkey, $paras=array()) {
        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[get_multi_cache] get config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[get_multi_cache] host config error (mc_host=".json_encode($mc_host).", mc_port=".json_encode($mc_port).")");
			return false;
		}
		$mc_key = $mc_config['mc_key'];
		if(!$mc_key || !is_array($mc_key)) {
			$this->error_msg($vhost, $vkey, "[get_multi_cache] mc key error (mc_key=".json_encode($mc_key).")");
			return false;
		}

		/**
		 * 打包格式
		 */
		$pack_type = $mc_config['pack_type'];
		/**
		 * 是否需要反序列化
		 */
		$is_unserialize = $mc_config['is_unserialize'];
		$get_mc_result = array();			// 查询结果

		/**
		 * 查询memcache
		 */
		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[get_multi_cache] connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$get_mc_result = memcache_get($mc_connect, $mc_key);
		memcache_close($mc_connect);

		if(is_array($get_mc_result)) {
			foreach($get_mc_result as $key => $value) {
				$tmp = explode("_", $key);
				$count = count($tmp);
				$new_key = $tmp[$count - 1];
                //$new_key = $key;
				unset($get_mc_result[$key]);
				if($is_unserialize) {
					$get_mc_result[$new_key] = unserialize($value);
				}else {
					$get_mc_result[$new_key] = _kingnet_unpack($pack_type, $value);
				}
			}
		}
		return $get_mc_result;
	}

	/**
	 * 设置缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @param value 缓存值，设置为对应数据表一行记录中的常驻或者淘汰型数据
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function set_cache($vhost, $vkey, $paras, $value) {
		global $__MC_PHP_CACHE;
		//$__MC_PHP_CACHE = array();

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[set_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[set_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key) {
			$this->error_msg($vhost, $vkey, "[set_cache] mc_key error (mc_key=".json_encode($mc_key).")");
			return false;
		}
		$mc_compress = $mc_config['mc_compress'] ? MEMCACHE_COMPRESSED : 0;
		$mc_expire = $mc_config['mc_expire'] > 0 ? $mc_config['mc_expire'] : 0;

		/**
		 * 检查字段完整性
		 */
		if($mc_config['check_field'] == true) {
			$mc_fields = $mc_config['fields'];
			if(!$mc_fields || !is_array($mc_fields)) {
				$err_log = "mc_fields error|set_mc|".$mc_key;
				_send_udp_log(1, "error_tt", $err_log);
				$this->error_msg($vhost, $vkey, "[set_cache] check field error (mc_key=".json_encode($mc_key).")");
				return false;
			}
			foreach($mc_fields as $v_field => $v_type) {
				if(!isset($value[$v_field])) {
					$err_log = "mc_fields error[".$v_field."]|set_mc|".$mc_key;
					_send_udp_log(1, "error_tt", $err_log);
					$this->error_msg($vhost, $vkey, "[set_cache] not find field (".$v_field.") in the values");
					return false;
				}
			}
		}

		$mc_value = _kingnet_pack($mc_config['pack_type'], $value);
		unset($value);
		if($mc_value === false) {
			$err_log = "_kingnet_pack error|set_mc|".$mc_key;
			_send_udp_log(1, "error_tt", $err_log);
			$this->error_msg($vhost, $vkey, "[set_cache] data pack failed");
			return false;
		}

		/**
		 * db类型
		 */
		$db_type = $mc_config['db_type'];
		/**
		 * db服务器配置
		 */
		$db_config = $mc_config['db_server'];

		/**
		 * 修改memcache
		 */
		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[set_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$res = memcache_set($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		if(!$res) {
			$res = memcache_set($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		}
		memcache_close($mc_connect);
		if(!$res) {
			$err_log = "mc set error|set_mc(".$mc_host.":".$mc_port.")|".$mc_key;
			_send_udp_log(1, "error_tt", $err_log);
			$this->error_msg($vhost, $vkey, "[set_cache] mc set error (mc_key=".json_encode($mc_key).", mc_value=".json_encode($mc_value).")");
			return false;
		}

		$__MC_PHP_CACHE[$mc_key] = $mc_value;

		if(defined("CORE_DAEMON") && CORE_DAEMON === true) {
			unset($__MC_PHP_CACHE);
		}
		unset($mc_config);
		return $res;
	}

	/**
	 * 设置缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @param value 缓存值，设置为对应数据表一行记录中的常驻或者淘汰型数据
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function replace_set_cache($vhost, $vkey, $paras, $value)
	{
		global $__MC_PHP_CACHE;

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[replace_set_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[replace_set_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key) {
			$this->error_msg($vhost, $vkey, "[replace_set_cache] mc_key error (mc_key=".json_encode($mc_key).")");
			return false;
		}
		$mc_compress = $mc_config['mc_compress'] ? MEMCACHE_COMPRESSED : 0;
		$mc_expire = $mc_config['mc_expire'] > 0 ? $mc_config['mc_expire'] : 0;

		/**
		 * 检查字段完整性
		 */
		if($mc_config['check_field'] == true) {
			$mc_fields = $mc_config['fields'];
			if(!$mc_fields || !is_array($mc_fields)) {
				$err_log = "mc_fields error|set_mc|".$mc_key;
				_send_udp_log(1, "error_tt", $err_log);
				$this->error_msg($vhost, $vkey, "[replace_set_cache] check field error (mc_key=".json_encode($mc_key).")");
				return false;
			}
			foreach($mc_fields as $v_field => $v_type) {
				if(!isset($value[$v_field])) {
					$err_log = "mc_fields error[".$v_field."]|set_mc|".$mc_key;
					_send_udp_log(1, "error_tt", $err_log);
					$this->error_msg($vhost, $vkey, "[replace_set_cache] not find field (".$v_field.") in the values");
					return false;
				}
			}
		}

		$mc_value = _kingnet_pack($mc_config['pack_type'], $value);
		unset($value);
		if($mc_value === false) {
			$err_log = "_kingnet_pack error|set_mc|".$mc_key;
			_send_udp_log(1, "error_tt", $err_log);
			$this->error_msg($vhost, $vkey, "[replace_set_cache] data pack failed");
			return false;
		}

		/**
		 * db类型
		 */
		$db_type = $mc_config['db_type'];
		/**
		 * db服务器配置
		 */
		$db_config = $mc_config['db_server'];
		/**
		 * 修改memcache
		 */
		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[replace_set_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		//Debug::dump($mc_key, $mc_value);
		$res = memcache_replace($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		if(!$res) {
			$res = memcache_add($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		}
		memcache_close($mc_connect);
		if(!$res) {
			$err_log = "mc set error|set_mc(".$mc_host.":".$mc_port.")|".$mc_key;
			_send_udp_log(1, "error_tt", $err_log);
			$this->error_msg($vhost, $vkey, "[replace_set_cache] mc set error, maybe this key is exists (mc_key=".json_encode($mc_key).", mc_value=".json_encode($mc_value).")");
			return false;
		}

		$__MC_PHP_CACHE[$mc_key] = $mc_value;

		if(defined("CORE_DAEMON") && CORE_DAEMON === true) {
			unset($__MC_PHP_CACHE);
		}
		unset($mc_config);
		return $res;
	}

	/**
	 * 添加缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @param value 缓存值，设置为对应数据表一行记录中的常驻或者淘汰型数据
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function add_cache($vhost, $vkey, $paras, $value) {
		global $__MC_PHP_CACHE;

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[add_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[add_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key) {
			$this->error_msg($vhost, $vkey, "[add_cache] mc_key error (mc_key=".json_encode($mc_key).")");
			return false;
		}
		$mc_compress = $mc_config['mc_compress'] ? MEMCACHE_COMPRESSED : 0;
		$mc_expire = $mc_config['mc_expire'] > 0 ? $mc_config['mc_expire'] : 0;

		/**
		 * 检查字段完整性
		 */
		if($mc_config['check_field'] == true) {
			$mc_fields = $mc_config['fields'];
			if(!$mc_fields || !is_array($mc_fields)) {
				$this->error_msg($vhost, $vkey, "[add_cache] check field error (mc_key=".json_encode($mc_key).")");
				return false;
			}
			foreach($mc_fields as $v_field => $v_type) {
				if(!isset($value[$v_field])) {
					$this->error_msg($vhost, $vkey, "[add_cache] not find field (".$v_field.") in the values");
					return false;
				}
			}
		}

		$mc_value = _kingnet_pack($mc_config['pack_type'], $value);
		unset($value);
		if($mc_value === false) {
			$this->error_msg($vhost, $vkey, "[add_cache] data pack failed");
			return false;
		}

		/**
		 * 修改memcache
		 */
		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[add_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		//Debug::dump($mc_key, $mc_value);
		$res = memcache_add($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		if(!$res) {
            //$res = memcache_add($mc_connect, $mc_key, $mc_value, $mc_compress, $mc_expire);
		}
		memcache_close($mc_connect);
		if(!$res) {
			$this->error_msg($vhost, $vkey, "[add_cache] mc add error, maybe this key is exists (mc_key=".json_encode($mc_key).", mc_value=".json_encode($mc_value).")");
			return FALSE;
		}
		unset($__MC_PHP_CACHE[$mc_key], $mc_config);
		return $res;
	}

	/**
	 * 缓存自增
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @param value 自增的数量
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function increment_cache($vhost, $vkey, $paras, $num) {
		global $__MC_PHP_CACHE;

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[increment_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[increment_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key) {
			$this->error_msg($vhost, $vkey, "[increment_cache] check field error (mc_key=".json_encode($mc_key).")");
			return false;
		}
		$mc_compress = 0;
		$mc_expire = 0;

		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[increment_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$ret = memcache_increment($mc_connect, $mc_key, $num);
		if(!$ret) {
			if($mc_config['add_when_null']) {
				$ret = memcache_add($mc_connect, $mc_key, $num);
			}
		}
		memcache_close($mc_connect);
		unset($__MC_PHP_CACHE[$mc_key], $mc_config);
		return $ret;
	}

	/**
	 * 缓存自减
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @param value 自减的数量
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function decrement_cache($vhost, $vkey, $paras, $num) {
		global $__MC_PHP_CACHE;

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[decrement_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port) {
			$this->error_msg($vhost, $vkey, "[decrement_cache] get config error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$mc_key = trim($mc_config['mc_key']);
		if(!$mc_key) {
			$this->error_msg($vhost, $vkey, "[decrement_cache] check field error (mc_key=".json_encode($mc_key).")");
			return false;
		}
		$mc_compress = 0;
		$mc_expire = 0;

		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[decrement_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$ret = memcache_decrement($mc_connect, $mc_key, $num);
		memcache_close($mc_connect);
		unset($__MC_PHP_CACHE[$mc_key], $mc_config);
		return $ret;
	}

	/**
	 * 清除缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @return mixed 成功返回true，失败则返回false
	 */
	public function del_cache($vhost, $vkey, $paras) {
		global $__MC_PHP_CACHE;

        $mc_config = s7::get_config($vkey, $paras);
		if(!$mc_config || !is_array($mc_config)) {
			$this->error_msg($vhost, $vkey, "[del_cache] call config error (mc_config=".json_encode($mc_config).")");
			return false;
		}
		$arr_mc_config = $mc_config['mc_server'];
		$mc_host = $arr_mc_config['host'];
		$mc_port = $arr_mc_config['port'];
		$mc_key = trim($mc_config['mc_key']);
		if(!$arr_mc_config || !is_array($arr_mc_config) || !$mc_host || !$mc_port || !$mc_key) {
			$this->error_msg($vhost, $vkey, "[del_cache] get config error (mc_host=".json_encode($mc_host).", mc_port=".json_encode($mc_port).", mc_key=".json_encode($mc_key).")");
			return false;
		}

		/**
		 * 修改memcache
		 */
		$mc_connect = memcache_connect($mc_host, $mc_port);
		if(!$mc_connect) {
			$mc_connect = memcache_connect($mc_host, $mc_port);
		}
		if(!$mc_connect) {
			$err_msg = $_SERVER['SERVER_ADDR']."->".$mc_host.":".$mc_port;
			_send_udp_log(1, "mc_conn_2", $err_msg);
			$this->error_msg($vhost, $vkey, "[del_cache] mc connect error(".$mc_host.":".$mc_port.")");
			return false;
		}
		$res = memcache_delete($mc_connect, $mc_key);
		memcache_close($mc_connect);

		unset($__MC_PHP_CACHE[$mc_key], $mc_config);
		return $res;
	}

	/**
	 * 清除缓存
	 * @param vhost 服务器配置名称
	 * @param vkey 键配置名称
	 * @param paras 附加参数
	 * @return mixed 成功返回true，失败则返回false
	 */
	protected function error_msg($vhost, $vkey, $msg) {
		//echo $vhost." - ".$vkey." - ".$msg."<br>\n";
	}
}
