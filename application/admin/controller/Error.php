<?php
/**
 * [错误管理]
 * @Author: Careless
 * @Date:   2017-04-09 18:08:42
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use think\Controller;

class Error extends Controller{

    public function access(){
        return view();
    }


    public function err(){
        return view();
    }
}