<?php
/**
 * [权限组管理控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-05-01 20:47:45
 * @Copyright:
 */
namespace app\admin\controller;

class Admin extends Common{
    /**
     * [index 管理员列表]
     * @return [type] [description]
     */
    public function index(){
        $data = db('admin') -> select() -> toArray();
        if ($data) {
            $groupModel = db('access_group');
            foreach ($data AS $k => $v) {
                $data[$k]['name'] = $groupModel -> where(['id'=>['in', $v['group_id']]]) -> field('name') -> select() -> toArray();
            }
        }

        return view('', [
            'data'  => $data
        ]);
    }


    /**
     * [add 添加]
     */
    public function add(){
        // POST提交处理
        if ($this -> isPost) {
            $data = input('post.');
            // 实例化模型
            $model = db('admin');
            // 判断用户名是否存在
            $oldData = $model -> where(['username'=>$data['username']]) -> find();
            if ($oldData) {
                return ajax('用户名已存在', 2);
            }
            $data['password'] = md5(md5($data['password']));
            $data['group_id'] = implode(',', $data['group_id']);
            // 执行添加
            $res = $model -> insert($data);
            if (!$res) {
                return ajax('添加失败', 2);
            }
            return ajax('添加成功');
        }

        // 获取权限组
        $gModel = db('access_group');
        $group = $gModel -> field('id,name') -> select();

        // 载入模板
        return view('', [
            'group'     => $group
        ]);
    }

    /**
     * [edit 修改]
     */
    public function edit(){
        // 实例化模型
        $model = db('admin');
        // POST提交处理
        if ($this -> isPost) {
            $data = input('post.');
            // 密码加密
            if (!empty($data['password'])) {
                $data['password'] = md5(md5($data['password']));
            } else {
                unset($data['password']);
            }
            $data['group_id'] = implode(',', $data['group_id']);
            // 执行修改
            $res = $model -> update($data);
            return ajax('修改成功');
        }

        $aid = (int)$this -> param['aid'];
        // 获取旧数据
        $oldData = $model -> where(['aid'=>$aid]) -> find();
        $oldData['group_id'] = explode(',', $oldData['group_id']);
        // 获取权限组
        $gModel = db('access_group');
        $group = $gModel -> field('id,name') -> select();

        // 载入模板
        return view('',[
            'oldData'   => $oldData,
            'group'     => $group
        ]);
    }

    /**
     * [del 删除]
     */
    public function del(){
        if ($this -> isPost) {
            // 实例化模型
            $model = db('admin');
            $data = input('post.');
            // 执行删除
            $model -> where(['aid'=>$data['aid']]) -> delete();
            return ajax('删除成功');
        }
        
    }
}
