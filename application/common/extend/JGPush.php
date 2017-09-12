<?php
/**
 * [极光推送]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\extend;
use think\Config;
require EXTEND_PATH . 'jpush/autoload.php';
use JPush\Client as JPush;

class JGPush{

    /**
     * 创建推送
     * @param array $tag 群组
     * @param string $title 消息标题
     * @param array $data 数据
     */
    public function push($alias = '',$tag = [], $title = '', $data = []){
        // 加载配置项
        $conf = Config::get('jpush');

        $app_key = '871d145c82cf3e3997ada8e2';
        $master_secret ='9e89aceeda67f10ba75c189f';
        $client = new \JPush\Client($app_key,$master_secret);
        $pusher = $client->push();
        $pusher->setPlatform('all');
        $pusher->options(['apns_production'=>true]);
        $pusher->addAlias('17');
        $pusher->setNotificationAlert('hello');
        try {
            $res = $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {

        }
    }
}