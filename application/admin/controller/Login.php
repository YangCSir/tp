<?php
/**
 * [登陆管理控制器]
 * @Author: Careless
 * @Date:   2017-04-09 17:25:58
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use think\Session;
use think\Controller;

class Login extends Controller{
    /**
     * [login 登陆]
     */
    public function login(){
        // 请求数据
        $request = request(); 
        // 判断是否为POST提交
        if ($request -> method() == 'POST') {
            // 请求参数
            $param = $request -> post();
            // 判断是否为空
            if (empty($param['username']) || empty($param['password'])) {
                return ajax('请输入用户名和密码', 2);
            }
            // 实例化模型
            $model = new Admin();
            // 登陆验证
            $admin = $model -> login($param);
            if (!$admin) return ajax($model -> errMsg, 2);
            Session::set('adminUser', $admin);
            return ajax('登录成功');
        }

        return view();
    }

    /**
     * [loginOut 退出登录]
     */
    public function loginOut(){
        Session::set('adminUser', null);
        $this -> redirect(url('login/login'));
    }
}
