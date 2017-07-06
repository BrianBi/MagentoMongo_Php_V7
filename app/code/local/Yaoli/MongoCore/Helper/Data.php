<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/5
 * Time: 15:54
 */

class Yaoli_MongoCore_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $stdclassobject
     * @return mixed
     */
    public function objectToArray($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        foreach ($_array as $key => $value)
        {
            $value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            $array[$key] = $value;
        }

        return $array;
    }
}