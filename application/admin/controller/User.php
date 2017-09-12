<?php
/**
 * [普通用户管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\User as model;

class User extends Common{
    protected $checkVal = [];
    /**
     * [_initialize 初始化]
     */
    public function _initialize(){
        parent::_initialize();
        $this -> checkVal = [
            'statusVal' => [
                '0' => '禁用',
                '1' => '启用',
            ],
        ];
    }
    /**
     * [index 列表]
     */
    public function index(){
        // 搜索关键词
        $keywords = input('get.keywords');
        //实例化模型
        $model = new model;
        $where = '';
        if (!empty($keywords)) {
            $where['uid|phone|niname'] = ['like', "%{$keywords}%"];
        }
        // 获取数据
        $data  = admin_page($model, $where, 'add_time DESC');
        
        // 模板
        return view('',[
            'data'     => $data['data'],
            'page'     => $data['page'],
            'keywords' => $keywords,
            'checkVal' => $this -> checkVal
        ]);
    }

    /**
     * [add 添加数据]
     */
    public function add(){
        if ($this -> isPost) {
            // 实例化模型
            $model = new model;
            // 获取post数据
            $data = input('post.');
            $data['add_time'] = time();
            // 验证
            if (!$model -> checkData($data)) {
                return ajax($model -> err, 2);
            }
            // 判断用户是否注册过
            if ($model -> getOne(['phone'=>$data['phone']])) {
                return ajax('账号已被占用', 2);
            }
            // 添加数据
            $data['password'] = md5($data['password']);
            if (!$model -> addData($data)) return ajax('添加失败', 2);
            return ajax('添加成功');
        }

        return view('', [
            'checkVal' => $this -> checkVal
        ]);
    }

    /**
     * [edit 编辑]
     */
    public function edit(){
        $model = new model;
        // POST提交处理
        if ($this -> isPost) {
            // 获取post数据
            $data = input('post.');

            // 验证
            if (!$model -> checkData($data, ['password'])) {
                return ajax($model -> err, 2);
            }

            // 判断修改的账号是否被占用
            $where = [
                'phone'     => $data['phone'],
                'uid'       => ['neq', $data['uid']]
            ];
            if ($model -> getOne($where)) {
                return ajax('账号已被占用', 2);
            }
            if (!empty($data['password'])) {
                $data['password'] = md5($data['password']);
            } else {
                unset($data['password']);
            }
            // 修改数据
            if (!$model -> editData(['uid'=>$data['uid']], $data)) return ajax('没有数据被修改', 2);
            return ajax('修改成功');
        }
        
        // 获取旧数据
        $uid = (int)$this -> param['uid'];
        $data = $model -> getOne(['uid'=>$uid]);
        
        return view('',[
            'data' => $data,
            'uid' => $uid,
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
        $uid = $this -> param['id'];
        // 可批量删除
        $where['uid'] = ['in', $uid];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }

    /**
     * 修改状态
     * @return \think\response\Json
     */
    public function status(){
        $param = input('post.');
        $model = new model;
        $model -> editData(['uid'=>$param['uid']], ['status'=>$param['status']]);
        return ajax('操作成功');
    }

    /**
     * 详情
     * @return \think\response\View
     */
    public function detail(){
        $where['uid'] = $this -> param['uid'];
        $model = new model;
        $data = $model -> getOne($where);

        return view('', [
            'data'      => $data,
            'checkVal'  => $this -> checkVal
        ]);
    }
}