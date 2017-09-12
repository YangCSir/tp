<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/14
 * Time: 18:20
 */

namespace app\index\model;


use think\Model;

class Browse extends Model
{
    public function getBrowseNumByPid($project_id)
    {
        $num = $this->where('project_id', $project_id)->count();
        return $num;
    }

    public function addBrowseNum($value)
    {
        $arr = $this->where($value)->value('bid');
        if (!$arr) {
            $value['add_time'] = time();
            $this->insert($value);
        }
    }

    public function addBrowseNumInvestor($value)
    {
        $arr = $this->where('uid', $value['uid'])->where('project_id', $value['investor_id'])->where('type', 2)->value('bid');
        if (empty($arr)) {
            $value['type'] = 2;
            $value['project_id'] = $value['investor_id'];
            $value['add_time'] = time();
            unset($value['investor_id']);
            if ($this->insert($value)) return true;
            return false;
        } else {
            return true;
        }
    }
}