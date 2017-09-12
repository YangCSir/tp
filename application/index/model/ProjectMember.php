<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/14
 * Time: 11:20
 */

namespace app\index\model;


use think\Model;

class ProjectMember extends Model
{
    public function addProjectMember($value)
    {
//        $value['head_img'] = uploadImg($value['head_img']);
        $projectMember_model = new ProjectMember($value);
        if ($projectMember_model->allowField(true)->save()) return $projectMember_model->mid;
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    public function getProjectMember($member_id)
    {
        $member = substr($member_id, 0, strlen($member_id) - 1);
        $data = collection(ProjectMember::all($member))->toArray();
        $memberWork_model = new MemberWork();
        $arr = [];
        foreach ($data as $v) {
            if (!empty($v['work'])) {
                $v['work'] = $memberWork_model->getMemberWork($v['work']);
            } else {
                $v['work'] = [];
            }
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
            $arr[] = $v;
        }
        return _unsetNull(collection($arr)->toArray());
    }
}