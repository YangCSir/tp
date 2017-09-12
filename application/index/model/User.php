<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/12
 * Time: 14:59
 */

namespace app\index\model;


use think\Model;

class User extends Model
{
    /**
     * 检查登录
     * @param $value
     * @return array|bool
     */
    public function checkLogin($value)
    {
        $data = User::get(['phone' => $value['phone'], 'password' => md5($value['password'])]);
        if (empty($data)) exit(\Response::json(FAIL, '密码输入错误'));
        $token = md5($data->uid . time());
        $result = User::get($data->uid);
        $result->logintoken = $token;
        if ($result->save()) {
            return ['token' => $token, 'unique' => md5($data->uid)];
        } else {
            return false;
        }
    }


    public function getUserIdByToken($token)
    {
        $user = User::get(['logintoken' => $token]);
        if (empty($user)) exit(\Response::json(LOGIN_AGAIN, '登录过期，请重新登录'));
        return $user->uid;
    }

    public function registerUser($value)
    {
        $value['niname'] = substr_replace($value['phone'], '****', 4, 4);
        $value['password'] = md5($value['password']);
        $value['add_time'] = time();
        $value['login_time'] = time();
        $token = md5(time() . $value['password']);
        $value['logintoken'] = $token;
        $user = new User($value);
        if ($user->allowField(true)->save()) {
            return ['token' => $token, 'unique' => $user->uid];
        } else {
            return false;
        }
    }

    public function forgetPass($value)
    {
        if (User::where('phone', $value['phone'])->update(['password' => md5($value['password'])])) {
            return true;
        } else {
            return false;
        }
    }

    public function changePass($value)
    {
        if ($this->where('uid', $value['uid'])->update(['password' => md5($value['password'])])) return true;
        return false;
    }

    public function thirdLoginRegister($value)
    {
        $user=new User($value);
        $data=$user->allowField(true)->save();
        dump($data);die;
    }
}