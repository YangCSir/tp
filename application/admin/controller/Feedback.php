<?php
/**
 * [反馈列表控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 14:44:58
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\Feedback as model;

class Feedback extends Common{
    protected $checkVal = [];
    /**
     * [_initialize 初始化]
     */
    public function _initialize(){
        parent::_initialize();
        $this -> checkVal = [
        ];
    }
    /**
     * [index 列表]
     */
    public function index(){
        // 搜索关键词
        $keywords = input('get.keywords');
        // 获取视图数据
        $where = '';
        if (!empty($keywords)) {
            $where['content|phone|niname'] = ['like', "%{$keywords}%"];
        }
        $data = admin_page(db('v_feedback_user'), $where, 'add_time DESC');
        if (!empty($data['data'])) {
            foreach($data['data'] AS $k => $v){
                $data['data'][$k]['img'] = explode(',', $v['img']);
            }
        }
        // 模板
        return view('',[
            'data'     => $data['data'],
            'page'     => $data['page'],
            'keywords' => $keywords,
            'checkVal' => $this -> checkVal
        ]);
    }

    /**
     * [del 删除]
     */
    public function del(){
        // 实例化模型
        $model = new model;
        if (!$this -> isPost) return ajax('非法请求');
        // 定义条件
        $fid = $this -> param['id'];
        // 可批量删除
        $where['fid'] = ['in', $fid];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }


}