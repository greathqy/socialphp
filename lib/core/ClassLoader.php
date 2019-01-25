<?php
/**
 * @author greathqy@gmail.com
 * @file php autoloader 需要一个类名到文件路径的映射关系
 */
/**
 * 按照数组映射关系加载类文件
 *
 * @param String $className 要加载的类名
 */
class ClassLoader
{
    private function __construct() {
    }

    static private $classFileMapping = array();

    /**
     * 设置类名，文件映射关系
     *
     * @param Array $classFileMapping
     */
    static public function setFileMapping(& $classFileMapping) {
        if (!is_array($classFileMapping)) {
            throw new Exception("include class mapping config file failed.");
        }
        self::$classFileMapping =& $classFileMapping;
        
        return TRUE;
    }

    static public function mappingLoader($className) {
        static $loads = array();
        $mapping =& self::$classFileMapping;
        $className = strtolower($className);
        $ret = FALSE;

        if (isset($loads[$className])) {
            $ret = TRUE;
        } else {
            if (isset($mapping[$className])) {
                $classFile = $mapping[$className];
                if (!in_array($classFile, get_included_files())) {
                    include($classFile);
                }
                $ret = TRUE;
            } else {
                $ret = FALSE;
            }
        }

        return $ret;
    }
}
