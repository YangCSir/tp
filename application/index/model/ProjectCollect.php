<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/17
 * Time: 14:41
 */

namespace app\index\model;


use think\Model;

class ProjectCollect extends Model
{
    public function checkCollect($value)
    {
        if (empty($value['uid'])) return 2;
        $data = $this->where(['pid' => $value['project_id'], 'uid' => $value['uid'], 'status' => 1])->value('psid');
        if ($data) return 1;
        return 2;
    }

    public function collectProject($value)
    {
        $data = $this->where($value)->find();
        if ($data) {
            if ($data['status'] == 1) {
                $arr = $this->where($value)->update(['status' => 2]);
            } else {
                $arr = $this->where($value)->update(['status' => 1]);
            }
        } else {

            $value['add_time'] = time();
            $arr = $this->insert($value);
        }
        if ($arr) return true;
        return false;
    }
}