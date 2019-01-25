<?php
/**
 * @param String    $helperName 助手文件名
 * @return Boolean
 */
function load_helper($helperName) {
    $fileName = $helperName . '_helper.php';
    $filePath = SYS_ROOT . DS . 'lib' . DS . 'helpers' . DS . $fileName;
    if (file_exists($filePath)) {
        if (!in_array($filePath, get_included_files())) {
            return include($filePath);
        }
    }

    return FALSE;
}

/**
 * 判断传入的数字是否为>=$lim的整数
 */
function __is_num_b(&$num = 0,$lim = 0)
{
	return is_numeric($num) && ($num = intval($num)) >= $lim;
}

/**
 * 判断传入的数字是否为<=$lim的整数
 */
function __is_num_s(&$num = 0,$lim = 0)
{
	return is_numeric($num) && ($num = intval($num)) <= $lim;
}

/**
 * 判断传入数字是否为自然数
 */
function __is_big_num($bignum = 0)
{
	//判断是否是数字
 	if(!preg_match("/^[0-9]+$/i", $bignum) || !$bignum)
 	{
 		return false;
 	}
	else return true;
}

/**
 * 发送UPD日志
 * @param	int		uid		用户id
 * @param	string	type	表,(gb, prop, mb, user_exp, store_exp)
 * @param	array	msg		消息内容
 */

function _send_udp_log($uid, $type, $msg, $server=1)
{
	global $__core_env;
	
	if(!function_exists("__socket_config_udp_log")) {
		return false;
	}
	if($_SERVER['SERVER_ADDR']) {
		$clientIp = $_SERVER['SERVER_ADDR'];
		$ip_msg = $clientIp.":";
	}else {
		$ip_msg = "";
	}
	$server_config = __socket_config_udp_log($server);
	if(!$server_config) {
		return false;
	}
	shuffle($server_config);
	$host = $server_config[0]['host'];
	$port = $server_config[0]['port'];
	if(!$host || !$port) {
		return false;
	}

	switch($type) {
		case "gb":
					$tbType = 1;
					break;
		case "mb":
					$tbType = 3;
					break;
		case "user_exp":
					$tbType = 4;
					break;
		case "store_exp":
					$tbType = 5;
					break;
		case "tb_user":
					$tbType = 6;
					break;
		case "tb_ad":
					$tbType = 7;
					break;
		case "login":
					$tbType = 8;
					break;
		case "adv":
					$tbType = 9;
					break;
		case "act":
					$tbType = 10;
					break;
		case "inv":
					$tbType = 11;
					break;
		case "syslog":
					$tbType = 12;
					break;
		case "mc_conn_1":
					$tbType = 13;
					break;
		case "mc_conn_2":
					$tbType = 14;
					break;
		case "tt_stat":
					$tbType = 15;
					$msg = $ip_msg.$msg;
					break;
		case "db_stat":
					$tbType = 16;
					$msg = $ip_msg.$msg;
					break;
		case "sns_stat":
					$tbType = 17;
					break;
		case "error_tt":
					$tbType = 18;
					$msg = $ip_msg.$msg;
					break;
		case "login_mob":
					$tbType = 19;
					break;
		//iphone
		case "login_ip":
					$tbType = 20;
					break;
		case "repair_prop":
					$tbType = 21;
					break;
		case "repair_gb":
					$tbType = 22;
					break;
		case "guest_error":
					$tbType = 23;
					break;
		case "report":
					$tbType = 24;
					break;
		case "asyn_err":
					$tbType = 25;
					$msg = $ip_msg.$msg;
					break;
		case "monitor":
					$tbType = 26;
					break;
		case "act_tmp":
					$tbType = 27;
					break;
		case "error_msg":
					$tbType = 28;
					break;
		case "error_exp":
					$tbType = 29;
					break;
		case "act_list":
					$tbType = 30;
					break;
		/*case "tb_user":
					$tbType = 6;
					break;*/
		default:
					return false;
					break;
	}
	$notifyWriteLog= new CCSNotifyWriteLog();
	$notifyWriteLog->m_nUid = $uid;
	$notifyWriteLog->m_shTableType = $tbType;
	$notifyWriteLog->m_strLog = $msg;
	// 1|user,321,gb,add,123		// 增加gb，要归档
	// 0|user,321,mb,sub,123		// 减mb，不归档
	// 1|user,321,prop,123,add,3		// 道具ID123，增加3个，要归档
	// 0|floor,321,exp,add,321				// 不归档
	$notifyWriteLog->encode($body, $bodyLength);

	$cshead = new CCSHead();
	$cshead->nPackageLength = $bodyLength + $cshead->size();
	$cshead->nUID = $uid;
	$cshead->shFlag = 0;
	$cshead->shOptionalLen = 0;
	$cshead->lpbyOptional = "";
	$cshead->shHeaderLen = $cshead->size();
	$cshead->shMessageID = 0x0010;
	$cshead->shMessageType = 0x03;
	$cshead->shVersionType = 0x03;
	$cshead->shVersion  = 0;
	$cshead->nPlayerID  = -1;
	$cshead->nSequence  = 0;

	$cshead->encode($head, $headLength);

	$sockfd=socket_create(AF_INET,SOCK_DGRAM,0);
	if(!$sockfd) {
		return false;
	}
	$res = socket_sendto($sockfd,$head.$body,$headLength+$bodyLength+1,0,$host,$port);
	return $res;
}

