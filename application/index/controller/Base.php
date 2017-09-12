<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/12
 * Time: 15:17
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Loader;
use think\Log;

class Base extends Controller
{
    private $apiid = "WJJMeaBX";
    private $apikey = "ZIKXexGfVFaNFRy3";
    protected $value;
    protected $allow_ios_request = array('feedback', 'investorAuthenticationOne', 'investorAuthenticationTwo', 'promulgatorAuthentication', 'releaseProjectOne', 'releaseProjectTwo', 'releaseProjectThree', 'projectEditOne', 'projectEditThree', 'projectAddMember');

    public function _initialize()
    {
        Loader::import('Response', EXTEND_PATH);
        if (!$this->checkApiKey()) {
            exit(\Response::json(NO_PASS, '验证不通过'));
        }
    }

    private function checkApiKey()
    {
        $data = input('post.data');
        Log::record("data为：" . $data);
        if (empty($data)) {
            $result = $_POST;
            if (empty($result['apitype'])) exit(\Response::json(BAD_REQUEST, '数据类型错误1'));
            if (!in_array($result['apitype'], $this->allow_ios_request)) exit(\Response::json(BAD_REQUEST, '数据类型错误2'));
        } else {
            parse_str(base64_decode($data), $result);
            if (!is_array($result) || empty($result)) exit(\Response::json(BAD_REQUEST, '数据类型错误'));
        }
        if (empty($result['apiid'])) return false;
        if (empty($result['apikey'])) return false;
        if ($result['apiid'] !== $this->apiid) return false;
        if ($result['apikey'] !== $this->apikey) return false;
        if (empty($result['apitype'])) exit(\Response::json(BAD_REQUEST, '数据类型错误3'));
        unset($result['apiid'], $result['apikey']);
        $this->value = $result;
        return true;
    }

    public function getLogoUrl()
    {
        $data = Db::name('slideshow')->where('id', 2)->value('img');
        return '.' . parse_url($data)['path'];
    }
}