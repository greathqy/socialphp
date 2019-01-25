<?php
/**
 * @file	根据数据描述数组构造mysql语句
 * @author	original bambo.w@gmail.com, modified by greathqy@gmail.com
 */
class MysqlBuilder
{
    const RAW_STR_PREFIX = '&/';
    const RAW_STR_NO_ESCAPE_PREFIX = '&/!';
    
    const LOGIC = '__logic';
    
	const RAW_STR_PREFIX_LENGTH = 2;
	const RAW_STR_NO_ESCAPE_PREFIX_LENGTH = 3;
    
    private function __construct() { //Prevent installation
    }
    
    /**
     * SQL 原始字符包装，如 CURRENT_TIMESTAMP, field + 1
     */
    static public function rawValue($val, $escapeIt = TRUE) {
        return ($escapeIt ? self::RAW_STR_PREFIX : self::RAW_STR_NO_ESCAPE_PREFIX) . $val;    
    }
    
	/**
	 * 构造sql
	 *
	 * @param String $op	操作
	 * @param Array	$data	数据
	 */
	static public function buildSql($op, $data, $attrs = array()) {
		$op = strtolower($op);
		if (in_array($op, array('select', 'selectone', 'count', 'delete'))) {
			$op = 'where';
		}
		if ($op == 'update') {
			$sql = self::update($data);
			if ($attrs) {
				$sql .= self::where($attrs);
			}

			return $sql;
		}

		return self::$op($data, $attrs);
	}

    /**
     * @example
     * 单行数据
     * insert(array(
     *   'key1' => 'val1',
     *   'key2' => '&/CURRENT_TIMESTAMP',
     * ));
     *
     * output : (`key1`,`key2`) VALUES ('val1',CURRENT_TIMESTAMP)
     *
     * 多行数据
     * insert(array(
     *    'key1', 'key2',
     * ), array(
     *      array('val11', 'val12'),
     *      array('val21', 'val22')
     * ));
     * output: (`key1`,`key2`) VALUES ('val11','val12'),('val21','val22')
     *
     * @param array
     */
    static public function insert($row, $rowsData = NULL) {
        if ($rowsData) {
            $keys = $row;
        } else {
            $keys = array_keys($row);
            $rowsData = array(array_values($row));
        }
        
        $keySql = '(' . implode(',', array_map(array(__CLASS__, '_escapeName'), $keys)) . ')';
        
        $valSqls = array();
        foreach ($rowsData as $data) {
            $valSqls[] =
            '(' . implode(',', array_map(array(__CLASS__, '_escapeValue'), $data)) . ')';
        }
        $valSql = implode(',', $valSqls);
        
        return " $keySql VALUES $valSql";
    }
    
    /**
     * @example
     * update(array(
     *   'key1' => 'value1',
     *   'key2' => '&/CURRENT_TIMESTAMP',
     * ));
     * output: " (`key1`,`key2`) VALUES ('value1',CURRENT_TIMESTAMP)"
     * 
     * @param array $data
     * @return string
     */
    static public function update($data) {
        $sql = '';
        foreach ($data as $name => $val) {
            $name = self::_escapeName($name);
            $val = self::_escapeValue($val);
            $sql .= "$name=$val,";
        }
        return ' SET ' . trim($sql, ',');
    }
    
    /**
     * @example
     * replace(array(
     *    'key1' => 'value1',
     *    'key2' => '&/CURRENT_TIMESTAMP',
     * ), array(
     *    'key1' => '&/key1 + 1'
     * ));
     *
     * output: " (`key1`,`key2`) VALUES ('value1',CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `key1`=key1 + 1"
     * 
     * @param array $insData  same as method insert parameter
     * @param array $resData  replace data
     * @return string
     */
    static public function replace($insData, $resData = NULL) {
        if ($resData === NULL) {
            $resData = $insData;
        }
        
        $sql = self::insert($insData);
        
        if (empty($resData)) {
            return $sql;
        }
        
        $sql .= ' ON DUPLICATE KEY UPDATE ';
        $sql .= preg_replace('@^\s*SET\s*@', '', self::update($resData));
        return $sql;
    }
    
