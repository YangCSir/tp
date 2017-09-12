<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/13
 * Time: 16:04
 */

namespace app\index\model;


use think\Model;

class Promulgator extends Model
{
    public static function checkIsPromulgator($uid)
    {
        $status = self::where('uid', $uid)->value('status');
        if (!empty($status)) {
            if ($status == 1) return true;
        }
        return false;
    }
}