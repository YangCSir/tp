<?php
/**
 * [单页面修改控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 09:22:22
 * @Copyright:
 */
namespace app\admin\controller;

class Tip extends Common{
    /**
     * [index 默认方法]
     */
    public function index(){
        if ($this -> isPost) {
            $up[$this -> param['name']] = $this -> param['content'];
            db('tip') -> where(['id'=>1]) -> update($up);
            return ajax('修改成功');
        }

        $name = empty($this -> param['name']) ? 'user_agreement' : $this -> param['name'];
        $menu = [
            'user_agreement'    => '用户协议',
            'project_proposal'  => '项目计划书',
            'investor'          => '投资人认证说明',
            'initiator'         => '项目发起人认证说明',
            'investor_tip'      => '首次投资人认证提示',
            'initiator_tip'     => '首次项目发起人认证须知'
        ];
        $data = db('tip') -> where(['id'=>1]) -> find();

        return view('', [
            'menu'  => $menu,
            'name'  => $name,
            'data'  => $data
        ]);
    }
}