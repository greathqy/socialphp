<?php
/**
 * @author greathqy@gmail.com
 * @file   验证类, 按照module里定义的dtds格式进行数据合法性验证
 */
class Validator
{
    static private $dataSource = NULL;

    /**
     * 设置数据源对象
     *
     * @param Object $dataSource
     * @return Boolean
     */
    static public function setDataSource($dataSource) {
        self::$dataSource = $dataSource;

        return TRUE;
    }

    /**
     * 验证所有规则
     *
     * @param Array $dtds   规则描述
     * @return Array
     */
    static public function validate($dtds) {
        $errors = array();
        foreach ($dtds as $field => $conf) {
            if (!isset($conf['value'])) {
                $func = 'post';
                $value = self::$dataSource->$func($field);
            } else {
                if ($conf['value'][0] == '@') {
                    $func = str_replace('@', '', $conf['value']);
                    $value = self::$dataSource->$func($field);
                } else {
                    $value = $conf['value'];
                }
            }

            foreach($conf['rule'] as $rule) {
                $ret = self::validateSingleRule($rule, $value); //array('required' => 'reason')
                if ($ret) { //发现了一个错误，没必要尝试其他规则了
                    $ruleName = key($ret);
                    $ruleErr = current($ret);
                    $errors[$field] = array('rule' => $ruleName, 'text' => $ruleErr);
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * 验证单个规则
     *
     * @param Array $rule   字段规则
     * @param Mixed $value  字段值
     * @return Array
     */
    static public function validateSingleRule($rule, $value) { //$rule
        $arrRule = explode("\t", $rule);
        $validator = $arrRule[0];
        $arrValidator = explode(':', $validator);
        $validator = $arrValidator[0];
        $errDesc = '';
        if (isset($arrRule[1])) {
            $errDesc = $arrRule[1];
        }
        $args = array($value);
        array_shift($arrValidator);
        if ($arrValidator) {
            $appendArg = join(':', $arrValidator);
            $args[] = $appendArg;
        }

        $msg = array();
        $ret = call_user_func_array(array(__CLASS__, $validator), $args);
        if (!$ret) { //got error
            $msg[$validator] = $errDesc;
        }

        return $msg;
    }

    /**
     * 不得为空
     *
     * @param String    $value  字段值
     * @return Boolean
     */
    static private function required($value) {
        if ($value) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 最大长度限制
     *
     * @param String    $value  字段值
     * @param Integer   $limit  最大长度
     * @return Boolean
     */
    static private function maxlength($value, $limit) {
        return (mb_strlen($value) <= $limit);
    }

    /**
     * 最小长度限制
     *
     * @param String    $value  字段值
     * @param Integer   $limit  最小长度
     */
    static private function minlength($value, $limit) {
        return (mb_strlen($value) >= $limit);
    }
}