/**
 * 腾讯平台检测用户是否已经被封
 */
function __is_forbid_user($uid = 0)
{
	$arr = array();
	#此处320090611是固定值。
    $ret = php_sac_api_init(320090611, $arr);
    if($ret==0)
    {
		$uiClientIp = '';   
		if (isset($_SERVER)){
	        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
	            $uiClientIp = $_SERVER["HTTP_X_FORWARDED_FOR"];
	        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
	            $uiClientIp = $_SERVER["HTTP_CLIENT_IP"];
	        } else {
	            $uiClientIp = $_SERVER["REMOTE_ADDR"];
	        }
	    } else {
	        if (getenv("HTTP_X_FORWARDED_FOR")){
	            $uiClientIp = getenv("HTTP_X_FORWARDED_FOR");
	        } else if (getenv("HTTP_CLIENT_IP")) {
	            $uiClientIp = getenv("HTTP_CLIENT_IP");
	        } else {
	            $uiClientIp = getenv("REMOTE_ADDR");
	        }
	    }
	    $uiClientIp = ip2long($uiClientIp);
	    # php_sac_api_check的参数为
		#   1. 客户端IP 123123
		#   2. 发起请求的uid 9
		#   3. 被请求的uid 5
		#   4. 最后的2表示只校验发起请求的uid，固定值
        $ret 		= php_sac_api_check($uiClientIp, $uid, $uid, $arr, 2);
        if($ret==0)
        {
            //echo $arr['result']."-".$arr['level']."-".$arr['endtime']."-".$arr['errmsg']."\n";
			#-----------------------------------------------------------------#
			#                        注意!!!!!!                               #
			#此处如果$arr['result']为4，就说明该用户被封锁了，否则可以继续操作#
			#-----------------------------------------------------------------#

			if($arr['result'] == '4')
            {
                 return true;
            }else {
                 return false;
            }
        } else {
            return false;
        }
    }else {
        return false;
    }
}

function hearder_cache($maxAge=43200,$expire=43200)
{
	$gmt = gmmktime();
	header("Cache-Control: max-age=".$maxAge);
	header("Pragma: cache");
	header("Expires: ". gmdate("D, d M Y H:i:s", $gmt + $expire) . " GMT");
}

/**
 * 二进制打包
 */
