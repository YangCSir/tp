<?php
/**
 * [邮件管理类]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\extend;
use think\Config;
include_once EXTEND_PATH . 'phpmailer/class.phpmailer.php';

class Email{
    public $err = '';
    /**
     * 发送邮件
     * @param $address  收件人邮箱地址
     * @param $title    邮件标题
     * @param $content  邮件内容
     * @return bool
     */
    public function sendMeail($address, $title, $content){
        $mail = new \PHPMailer();
        // 设置PHPMailer使用SMTP服务器发送Email
        $mail -> IsSMTP();
        // 设置邮件的字符编码，若不指定，则为'UTF-8'
        $mail -> CharSet = 'UTF-8';
        // 添加收件人地址，可以多次使用来添加多个收件人
        $mail -> AddAddress($address,'亲^_^');
        // 支持html格式内容
        $mail -> IsHTML(false);
        // 设置邮件正文
        // $mail -> Body = $this -> _emailTpl($content);
        $mail -> Body = $content;

        // 获取邮件配置信息
        $conf = Config::get('email');
        // 设置邮件头的From字段。
        $mail -> From = $conf['address'];
        // 设置发件人名字
        $mail -> FromName = $conf['from_name'];
        // 设置邮件标题
        $mail -> Subject = $title;
        // 设置SMTP服务器。
        $mail -> Host = $conf['smtp'];
        // 设置为“需要验证”
        $mail -> SMTPAuth = true;
        // 设置用户名和密码。
        $mail -> Username = $conf['loginname'];
        $mail -> Password = $conf['password'];
        // 发送邮件。
        if (!$mail -> Send()) {
            $this -> err = $mail -> ErrorInfo; return false;
        }
        return true;
    }

    /**
     * 创建发送邮件模板
     * @param $content  邮件内容
     * @return string
     */
    private function _emailTpl($content){
        $time = date('Y-m-d H:i:s');
        $html = <<<Html
    <div style='font-size:12px; width:600px; margin:10px auto; min-height:500px; background:#e7e7e7; border-radius:10px; padding:10px;'>
        <h2 style='color:#3ba7f4;'>ZPF Framework：</h2>
        <div style='padding:0 15px;'>
            {$content}
        </div>
        <div style='text-align:right; line-height:22px; color:#666; margin-top:10px; margin-right:10px;'>
            ZPF Framework<br/>
            {$time}
        </div>
    </div>
Html;
        return $html;
    }
}