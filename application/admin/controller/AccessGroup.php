<?php
/**
 * [权限组管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-05-01 20:47:45
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\AccessGroup as model;

class AccessGroup extends Common{
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

        // 获取数据
        $data  = $model -> getAll();
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
            $data = input('post.');
            // 实例化模型
            $model = db('access_group');
            // 重组数据
            $name = $data['name'];
            // 判断名称是否存在
            if ($model -> where(['name'=>$name]) -> find()) {
                return ajax('组名称已存在', 2);
            }

            unset($data['name']);
            $add = [
                'name'      => $name,
                'access'    => json_encode($data),
                'add_time'  => time(),
                // 'is_show'   => $data['is_show']
            ];
            // 执行添加
            $model -> insert($add);
            return ajax('添加成功');
        }

        // 获取菜单列表，用户权限选择
        $tmp = db('menu') -> order('`id` ASC') -> select();
        foreach ($tmp as $k => $v) {
            $v['method'] = json_decode($v['method'], true);
            $menu[$v['mcid']][] = $v;
        }
        // 获取菜单分类
        $mc = db('menu_category') -> order('`sort` ASC') -> select();
        foreach ($mc as $v) {
            $nmc[$v['id']] = $v;
        }
        return view('',[
            'menu'      => $menu,
            'mc'        => $nmc
        ]);
    }

    /**
     * [edit 编辑]
     */
    public function edit(){
        $model = db('access_group');
        // POST提交处理
        if ($this -> isPost) {
            $data = input('post.');
            // 重组数据
            $name = $data['name'];
            $id   = $data['id'];
            unset($data['name'], $data['id']);
            $add = [
                'id'        => $id,
                'name'      => $name,
                'access'    => json_encode($data),
                'edit_time' => time(),
                // 'is_show'   => $data['is_show']
            ];
            // 执行修改
            $model -> update($add);
            return ajax('修改成功');
        }
        
        $id = (int)$this -> param['id'];
        // 获取旧数据
        $oldData = $model -> where(['id'=>$id]) -> find();
        // 获取菜单列表，用户权限选择
        $tmp = db('menu') -> order('`id` ASC') -> select();
        foreach ($tmp as $k => $v) {
            $v['method'] = json_decode($v['method'], true);
            $menu[$v['mcid']][] = $v;
        }
        // 获取菜单分类
        $mc = db('menu_category') -> order('`sort` ASC') -> select();
        foreach ($mc as $v) {
            $nmc[$v['id']] = $v;
        }

        $oldData['access'] = json_decode($oldData['access'], true);
        // 载入模板
        return view('',[
            'oldData'   => $oldData,
            'menu'      => $menu,
            'mc'        => $nmc
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
        $old = $model -> getOne(['id'=>$id]);
        if ($old['is_sys'] == 1) {
            return ajax('!  非法操作，管理员组不允许删除', 2);
        }
        // 可批量删除
        $where['id'] = ['in', $id];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }


}