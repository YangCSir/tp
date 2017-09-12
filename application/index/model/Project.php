<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/14
 * Time: 10:42
 */

namespace app\index\model;


use think\Model;

class Project extends Model
{
    public function getProjectList($uid, $p = 1)
    {
//        dump($p);die;
        $data = Project::where('status', '>', '1')->where('uid', $uid)->where('pro_id', 0)->page($p, config('page'))->field('pid,logo,status,name,ptype')->select()->toArray();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        $arr = [];
        foreach ($data as $v) {
            if ($v['status'] == 3) {
                $pro_id = Project::where('pro_id', $v['pid'])->value('pid');
                $v['pro_id'] = $pro_id ? $pro_id : 0;
            }
            $arr[] = $v;
        }
        return $this->projectData($arr);
    }

    public function getHomeProjectList($value)
    {
        $order = [];
        $where = ['pro_id' => 0, 'status' => 3];
        if (!empty($value['time'])) {
            switch ($value['time']) {
                case 1:
                    $order['add_time'] = 'desc';
                    break;
                case 2:
                    $order['money'] = 'desc';
                    break;
                case 3:
                    $order['money'] = 'asc';
                    break;
                default:
                    return;
                    break;
            }
        } else {
            $order['pid'] = 'desc';
        }
        if (!empty($value['status'])) {
            $where['ptype'] = $value['status'];
        }
        if (!empty($value['industry'])) {
            $where[] = ['exp', "FIND_IN_SET($value[industry],best_pcid)"];
        }
        if (!empty($value['keyword'])) {
            $where['name'] = array('like', '%' . $value['keyword'] . '%');
        }
        if (!empty($value['address'])) {
            $where[] = ['exp', "FIND_IN_SET($value[address],best_address)"];
        }
        $data = $this->where($where)->where('uid', '<>', $value['uid'])->order($order)->field('pid,name,address,pcid,company_name,img_list,money,ptype')->page($value['p'], config('page'))->select();

        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $this->projectDataMore($data);
    }

    public function getProjectDetail($value)
    {
        $data = $this->where('pid', $value['project_id'])->find()->toArray();
        if (empty($data)) exit(\Response::json(FAIL, '该项目不存在'));
        $data['pcid'] = ProjectCategory::all($data['pcid']);
        $data['address'] = City::all($data['address']);
        $img_list = explode(',', $data['img_list']);
        array_pop($img_list);
        $data['img_list'] = $img_list;
        if (!empty($data['member'])) {
            $projectMember_model = new ProjectMember();
            $data['member'] = $projectMember_model->getProjectMember($data['member']);
        }
        $data['ptype'] = getPType($data['ptype']);
        $projectCollect_model = new ProjectCollect();
        $data['collect'] = $projectCollect_model->checkCollect($value);//1-收藏 2-未收藏
        $data['url'] = SHARE_PATH . $value['project_id'];
        return _unsetNull($data);
    }

    public function getSendProjectList($value)
    {
        $data = $this->where('uid', $value['uid'])->where('status', 3)->field('pid,name,logo,status,ptype')->page($value['p'], config('page'))->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $this->projectData($data);
    }

    private function projectData($data)
    {
        $arr = [];
        foreach ($data as $v) {
            $v['status_name'] = getStatus($v['status']);
            $v['type_name'] = getPType($v['ptype']);
            $arr[] = $v;
        }
        return $arr;
    }

    private function projectDataMore($data)
    {
        $browse_model = new Browse();
        $arr = [];
        foreach ($data as $v) {
            $v['browse_num'] = $browse_model->getBrowseNumByPid($v['pid']);
            $v['pcid'] = collection(ProjectCategory::all($v['pcid']))->toArray();
            $v['address'] = collection(City::all($v['address']))->toArray();
            $v['img_list'] = strstr($v['img_list'], ',', true);
//            $v['img_list'] = explode(',',$v['img_list']);

            $v['ptype'] = getPType($v['ptype']);
            $arr[] = $v;
        }
        return $arr;
    }

    public function getBrowseProjectList($browse_project)
    {
//        $data = $this->where('pid', 'in', $project_id)->field('pid,name,logo,status,ptype')->select();
//        if (empty($data)) return [];
        return $this->projectData($browse_project);
    }

    public function getCollectProjectList($pid)
    {
        $data = $this->where('pid', 'in', $pid)->order('pid', 'desc')->field('pid,name,address,pcid,company_name,img_list,money,ptype')->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $this->projectDataMore($data);
    }

    public function getOneProject($uid)
    {
        $data = $this->where('uid', $uid)->where('status', '>', 1)->order('pid', 'desc')->field('pid,name,logo,status,ptype')->find();
        if (empty($data)) return null;
//        $data['status'] = getStatus($data['status']);
//        $data['ptype'] = getPType($data['ptype']);
        $data['status_name'] = getStatus($data['status']);
        $data['type_name'] = getPType($data['ptype']);
        return $data;
    }

    public function getUsername($pid)
    {
        $join = [['tpn_promulgator u', 'u.uid=a.uid']];
        $data = $this->alias('a')->where('a.pid', $pid)->join($join)->value('u.name');
        return $data;
    }
}