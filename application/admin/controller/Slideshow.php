<?php
/**
 * [投资人banner管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\Slideshow as model;

class Slideshow extends Common{
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
        //实例化模型
        $model = new model;
        $where = '';
        if (!empty($keywords)) {
            ;
        }
        // 获取数据
        $data  = $model -> getAll($where);
        
        // 模板
        return view('',[
            'data'     => $data,
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
            if (!$model -> checkData($data, ['id','add_time'])) {
                return ajax($model -> err, 2);
            }
            // 添加数据
            if (!$model -> addData()) return ajax('添加失败', 2);
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
            if (!$model -> checkData($data, [])) {
                return ajax($model -> err, 2);
            }
            // 修改数据
            if (!$model -> editData(['id'=>$data['id']])) return ajax('没有数据被修改', 2);
            return ajax('修改成功');
        }
        
        // 获取旧数据
        $id = (int)$this -> param['id'];
        $data = $model -> getOne(['id'=>$id]);
        
        return view('',[
            'data' => $data,
            'id' => $id,
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
        $id = $this -> param['id'];
        // 可批量删除
        $where['id'] = ['in', $id];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }


}