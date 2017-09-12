<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/13
 * Time: 14:46
 */

namespace app\index\model;


use think\Db;
use think\Model;

class Investor extends Model
{
    public function getInvestorList($value)
    {
        $where = [];
        if (!empty($value['intention'])) {
            $category = Db::name('categoryRelation')->where('category_id', 'in', $value['intention'])->field('investor_id')->select();
            $category_id = [];
            foreach ($category as $c) {
                $category_id[] = $c['investor_id'];
            }
            $where['iid'] = array('in', $category_id);
        }
        if (!empty($value['address'])) {
            $city = Db::name('cityRelation')->where('city_id', 'in', $value['address'])->field('investor_id')->select();
            $city_id = [];
            foreach ($city as $i) {
                $city_id[] = $i['investor_id'];
            }
            $where['iid'] = array('in', $city_id);
        }
        $data = $this->where($where)->where('uid', '<>', $value['uid'])->field('iid,uid,name,true_img,invest_intention,invest_address,money_top,money_down,invest_experience,status')->order('iid desc')->page($value['p'], config('page'))->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $this->investorData($data);
    }

    public function getInvestorDetail($investor_id)
    {
        $data = $this->where('iid', $investor_id)->field('iid,uid,name,true_img,invest_intention,invest_address,money_top,money_down,invest_experience,status')->find();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        $data['invest_intention'] = collection(ProjectCategory::all($data['invest_intention']))->toArray();
        $data['invest_address'] = collection(City::all($data['invest_address']))->toArray();
        return $data;
    }

    private function investorData($data)
    {
        $arr = [];
        foreach ($data as $v) {
            $v['invest_intention'] = collection(ProjectCategory::all($v['invest_intention']))->toArray();
            $v['invest_address'] = collection(City::all($v['invest_address']))->toArray();
            $arr[] = $v;
        }
        return $arr;
    }

    public function getBrowseInvestorList($browse_investor)
    {
//        $data = $this->where('iid', 'in', $investor_id)->field('iid,uid,name,true_img,invest_intention,invest_address,money_top,money_down,invest_experience,status')->select();
//        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        return $this->investorData($browse_investor);
    }

    public static function checkIsInvestor($uid)
    {
        $data = Investor::where('uid', $uid)->value('iid');
        if ($data) return true;
        return false;
    }
}