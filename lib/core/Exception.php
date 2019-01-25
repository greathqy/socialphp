<?php
/**
 * @file	各类异常定义
 * @author	greathqy@gmail.com
 */
//货币不足
class noMoneyException extends Exception {}

//位置不足
class noSpaceException extends Exception {}

//没达到条件异常
class notMeetException extends Exception {}

//非法动作序列异常
class invalidSeqException extends Exception {}

//没找到异常 用户缺少某东西 之类错误
class notFoundException extends Exception {}
