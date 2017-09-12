<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/14
 * Time: 11:16
 */

namespace app\index\model;


use think\Model;

class MemberWork extends Model
{
    public function addMemberWork($value)
    {
        $memberWork_model = new MemberWork($value);
        if ($memberWork_model->save()) return $memberWork_model->wid;
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    public function getMemberWork($wid)
    {
        $work_id = substr($wid, 0, strlen($wid) - 1);
        $data = MemberWork::all($work_id);
        if (empty($data)) return [];
        $arr=[];
        foreach ($data as $v){
            if (strtotime($v['start_time'])) {
                $v['start_time'] = date("Y.m", strtotime($v['start_time']));
            } else {
                $v['start_time'] = "0000.00";
            }
            if (strtotime($v['end_time'])) {
                $v['end_time'] = date("Y.m", strtotime($v['end_time']));
            } else {
                $v['end_time'] = "0000.00";
            }
            $arr[]=$v;
        }
        $arr = my_sort(collection($arr)->toArray(), SORT_DESC);
        return $arr;
    }
}