function _kingnet_pack($format, &$text)
{
	if($format === false) {
		return $text;
	}else if(!$format) {
		return false;
	}
	if ($format == 'array') {
		return json_encode($text);
	}
	if($format == "array" && is_array($text)) {
		return json_encode($text);
	}else {
		if($format == "L" || $format == "N" || $format == "V") {
			if(is_numeric($text) && $text >= 4294967296) {
				return false;
			}
		}else if($format == "l") {
			if(intval($text) >= 2147483648) {
				return false;
			}
		}
		return pack($format, $text);
	}
}

/**
 *
 */
function _kingnet_unpack($format, &$binary)
{
	if(!$format) {
		return $binary;
	}

	if($format == "array") {
		return json_decode($binary, true);
	}else {
		$text = unpack($format, $binary);
		return $text[1];
	}
}

/**
 * 模调接口
 * @param	p_mod_id	integer		模调id
 * @param	c_mod_id	integer		子模调id
 * @param	p_file_name	string		模调文件名
 * @param	p_file_line	integer		模调行号
 * @param	p_file_create_time		文件创建时间
 * @param	opt_type				操作类型	C U D Q
 * @param	is_ret_suc				操作成功还是失败 true|false
 * @param	use_time				整个调用使用的时间
 * @reutrn	void
 * #$msglog->msgprintf( 2030000000, "%d%d%d%d%d%d%s%s%s%d%d%d", "主调模块id[int]", "被调模块接口id[int]", "被调模块接口ID[int]", "主调模块IP[选][int]", "被调模块IP[选][int]","主调端口【选】[int]","被调端口【选】[int]","主调模块文件名【选】[string]","主调模块行号【选】[int]","主调模块文件创建时间【选】[string]","操作类型(insert/update/delete/select)[选][string]","返回值(这个类型比较多)【选】[int]","调用结果【必填】0成功 1失败[int]","响应时间[int]" );
 */
function __tt_module_report($p_mod_id,$c_mod_id,$c_api_id,$is_ret_suc,$use_time,$opt_type = '',$p_file_name = '',$p_file_line = 0,$p_file_create_time = '')
{
	//return false;
	//require_once( "tphplib.inc.php" );
	$ip  	  = ip2long($_SERVER['SERVER_ADDR']);
	$res 	  = $is_ret_suc ? 0 : 1;
	$use_time = intval($use_time * 1000);
	$msglog   = new tmsglog_z(3,"LOUYIDONG");
	$ret 	  = $msglog->msgprintf(3, 2025000013, time(), "%d%d%d%d%d%d%d%s%d%s%s%d%d%d%d%d%d%d%d%s%s%s%s%s", $p_mod_id , $c_mod_id, $c_api_id, $ip, $ip,0,0,$p_file_name,$p_file_line,$p_file_create_time,$opt_type,0,$res,$use_time,0,0,0,0,0,"","","","","");
	if($ret !== 0)
	{
		$c_date = date('Y-m-d H:i:s');
		//error_log($c_date."\t".$p_mod_id."\t".$ret."\n",3,'/data/tt_module_report.log'); 
	} else {
		//error_log($p_mod_id."\t".$c_mod_id."\t".$c_api_id."\t".$ip."\t".$ip."\t0\t0\t".$p_file_name."\t".$p_file_line."\t".$p_file_create_time."\t".$opt_type."\t0\t".$res."\t".$use_time."\t".$_SERVER['SERVER_ADDR']."\n",3,'/data/log/tt_module_report.log');
	} 
}

/**
 * 摩天大楼应用 腾讯消息上报接口
 * @param	iCmd		integer		命令字，大楼操作码
 * @param	master_uid	integer		被操作用户id，或是店铺主人
 * @param	op_uid		integer		操作用户uid
 * @param	op_oid		integer		操作用户openid
 * @param 	from		integer		来源2:校友 1:空间
 * @param 	gb_num		integer		变化的gb数
 * @param	exp_num		integer		变化的经验
 * @param	iState		integer		验证码是否通过
 * @return 	void
 */
