<?php
/**
 * [项目列表控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 15:11:15
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\Project as model;

class Project extends Common{
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
        $status = empty($this -> param['status']) ? 2 : $this -> param['status'];
        // 获取视图数据
        if ($status == 5) {
            $where['pro_id'] = ['gt',0];
        } else {
            $where['status'] = $status;
            $where['pro_id'] = 0;
        }

        if (!empty($keywords)) {
            $where['name|phone|niname'] = ['like', "%{$keywords}%"];
        }
        $data = admin_page(db('v_project_user'), $where);
        $menu = ['2'=>'待审核', '3'=>'已通过', '4'=>'已拒绝', '5'=>'修改审核项目'];
        // 获取项目分类
        $tmp = db('project_category') -> select() -> toArray();
        $cat = [];
        foreach ($tmp AS $v) {
            $cat[$v['pcid']] = $v['name'];
        }

        // 模板
        return view('',[
            'data'     => $data['data'],
            'page'     => $data['page'],
            'keywords' => $keywords,
            'checkVal' => $this -> checkVal,
            'menu'     => $menu,
            'status'    => $status,
            'cat'       => $cat
        ]);
    }

    /**
     * 项目详情
     * @return \think\response\View
     */
    public function detail(){
        $where['pid'] = $this -> param['pid'];
        $model = new model;
        $data = $model -> getOne($where);
        // 获取项目分类
        $data['cat'] = db('project_category') -> where(['pcid'=>['in',$data['pcid']]]) -> select() -> toArray();

        // 项目发展区域
        $data['city'] = db('city') -> where(['id'=>['in', $data['address']]]) -> select() -> toArray();

        $ptype = [1=>'概念阶段',2=>'研发阶段',3=>'启动阶段',4=>'运营阶段',5=>'盈利阶段'];
        return view('', [
            'data'  => $data,
            'ptype' => $ptype
        ]);
    }

    /**
     * 修改状态
     * @return \think\response\Json
     */
    public function pass(){
        $param = input('post.');
        $model = new model;
        $model -> editData(['pid'=>$param['pid']], ['pass'=>$param['pass']]);
        return ajax('操作成功');
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
            $old = $model -> getOne(['pid'=>$data['pid']]);
            if ($old['status'] != 2) return ajax('该记录已经处理过');

            // 修改
            if ($data['status'] == 3) {
                $data['pro_id'] = 0;
            }

            if (!$model -> editData(['pid'=>$data['pid']], $data)) {
                return ajax('系统错误', 2);
            }
            if ($old['pro_id'] > 0 && $data['status'] == 3) {
                $model -> delData(['pid'=>$old['pro_id']]);
            }

            return ajax('操作成功');
        }

        return view('', [
            'data'    => $this -> param
        ]);
    }
}