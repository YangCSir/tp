<?php
/**
 * [普通用户管理模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class User extends CommonModel{
    public $err;
    public $data;
    public $pk = 'uid';

    /**
     * [checkData 验证数据]
     */
    public function checkData($data, $noverify = []){
        // 验证规则
        $rule = [
            'phone' => ['require', 'regex' => '/^1[0-9]{10}$/'],
            'password' => 'require',
            'niname' => 'require',
            'status' => 'require',
       ];

        // 去除不需要验证的字段
        if (!empty($noverify)) {
            foreach ($noverify as $v) {
                unset($rule[$v]);
            }
        }

        // 错误提示
        $message = [
            'uid.require' => '请填写主键',
            'phone.regex' => '请填写正确的账号',
            'phone.require' => '请填写账号',
            'password.require' => '请填写密码',
            'niname.require' => '请填写昵称',
            'qq.require' => '请填写QQ号',
            'wechat.require' => '请填写微信号',
            'face.require' => '请填写头像',
            'sex.require' => '请填写性别 1：男，2：女',
            'add_time.require' => '请填写注册时间',
            'login_time.require' => '请填写登录时间',
            'logintoken.require' => '请填写用户登录凭证',
            'weichat_openid.require' => '请填写微信openid',
            'login_qq.require' => '请填写QQ登录token',
            'login_wechat.require' => '请填写微信登录',
            'login_sina.require' => '请填写新浪登录',
            'status.require' => '请填写状态 0：禁用，1：正常',
            'type.require' => '请填写类型  0：未认证，1：投资者，2：项目者 3：两者都是',
            'email.require' => '请填写邮箱',
        ];

        // 创建验证
        if (!empty($rule) && !empty($message)) {
            // 创建验证规则
            $validate = new Validate($rule, $message);
            // 执行验证
            if (!$validate -> check($data)) {
                // 保存错误信息
                $this -> err = $validate -> getError();
                return false;
            }
        }

        // 保存验证过的数据
        $this -> data = $data;
        return true;
    }
}