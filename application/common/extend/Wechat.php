<?php
/**
 * [微信支付管理]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\extend;
use think\Config;

class Wechat{
    public $err = '';
    /**
     * 创建微信扫码支付
     * @param array $data
     * @return string
     * $data = [
            'out_trade_no'  => create_order_num(), // 商户订单号，商户网站订单系统中唯一订单号，必填
            'subject'       => '积分充值', // 订单名称，必填
            'total_amount'  => 1, // 付款金额，必填
            'body'          => '' // 商品描述，可空
       ];
     */
    public function createScanQrcodePay($data = []){
        // 载入所需文件
        require_once EXTEND_PATH . "wechat/lib/WxPay.Api.php";
        require_once EXTEND_PATH . "wechat/example/WxPay.NativePay.php";
        // 配置项
        $conf = Config::get('pay.wechat');
        // 创建支付
        $notify = new \NativePay();
        $input  = new \WxPayUnifiedOrder();
        $input -> SetBody($data['subject'] . $data['body']);
        $input -> SetAttach($data['subject']);
        $input -> SetOut_trade_no($data['out_trade_no']);
        $input -> SetTotal_fee($data['total_amount'] * 100);
        $input -> SetTime_start(date("YmdHis"));
        $input -> SetTime_expire(date("YmdHis", time() + 600));
        $input -> SetGoods_tag($data['subject']);
        $input -> SetNotify_url($conf['notify_url']);
        $input -> SetTrade_type("APP");
        $input -> SetProduct_id($data['out_trade_no']);
        $result = $notify -> GetPayUrl($input);
        if ($result['return_code'] == 'FAIL') {
            $this -> err = $result['return_msg']; return false;
        }

        // 二维码信息
        return urlencode($result["code_url"]);
    }

    /**
     * 创建微信APP支付
     * @param array $data
     * @return bool|\json数据，可直接填入js函数作为参数
     * $data = [
            'out_trade_no'  => create_order_num(), // 商户订单号，商户网站订单系统中唯一订单号，必填
            'subject'       => '积分充值', // 订单名称，必填
            'total_amount'  => 1, // 付款金额，必填
            'body'          => '' // 商品描述，可空
       ];
     */
    public function createAppPay($data = []){
        // 载入所需文件
        require_once EXTEND_PATH . "wechat/lib/WxPay.Api.php";
        require_once EXTEND_PATH . "wechat/example/WxPay.NativePay.php";
        require_once EXTEND_PATH . "wechat/example/WxPay.JsApiPay.php";

        // 配置项
        $conf = Config::get('pay.wechat');
        // 创建支付
        $notify = new \NativePay();
        $input  = new \WxPayUnifiedOrder();
        $input -> SetBody($data['subject'] . $data['body']);
        $input -> SetAttach($data['subject']);
        $input -> SetOut_trade_no($data['out_trade_no']);
        $input -> SetTotal_fee($data['total_amount'] * 100);
        $input -> SetTime_start(date("YmdHis"));
        $input -> SetTime_expire(date("YmdHis", time() + 600));
        $input -> SetGoods_tag($data['subject']);
        $input -> SetNotify_url($conf['notify_url']);
        $input -> SetTrade_type("APP");
        $input -> SetProduct_id($data['out_trade_no']);
        // 创建订单信息
        $order = \WxPayApi::unifiedOrder($input);

        $order['timestamp'] = time();
        $str = 'appid='.$order['appid'].'&noncestr='.$order['nonce_str'].'&package=Sign=WXPay&partnerid='.\WxPayConfig::MCHID.'&prepayid='.$order['prepay_id'].'&timestamp='.$order['timestamp'];
        //重新生成签名
        $order['sign'] = strtoupper(md5($str.'&key='.\WxPayConfig::KEY));
        //将$order_data数据返回给APP端调用
        return json_encode($order);
        /*if ($order['return_code'] == 'FAIL') {
            $this -> err = $order['return_msg']; return false;
        }
        $tools = new \JsApiPay();
        $api = $tools -> GetJsApiParameters($order);
        return $api;*/
    }
}