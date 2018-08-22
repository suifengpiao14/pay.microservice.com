<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:43
 */
namespace App\Common;
class Helpers{

    /**
     *
     * 替换array 的键值
     * @param $array
     * @param $keyMap
     * @return array
     */
    public static function RecordMapKey($array,$keyMap){
        $output=[];
        foreach($array as $key=>$value){
            $output[$keyMap[$key]]=$value;
        }
        return $output;
    }
}
