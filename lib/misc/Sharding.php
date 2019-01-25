<?php
/**
 * @author greathqy@gmail.com
 * @file   各种shard策略
 */
class Sharding
{
    private function __construct() {
    }

    //Begin of 功能函数
    static public function algoMd5Hex($shardId) {
        $hash = 'mc_' . $shardId;
        $hash = md5($hash);
        $sub = hexdec(substr($hash, 0, 1));
        return $sub;
    }
    //End of 功能函数


    //Begin of 分区函数
    /**
     * 1分区
     */
    static public function byOne($shardId) {
        return 1;
    }
    
    /**
     * 按照md5后首字母对半分区
     */
    static public function byMd5HexHalf($shardId) {
        $num = self::algoMd5Hex($shardId);
        if ($num < 8) {
            $shard = 1;
        } else {
            $shard = 2;
        }

        return $shard;
    }
    //End of 分区函数
}
