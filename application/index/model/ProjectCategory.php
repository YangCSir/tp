<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/14
 * Time: 16:30
 */

namespace app\index\model;


use think\Cache;
use think\Model;

class ProjectCategory extends Model
{
    public function getCategoryList()
    {
        $data = $this->where('pid', '0')->where('status', 1)->field('pcid,name')->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        $arr = [];
        foreach ($data as $v) {
            $son = $this->where('pid', $v['pcid'])->where('status', 1)->field('pcid,pid,name')->select()->toArray();
            $all = [['pcid' => $v['pcid'], 'pid' => $v['pcid'], 'name' => $v['name']]];
            $v['son']=array_merge($all,$son);
            $arr[] = $v;
        }
        return $arr;
    }

    public function getCateGoryOne()
    {
        $data = $this->where('pid', '0')->where('status', '1')->field('pcid,name')->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $data;
    }
}