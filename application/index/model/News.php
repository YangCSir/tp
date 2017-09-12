<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/20
 * Time: 10:36
 */

namespace app\index\model;


use think\Model;

class News extends Model
{
    public function getInformationByCategory($value)
    {
        $data = $this->where('ncid', $value['ncid'])->order('nid desc')->page($value['p'], config('page'))->field('nid,title,img,intro,browse,add_time')->select();
        if (empty($data)) return [];
        $arr = [];
        foreach ($data as $v) {
            if (!empty($v['img'])) {
                $v['img'] = explode(',', $v['img']);
            } else {
                $v['img'] = [];
            }

            $v['add_time'] = date("m-d H:i", $v['add_time']);
            $v['url'] = POST_PATH . $v['nid'];
            $arr[] = $v;
        }
        return $arr;
    }
}