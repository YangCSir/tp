<?php
/**
 * [后台管理员管理模型]
 * @Author: Careless
 * @Date:   2017-04-09 17:38:26
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\model;
use think\Model;

class Admin extends Model{
    public $errMsg;

    /**
     * [login 登陆]
     */
    public function login($data){
        // 判断用户是否存在
        $user = $this -> getOne(['username'=>$data['username']]);
        if (!$user) {
            $this -> errMsg = '该用户不存在';
            return false;
        }

        // 判断密码是否正确
        if ($user['password'] != md5(md5($data['password']))) {
            $this -> errMsg = '密码错误';
            return false;
        }

        // 判断是否被锁定
        if ($user['islock'] == 1) {
            $this -> errMsg = '该用户已被锁定';
            return false;
        }

        return $user;
    }

    /**
     * [getOne 获取一条数据]
     * @param  [type] $where [where条件]
     */
    public function getOne($where){
        $data = $this -> get($where);
        if (!$data) return false;
        return $data -> data;
    }
}