function __tt_msg_report($iCmd = 0,$master_id = 0,$op_uid = 0,$from = 0,$gb_num = 0,$exp_num = 0,$iState = 0)
{
	//return false;
	$op_oid = snscall("getOpenID",array());
	if(!$op_oid || !$op_uid) return;
	$from 	= 2;
	$iAppID = QQXIAOYOU_APP_ID;
	if(_SNS_PLATFORM  === 'qzone' 	  && !defined('QZONE_APP_ID')) 		return;
	if(_SNS_PLATFORM  === 'qqxiaoyou' && !defined('QQXIAOYOU_APP_ID')) 	return;
	
	if(_SNS_PLATFORM === 'qqxiaoyou')
	{
		$from 	= 2;
		$iAppID = QQXIAOYOU_APP_ID;
	} elseif(_SNS_PLATFORM === 'qzone') {
		$from 	= 1;
		$iAppID = QZONE_APP_ID;
	} else {
		return;
	}
	
	$b 	= new tmsglog_z(3,"sendlog");
	$uiClientIp = '';   
	if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $uiClientIp = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $uiClientIp = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $uiClientIp = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $uiClientIp = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $uiClientIp = getenv("HTTP_CLIENT_IP");
        } else {
            $uiClientIp = getenv("REMOTE_ADDR");
        }
    }
	$msgid		= 1000000002;
	$iVersion 	= 1;
	$iSource	= $from;
	$uiOperTime = time();
	$sessionKey = isset($_GET['openkey']) ? $_GET['openkey'] : '';
	$uiMoney	= $gb_num;
	$uiExpr		= $exp_num;
	$uiClientIp = ip2long($uiClientIp);
	$uiRequestUin  = $op_uid;
	$uiAcceptUin   = $master_id;
	$Requestopenid = $op_oid;
	if(!defined("_DETECTION_RABOT_KEY_") || !_DETECTION_RABOT_KEY_ || (!$_GET['c'] && !$_GET['a']) || $iCmd == 100) {
		$iState = 0;
	}else {
		if($_REQUEST['b'] && $_REQUEST['b'] === _DETECTION_RABOT_KEY_) {
			$iState = 128;
		}else {
			$iState = 384;
		}
	}
	if($iState == 384) {
		$rd = rand(1, 100);
		if($rd <= 2) {
			$log_msg = "c=".$_GET['c']."&a=".$_GET['a']."&b=".$_REQUEST['b']."\n";
			error_log($log_msg, 3, "/data/error_log/msg_report2.log");
		}
	}
	$ret = $b->netprintf( $msgid, $uiRequestUin, "%d%d%d%d%d%d%d%s%d%d%d%d%s", $iAppID,$iVersion,$iSource,$iCmd,$iState,$uiRequestUin,$uiAcceptUin,$Requestopenid,$uiClientIp,$uiOperTime,$uiMoney,$uiExpr,$sessionKey);
	$tmp_msg = "{$uiRequestUin}\t{$iAppID}\t{$iVersion}\t{$iSource}\t{$iCmd}\t{$iState}\t{$uiRequestUin}\t{$uiAcceptUin}\t{$Requestopenid}\t{$uiClientIp}\t{$uiOperTime}\t{$uiMoney}\t{$uiExpr}\t{$sessionKey}\n";
	if($ret !== 0)
	{       
			$c_date = date('Y-m-d H:i:s');
			error_log($c_date."\t".$iCmd."\t".$ret."\n",3,'/data/tt_msg_report.log'); 
	}
}

function __record_user_event($uid = 0,$mod = "",$act = "",$add_num = 0)
{
	if(!$uid || !$mod || !$act || !$add_num)
		return;
	$str = $uid.":".$mod."->".$act."\t".$add_num."\n";
	error_log($str,3,'/tmp/trace_user.log');
}