    /**
     * @example
     *
     * example 1.
     * where(array(
     *   'key1' => 'value1',
     *   'key2' => NULL,
     *   'key3' => array('!=' => 'value3'),
     *   'key4' => array('value4_1', 'value4_2')
     * ));
     *
     * output : WHERE `key1`='value1' AND `key2` is NULL AND `key3` != 'value3' AND (`key4` = 'value4_1' OR `key4` = 'value4_2')
     *
     * example 2.
     * where(array(
     *    array('key1' => array('like' => '%value1%')),
     *    array(
     *          'key2' => 3,
     *          'key3' => 4,
     *    )
     * ), array(
     *   'order_by' => 'id DESC',
     *   'offset' => 10,
     *   'limit' => 20,
     * ));
     * 
     * output: WHERE (`key1` like '%value1%') OR (`key2`='3' AND `key3`='4') ORDER BY id DESC LIMIT 10, 20
     *
     * @param array $where  条件数组,默认是AND关系,数字索引数组(非关系数组)表示OR关系
     * @param array $attrs 可设置的值:order_by,group_by,limit,offset
     * @return string
     */
    static public function where($where, $attrs = array()) {
        $sql = '';
        if (!empty($where)) {
            $whereSql = self::_where($where);
            if ( $whereSql) {
                $sql .= ' WHERE ' . $whereSql;    
            }
        }
        if ($attrs) {
            if (isset($attrs['group_by'])) {
                $sql .= ' GROUP BY ' . $attrs['group_by'];
            }
            
            if (isset($attrs['order_by'])) {
                $sql .= ' ORDER BY ' . $attrs['order_by'];    
            }
            
            if (!empty($attrs['offset']) || !empty($attrs['limit'])) {
                $sql .= ' LIMIT ';
                if (isset($attrs['offset'])) {
                    $sql .= $attrs['offset'] . ',';
                }
                
                if (isset($attrs['limit'])) {
                    $sql .= $attrs['limit'];
                }
            }
        }
        
        return $sql;
    }
    
    static private function _where($where) {
        if (empty($where) || ! is_array($where)) {
            return '';
        }
        
        $logic = '';
        
        if (isset($where[self::LOGIC])) {
            $logic = $where[self::LOGIC];
            unset($where[self::LOGIC]);
        }
        
        $isArray = self::_isArray($where);
        if ($isArray) {	//数字键用or, 字符串键用and 连接
            $conds = array_map(array(__CLASS__, '_where'), $where);
            $conds = array_map(array(__CLASS__, '_wrapWithBrackets'), array_filter($conds));
            if ( ! $logic) {
                $logic = 'OR';
            }
            $sql = implode(" $logic ", $conds);
            return $sql;
        }
        
        $conds = array();
        foreach ($where as $key => $val) {
            $conds[] = self::_cond($key, $val);
        }
        if ( ! $logic) {
            $logic = 'AND';
        }
        $sql = implode(" $logic ", array_filter($conds));
        return $sql;
    }
    
    static private function _cond($name, $val, $inIteration = FALSE) {
        if ( ! $inIteration) {
            $name = self::_escapeName($name);    
        }
        
        if ( ! is_array($val)) {
            $val = self::_escapeValue($val);
            if ($val === 'NULL') {
                return "$name is NULL";
            }
            return "$name=$val";
        }
        
        $logic = 'OR';
        if (isset($val[self::LOGIC])) {
            $logic = 'AND';
            unset($val[self::LOGIC]);
        }
        
        if (self::_isHash($val)) {
            if (count($val) == 1) {
                $operation = array_pop(array_keys($val));
                $val = $this->_escapeValue($val[$operation]);
                return "{$name} {$operation} {$val}";    
            } else {
                $newVal = array();
                foreach ($val as $iKey => $iVal) {
                    $newVal[] = array($iKey => $iVal);
                }
                $val = $newVal;
            }
        }
        
        $conds = array();
        foreach ($val as $condVal) {
            if (self::_isArray($condVal)) {
                //array('val1', 'val2', ...)
                $conds[] = $this->_cond($name, $condVal, TRUE);
                continue;
            } else if (self::_isHash($condVal)) {
                //array('!=' => 'val')
                $operation = array_pop(array_keys($condVal));
                $condVal = $condVal[$operation];
            } else {
                $operation = '=';
            }
            $condVal = $this->_escapeValue($condVal);
            $conds[] = "{$name} {$operation} {$condVal}";
        }
        
        if (empty($conds)) {
            return "$name = ''";
        }
        
        return '(' . implode(" $logic ", $conds) . ')';
    }
    
    static private function _wrapWithBrackets($str) {
        return '('.$str.')';    
    }
    
    //是不是纯数字索引
    static private function _isArray($val) {
        if (!is_array($val)) {
            return FALSE;
        }
        $keys = array_keys($val);
        foreach ($keys as $key) {
            if ( ! is_numeric($key)) {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    static private function _isHash($val) {
        return is_array($val) && !self::_isArray($val);
    }
    
    static private function _escapeValue($str) {
        if ($str === NULL) {
            return 'NULL';
        }
        if (strpos($str, self::RAW_STR_NO_ESCAPE_PREFIX) === 0) {
            return substr($str, self::RAW_STR_NO_ESCAPE_PREFIX_LENGTH);
        }
        if (strpos($str, self::RAW_STR_PREFIX) === 0) {
            return self::escape(substr($str, self::RAW_STR_PREFIX_LENGTH));
        }
        return self::escape($str, TRUE);
    }
    
    static private function _escapeName($str) {
        $str = self::escape($str);
        return "`$str`";
    }
    
    static public function escape($str, $addQuote = FALSE) {
        $str = mysql_escape_string($str);
        if (! $addQuote) {
            return $str;
        }
        return "'" . $str . "'";
    }
}
