<?php
/**
 * [发送短信类]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\extend;
use think\Config;

class Note{
    // 保存错误信息
    public $err = '';

    /**
     * 短信宝发送短信
     * @param string $phone     手机号
     * @param string $content   内容
     * @return bool
     */
    public function smsbao($phone, $content){
        // 验证手机号
        if (!preg_match('/^1\d{10}$/', $phone)) {
            $this -> err = '手机号错误'; return false;
        }
        // 验证内容
        if (empty($content)) {
            $this -> err = '请输入短信内容'; return false;
        }
        // 获取短信配置项
        $conf = Config::get('note.smsbao');
        // 发送短信
        $smsapi = "http://api.smsbao.com/";
        $sendurl = $smsapi."sms?u=".$conf['username']."&p=".md5($conf['password'])."&m=".$phone."&c=".urlencode($content);
        $res = curl($sendurl);

        if ($res == 0) {
            return true;
        } else {
            // 定义错误信息
            $smsbaoErr = [
                '30'    => '密码错误',
                '40'    => '账号不存在',
                '41'    => '余额不足',
                '42'    => '帐号过期',
                '43'    => 'IP地址限制',
                '50'    => '内容含有敏感词',
                '51'    => '手机号码不正确',
            ];
            $this -> err = $smsbaoErr[$res]; return false;
        }
    }

    /**
     * 网易云发送短信
     * @param $phone            手机号
     * @param $tpl              短信模板
     * @param string $params    自定义参数
     * @return bool
     */
    public function wangyi($phone, $tpl, $params = ''){
        // 验证手机号
        if (!preg_match('/^1\d{10}$/', $phone)) {
            $this -> err = '手机号错误'; return false;
        }
        // 验证模板
        if (!preg_match('/^\d{6,9}$/', $tpl)) {
            $this -> err = '短信模板错误'; return false;
        }
        // 请求地址 - 请求参数
        if (empty($params)) {
            $url = 'https://api.netease.im/sms/sendcode.action';
            $info = [
                'mobile'        => $phone,
                'templateid'    => $tpl
            ];
        } else {
            $url = 'https://api.netease.im/sms/sendtemplate.action';
            $tmp = '['; $d = '';
            foreach ($params AS $v) {
                $tmp .= $d . '"' . $v . '"';
                $d = ',';
            }
            $tmp .= ']';
            // 请求参数
            $info = [
                'mobiles'        => '["'.$phone.'"]',
                'templateid'    => $tpl,
                'params'        => $tmp
            ];
        }

        // 获取短信配置项
        $conf = Config::get('note.wangyi');
        // 加密信息
        $Nonce   = md5(time());
        $CurTime = time();
        $AppKey  = $conf['app_key'];
        // 计算加密
        $sign = sha1($conf['app_secret'] . $Nonce . $CurTime);
        // header头
        $headers = [
            'AppKey: '.$AppKey,
            'Nonce: '.$Nonce,
            'CurTime: '.$CurTime,
            'CheckSum: '.$sign,
            'Content-Type: application/x-www-form-urlencoded',
        ];
        // 发送请求
        $res = curl($url, $info, 'post', $headers);
        // 成功返回发送的短信验证码
        if ($res != false && $res['code'] == 200){
            return $res['obj'];
        }
        // 失败返回错误信息
        else {
            $this -> err = $res['msg'] . ' error'; return false;
        }
    }
}