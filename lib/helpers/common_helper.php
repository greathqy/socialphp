<?php
/**
 * 生成链接
 *
 * @param String    $module 模块名
 * @param String    $action 方法名
 * @param Array     $args   附加参数
 * @return String
 */
function linkto($module, $action, $type='html', $args = array()) {
    $sep = '&amp;';
    if ($type == 'html') {
        $sep = '&';
    }
    $base = '/index.php?m=' . $module . $sep . 'a=' . $action;
    if (is_array($args) && $args) {
        foreach ($args as $field => $value) {
            $base .= $sep . $field . '=' . $value;
        }
    }
	$prev = getcurlink($type, TRUE);
	$prev = urlencode($prev);
	$base .= $sep . '_prev' . '=' . $prev;
	$base .= $sep . '_rnd' . '=' . microtime(TRUE);
    
    return $base;
}

//生成wml链接
function linkwml($module, $action, $args = array()) {
    return linkto($module, $action, 'wml', $args);
}

//生成html链接
function linkhtml($module, $action, $args = array()) {
    return linkto($module, $action, 'html', $args);
}

//获得当前链接 filtered out _prev and _rnd
function getcurlink($type = 'wml') {
	$sep = '&amp;';
	if ($type == 'html') {
		$sep = '&';
	}
	
	$cur = $_SERVER['QUERY_STRING'];
	if ($sep == '&amp;') {
		$cur = str_replace('&amp;', '&', $cur);
	}
	if ($cur) {
		parse_str($cur, $arrCur);
	} else {
		$arrCur = array();
	}

	foreach ($arrCur as $key => $part) {
		if ($key == '_prev' || $key == '_rnd') {
			unset($arrCur[$key]);
		}
	}
	$query = '';
	foreach ($arrCur as $key => $part) {
		$query .= $key . '=' . $part . $sep;
	}
	$query = rtrim($query, $sep);

	$url = '/index.php?' . $query;

	if ($sep == '&amp;'); {
		$url = str_replace('amp;', '', $url);
		$url = str_replace('&', '&amp;', $url);
	}

	return $url;
}

/**
 * 按照配置概率，获得该出现的玩意
 */
function getRandSelection($arrConf) {
	$r = mt_rand(1, 100);
	foreach ($arrConf as $prob => $conf) {
		if ($r < $prob) {
			return array($prob, $conf);
			break;
		}
	}
	return FALSE;
}

/**
 * 按照指定的myIndex, 选择一个符合的配置
 */
function getSelection($arrConf, $myIndex) {
	foreach ($arrConf as $index => $conf) {
		if ($myIndex <= $index) {
			return array($index, $conf);
			break;
		}
	}

	return FALSE;
}

/**
 * 是否是纯粹数组
 */
function isArray($arr) {
	if (!is_array($arr)) return FALSE;

	$keys = array_keys($arr);
	foreach ($keys as $key) {
		if (!is_numeric($key))
			return FALSE;
	}

	return TRUE;
}

/**
 * 是否是哈希数组
 */
function isHash($arr) {
	return is_array($arr) && !isArray($arr);
}

/**
 * 判断是否是真值
 */
function isTrue($arr, $field) {
	$ret = FALSE;
	if (isset($arr[$field]) && $arr[$field]) {
		$ret = TRUE;
	}
	return $ret;
}

/**
 * 获得间隔时间描述
 *
 * @param Integer	$start	开始的unix时间戳
 * @param Integer	$end	结束的unix时间戳
 * @param Boolean	$textual	是否返回文本描述
 * @return Array	array('day' => xx, 'hour' => xx, 'min' => xx)
 */
function getTimeSpan($start, $end, $textual = TRUE) {
	$ret = array(
		'day' => 0,
		'hour' => 0,
		'min' => 0,
		);
	if ($end > $start) {
		$total = $end - $start;
		$day = 3600 * 24;
		$hour = 3600;
		$min = 60;

		$days = floor($total / $day);
		$total = $total % $day;
		$hours = floor($total / $hour);
		$total = $total % $hour;
		$mins = floor($total / $min);

		$ret = array('day' => $days, 'hour' => $hours, 'min' => $mins);
	}
	if (!$textual) {
		return $ret;
	}
	$desc = '';
	if ($ret['day']) {
		$desc .= $ret['day'] . ' 天 ';
	}
	if ($ret['hour']) {
		$desc .= $ret['hour'] . ' 小时 ';
	}
	if ($ret['min']) {
		$desc .= $ret['min'] . ' 分 ';
	}

	return $desc;
}

/**
 * 获得明星类型的文字描述
 */
function getStarTypeTextual($type) {
	static $starTypes = NULL;
	if (!$starTypes) {
		$starTypes = Configurator::get('app.frontend.star_types');
		//Debug::dump($starTypes);
	}
	$text = '未知类型';
	if (isset($starTypes[$type])) {
		$text = $starTypes[$type];
	}

	return $text;
}

/**
 * 获得性别他她描述
 *
 * @param String	$sex	性别
 * @return String
 */
function getSexTextual($sex) {	
	$sex = getSexTypeTextual($sex);
	$str = '未知性别';
	$map = array(
		'男' => '他',
		'女' => '她',
		'不限' => '不限',
		);
	if (isset($map[$sex])) {
		$str = $map[$sex];
	}

	return $str;
}

/**
 * 获得性别男女描述
 *
 * @param Integer	$sex	性别
 * @return String
 */
function getSexTypeTextual($sex) {
	$sexes = Configurator::get('app.frontend.sexes');
	$str = '未知性别';
	if (isset($sexes[$sex])) {
		$str = $sexes[$sex];
	}

	return $str;
}

/**
 * 字符串长度截取
 * @param $str 		要进行截取的字符串
 * @param $start 	要进行截取的开始位置，负数为反向截取
 * @param $end 		要进行截取的长度
 * @return String
*/
function strLimit($str,$start=0) {
	if(empty($str)){
		return false;
	}
	if (function_exists('mb_substr')){
		if(func_num_args() >= 3) {
		    $end = func_get_arg(2);
		    return mb_substr($str,$start,$end,'utf-8');
		}
		else {
			mb_internal_encoding("UTF-8");
			return mb_substr($str,$start);
		}		

	}
	else {
		$null = "";
		preg_match_all("/./u", $str, $ar);
		if(func_num_args() >= 3) {
		    $end = func_get_arg(2);
		    return join($null, array_slice($ar[0],$start,$end));
		}
		else {
		    return join($null, array_slice($ar[0],$start));
		}
	}
}

/**
 * 根据条件生成标识
 * @param $str 		字符串参数
 * @param $limit 	生成长度
 * @return String
*/
function create_hash($str,$limit = 0){
	if(is_array($str)){
		$str = implode(',',$str);
	}
	$hash = md5($str);
	return $limit > 0 ? substr($hash,0, $limit) : $hash;
}
