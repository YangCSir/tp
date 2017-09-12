<?php
/**
 * [支付宝支付]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\extend;
use think\Config;

class Alipay{
    /**
     * 创建PC支付
     * @param array $data
     * $data = [
            'out_trade_no'  => create_order_num(), // 商户订单号，商户网站订单系统中唯一订单号，必填
            'subject'       => '积分充值', // 订单名称，必填
            'total_amount'  => 1, // 付款金额，必填
            'body'          => '' // 商品描述，可空
       ];
     */
    public function createPcPay($data = []){
        // 载入相关文件
        include_once EXTEND_PATH . 'alipay/pagepay/service/AlipayTradeService.php';
        include_once EXTEND_PATH . 'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
        // 加载配置项
        $conf = Config::get('pay.alipay');
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder -> setBody($data['body']);
        $payRequestBuilder -> setSubject($data['subject']);
        $payRequestBuilder -> setTotalAmount($data['total_amount']);
        $payRequestBuilder -> setOutTradeNo($data['out_trade_no']);
        $aop = new \AlipayTradeService($conf);

        // 电脑网站支付请求
        $response = $aop->pagePay($payRequestBuilder,$conf['return_url'],$conf['notify_url']);
        // 输出表单
        var_dump($response);
    }

    /**
     * 创建APP支付
     * @param array $data
     * @return string
     * $data = [
            'out_trade_no'  => create_order_num(), // 商户订单号，商户网站订单系统中唯一订单号，必填
            'subject'       => '积分充值', // 订单名称，必填
            'total_amount'  => 1, // 付款金额，必填
            'body'          => '' // 商品描述，可空
       ];
     */
    public function createAppPay($data = []){
        // 载入相关文件
        require_once EXTEND_PATH . 'alipay/aop/AopClient.php';
        require_once EXTEND_PATH . 'alipay/aop/request/AlipayTradeAppPayRequest.php';
        // 加载配置项
        $conf = Config::get('pay.alipay');
        $aop = new \AopClient;
        $aop -> gatewayUrl = $conf['gatewayUrl'];
        $aop -> appId = $conf['app_id'];
        $aop -> rsaPrivateKey = $conf['merchant_private_key'];
        $aop -> format = "json";
        $aop -> charset = $conf['charset'];
        $aop -> signType = $conf['sign_type'];
        $aop -> alipayrsaPublicKey = $conf['alipay_public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"\","
            . "\"subject\": \"".$data['subject']."\","
            . "\"out_trade_no\": \"".$data['out_trade_no']."\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".$data['total_amount']."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request -> setNotifyUrl($conf['notify_url']);
        $request -> setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop -> sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
    }
}