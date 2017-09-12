<?php
/**
 * [后台公共管理控制器]
 * @Author: Careless
 * @Date:   2017-04-09 18:08:42
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Request;

class Common extends Controller{
    public $user;
    public $param;
    public $isPost;
    public $isAjax;
    public $access;
    
    /**
     * [_initialize 初始化]
     */
    public function _initialize(){
        $request = Request::instance();
        $this -> param  = Request::instance() -> param();
        $this -> isPost = Request::instance() -> method() == 'POST' ? true : false;
        $this -> isAjax = Request::instance() -> isAjax();
        // 判断是否登陆
        $user = Session::get('adminUser');
        if (empty($user)){
            // $this -> redirect(url('login/login'));
            $url = url('login/login');
            echo "<script>window.parent.location.href='{$url}';</script>";
            die;
        }
        $this -> user = $user;

        // 获取当前操作控制和方法
        $controller = underline_to_hump($request -> controller());
        $method     = underline_to_hump($request -> action(), false);
        // 获取当前用户权限
        $tmp = db('access_group') -> where(['id'=>['in',$this -> user['group_id']]]) -> select();
        // 保存权限
        $this -> access = $tmp;

        // 获取当前组的权限
        $id = Session::get('gid') ? Session::get('gid') : $tmp[0]['id'];
        foreach ($tmp as $v) {
            if ($v['id'] == $id) {
                $access = json_decode($v['access'], true);
            }
        }

        // 通用权限
        $access['Index'] = ['index','welcome','table','form','chart'];
        $access['Upload'] = ['upload'];
        // 判断是否有控制器权限
        if (!array_key_exists($controller, $access)) {
            // 判断是否为异步请求
            if ($this -> isAjax) {
                echo json_encode([
                    'status'    => 2,
                    'msg'       => '! 您没有权限执行此操作，如需操作，请与管理员联系。'
                ]);die;
            } else {
                // 跳转到权限不足
                $go = url('error/access');
                $this -> redirect($go);
            }
        }

        // 判断是否有方法权限
        if (!in_array($method, $access[$controller])){
            // 判断是否为异步请求
            if ($this -> isAjax) {
                echo json_encode([
                    'status'    => 2,
                    'msg'       => '! 您没有权限执行此操作，如需操作，请与管理员联系。'
                ]);die;
            } else {
                // 跳转到权限不足
                $go = url('error/access');
                $this -> redirect($go);
            }
        }
    }
}

