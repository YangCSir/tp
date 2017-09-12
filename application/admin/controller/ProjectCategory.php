<?php
/**
 * [项目行业管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 13:59:31
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\ProjectCategory as model;
use app\common\extend\Data;

class ProjectCategory extends Common{
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
            $where['name'] = ['like', "%{$keywords}%"];
        }
        // 获取数据
        $tmp = $model -> getAll($where);
        $data = Data::tree($tmp, 'name', 'pcid');

        // 模板
        return view('',[
            'data'     => $data,
            'keywords' => $keywords,
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
            // 验证
            if (!$model -> checkData($data, ['pcid','status'])) {
                return ajax($model -> err, 2);
            }
            // 判断名称是否被占用
            if ($model -> getOne(['name'=>$data['name']])) {
                return ajax('名称已存在',2);
            }

            // 添加数据
            if (!$model -> addData()) return ajax('添加失败', 2);
            return ajax('添加成功');
        }

        // 获取数据
        $model = new model;
        $data = $model -> getAll(['pid'=>0]);
        return view('', [
            'cat'   => $data
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
            // 获取旧数据，判断名称是否重复
            $where = [
                'name'     => $data['name'],
                'pcid'       => ['neq', $data['pcid']]
            ];
            if ($model -> getOne($where)) {
                return ajax('名称已存在', 2);
            }

            // 修改数据
            if (!$model -> editData(['pcid'=>$data['pcid']], $data)) return ajax('没有数据被修改', 2);
            return ajax('修改成功');
        }
        
        // 获取旧数据
        $pcid = (int)$this -> param['pcid'];
        $data = $model -> getOne(['pcid'=>$pcid]);

        // 获取数据
        $model = new model;
        $cat = $model -> getAll(['pid'=>0]);
        return view('',[
            'data' => $data,
            'pcid' => $pcid,
            'cat'   => $cat
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
        $pcid = $this -> param['id'];
        $where['pcid'] = $pcid;
        if (!$model -> delData($where)) return ajax('删除失败');
        // 删除子类
        $model -> delData(['pid'=>$pcid]);
        return ajax('删除成功');
    }


    public function status(){
        $param = input('post.');
        // 修改状态
        $model = new model;
        $model -> editData(['pcid'=>$param['pcid']], ['status'=>$param['status']]);
        $model -> editData(['pid'=>$param['pcid']], ['status'=>$param['status']]);
        return ajax('操作成功');
    }
}