<?php
/**
 * [项目发起人管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 10:13:33
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\Promulgator as model;

class Promulgator extends Common{
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
        $where['status'] = ['neq', 0];
        if (!empty($keywords)) {
            $where['niname|uid|iid|phone|name'] = ['like', "%{$keywords}%"];
        }
        $data = admin_page(db('v_promulgator_user'), $where, 'status ASC,add_time DESC');

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
        $iid = $this -> param['id'];
        // 可批量删除
        $where['iid'] = ['in', $iid];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }

    /**
     * 审核
     * @return \think\response\Json|\think\response\View
     */
    public function status(){
        if ($this -> isPost) {
            $data = input('post.');
            // 模型
            $model = new model;
            // 判断是否已经操作过
            $old = $model -> getOne(['iid'=>$data['iid']]);
            if ($old['status'] != -1) return ajax('该记录已经处理过');
            // 操作时间
            $data['up_time'] = time();
            // 修改
            if (!$model -> editData(['iid'=>$data['iid']], $data)) {
                return ajax('系统错误', 2);
            }

            // 通过时 修改用户类型
            if ($data['status'] == 1) {
                $umodel = db('user');
                $uinfo  = $umodel -> where(['uid'=>$old['uid']]) -> find();
                if ($uinfo['type'] == 0) {
                    $type = 2;
                } else {
                    $type = 3;
                }
                $umodel -> where(['uid'=>$old['uid']]) -> update(['type'=>$type]);
            }

            return ajax('操作成功');
        }

        return view('', [
            'data'    => $this -> param
        ]);
    }

    /**
     * 详情
     * @return \think\response\View
     */
    public function detail(){
        $where['iid'] = $this -> param['iid'];
        $model = new model;
        $data = $model -> getOne($where);
        return view('', [
            'data'  => $data
        ]);
    }
}