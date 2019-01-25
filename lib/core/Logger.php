<?php
/**
 * @author huangqingyun
 * @file   同步日志和异步日志
 */
class AsyncLogger
{
    static private $logs = array();

    //记录日志到内存数组
    static public function log() {
    }

    //写出日志
    static public function flush() {
    }
}

class SyncLogger
{
    static public function log() {
    }

    static public function flush() {
        return TRUE;
    }
}
