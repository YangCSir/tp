<?php
namespace app\index\controller;


use app\index\model\Browse;
use app\index\model\City;
use app\index\model\Investor;
use app\index\model\MemberWork;
use app\index\model\News;
use app\index\model\Project;
use app\index\model\ProjectCategory;
use app\index\model\ProjectCollect;
use app\index\model\ProjectMember;
use app\index\model\Promulgator;
use app\index\model\User;
use JPush\Client;
use think\Cache;
use think\Db;
use think\Loader;
use think\Log;

//                   _ooOoo_
//                  o8888888o
//                  88" . "88
//                  (| -_- |)
//                  O\  =  /O
//               ____/`---'\____
//             .'  \\|     |//  `.
//            /  \\|||  :  |||//  \
//           /  _||||| -:- |||||-  \
//           |   | \\\  -  /// |   |
//           | \_|  ''\---/''  |   |
//           \  .-\__  `-`  ___/-. /
//         ___`. .'  /--.--\  `. . __
//      ."" '<  `.___\_<|>_/___.'  >'"".
//     | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//     \  \ `-.   \_ __\ /__ _/   .-` /  /
//======`-.____`-.___\_____/___.-`____.-'======
//                   `=---='
//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//         佛祖保佑       永无BUG
//  本模块已经经过开光处理，绝无可能再产生bug
//=============================================

class Index extends Base
{
    private $allow_token_apitype = ['login', 'register', 'forget', 'investmentIntention', 'investmentAddress', 'verify', 'tips', 'investmentStatus', 'categoryOne', 'addressOne', 'thirdLogin', 'versionUpdate'];
    private $sureOrNo_token_apitype = ['contact'];

    public function index()
    {
        $apitype = $this->value['apitype'];
        if (!empty($this->value['phone'])) {//验证手机格式
            if (!preg_match("/^1[34578]{1}\\d{9}$/", $this->value['phone'])) exit(\Response::json(PARAM_FAIL, '手机格式不正确'));
        }
        if (!empty($this->value['password']) && $apitype != 'login') {
            if (!preg_match("/^[0-9a-zA-Z]{6,16}$/", $this->value['password'])) exit(\Response::json(PARAM_FAIL, '设置6-16位数字、字母密码'));
        }
        if (!empty($this->value['email'])) {
            if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $this->value['email'])) exit(\Response::json(PARAM_FAIL, '邮箱格式错误'));
        }
        unset($this->value['apitype']);
        if (!in_array($apitype, $this->allow_token_apitype)) {//如果在不用登陆就能请求的接口则不用
            if (!in_array($apitype, $this->sureOrNo_token_apitype)) {
                if (empty($this->value['token'])) exit(\Response::json(LOGIN_AGAIN, '请先登录！'));
            }//如果在可有或可无
            if (!empty($this->value['token'])) {
                $user_model = new User();
                $uid = $user_model->getUserIdByToken($this->value['token']);
                if (!$uid) exit(\Response::json(LOGIN_AGAIN, '登录过期，请重新登录'));
                $this->value['uid'] = $uid;
                unset($this->value['token']);
            }
        }
        if (!function_exists($apitype)) exit(\Response::json(FAIL, "该接口不存在"));
        $this->$apitype();
    }

    /**
     * @param phone [手机]
     * @param password [密码]
     * @return [登录]
     */
    public function login()
    {
        $user_model = new User();
        if (empty($this->value['phone'])) exit(\Response::json(PARAM_FAIL, '手机号不能为空'));
        if (empty($user_model->where('phone', $this->value['phone'])->value('uid'))) exit(\Response::json(FAIL, '手机号不存在'));
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '密码不能为空'));
        $data = $user_model->checkLogin($this->value);
        if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param phone [手机]
     * @param password [密码]
     * @param verity [验证码]
     * @return [注册]
     */
    public function register()
    {
        $user_model = new User();
        if (empty($this->value['phone'])) exit(\Response::json(PARAM_FAIL, '手机号不能为空'));
        if ($user_model->where('phone', $this->value['phone'])->value('uid')) exit(\Response::json(FAIL, '手机号已经存在'));
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '密码不能为空'));
        if (empty($this->value['verity'])) exit(\Response::json(PARAM_FAIL, '验证码不能为空'));
        $verify = Db::name('code')->where('phone', $this->value['phone'])->order('id', 'desc')->limit(1)->find();
        if ($verify['time'] + 900 < time()) exit(\Response::json(FAIL, '验证码超过有效期，请重新获取'));
        if ($this->value['verity'] != $verify['code']) exit(\Response::json(FAIL, '验证码不正确!'));
        $data = $user_model->registerUser($this->value);
        if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param phone [手机号]
     * @param type [类型1-注册 2-找回]
     * @return [发送验证码]
     */
    public function verify()
    {
        if (empty($this->value['phone'])) exit(\Response::json(PARAM_FAIL, '手机号不能为空'));
        if (empty($this->value['type'])) exit(\Response::json(PARAM_FAIL, '请选择发送类型'));
        $time = time();
        $code = build_verify(6, 'num');
        if ($this->value['type'] == 1) {
            $sms = 'SMS_86595035';
            $uid = \think\Db::name('user')->where('phone', $this->value['phone'])->value('uid');
            if ($uid) exit(\Response::json(FAIL, '该手机号已经注册'));
            $verify = \think\Db::name('code')->where('phone', $this->value['phone'])->order('id', 'desc')->limit(1)->field('code,time')->find();
            if ($verify['time'] + 120 > $time) exit(\Response::json(FAIL, '验证码已经发送！请勿重复发送！'));
        } elseif ($this->value['type'] == 2) {
            $sms = 'SMS_86510025';
            if (!\think\Db::name('user')->where('phone', $this->value['phone'])->value('uid')) exit(\Response::json(FAIL, '该手机号还未注册，请先注册'));
        } else {
            exit(\Response::json(FAIL, '短信类型不正确'));
        }
        Loader::import('SmsDemo', EXTEND_PATH);
        $demo = new \SmsDemo(config('alidayu_apikey'), config('alidayu_apisecret'));
        $response = $demo->sendSms(
            "投资平台", // 短信签名
            $sms, // 短信模板编号
            $this->value['phone'], // 短信接收者
            Array(  // 短信模板中字段的值
                "code" => $code,
                "product" => "投资平台服务"
            )
        );
        if (collection($response)->toArray()['Code'] == "OK") {
            $data = ['phone' => $this->value['phone'], 'code' => $code, 'time' => $time];
            if (Db::name('code')->insert($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        }
        exit(\Response::json(FAIL, SERVICE_WRONG));

    }

    /**
     * @param phone [手机]
     * @param password [密码]
     * @param verity [验证码]
     * @return [忘记密码]
     */
    public function forget()
    {
        $user_model = new User();
        if (empty($this->value['phone'])) exit(\Response::json(PARAM_FAIL, '手机号不能为空'));
        if (empty($user_model->where('phone', $this->value['phone'])->value('uid'))) exit(\Response::json(FAIL, '手机号不存在'));
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '密码不能为空'));
        if (empty($this->value['verity'])) exit(\Response::json(PARAM_FAIL, '验证码不能为空'));
        $verify = Db::name('code')->where('phone', $this->value['phone'])->order('id', 'desc')->limit(1)->find();
        if ($verify['time'] + 900 < time()) exit(\Response::json(FAIL, '验证码超过有效期，请重新获取'));
        if ($this->value['verity'] != $verify['code']) exit(\Response::json(FAIL, '验证码不正确!'));
        $data = $user_model->forgetPass($this->value);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param old_password [老密码]
     * @param password [新密码]
     * @return [修改密码]
     */
    public function change()
    {
        if (empty($this->value['old_password'])) exit(\Response::json(PARAM_FAIL, '请输入原密码'));
        $user_model = new User();
        if (empty($user_model->where('uid', $this->value['uid'])->where('password', md5($this->value['old_password']))->value('uid'))) exit(\Response::json(FAIL, '原密码不正确'));
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '请输入新密码'));

        $data = $user_model->changePass($this->value);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param content [反馈内容]
     * @param img [反馈图片，没有不用上传]
     * @return [反馈意见]
     */
    public function feedback()
    {
        if (empty($this->value['content'])) exit(\Response::json(PARAM_FAIL, '请输入反馈意见'));
        $img = request()->file('img');
        if ($img) {
            $this->value['img'] = uploadImgMore($img, 'feedback');
        }
        $this->value['add_time'] = time();
        $data = Db::name('feedback')->insert($this->value);
        if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @return [联系客服]
     */
    public function contact()
    {
        exit(\Response::json(SUCCESS, SUCCESS_MSG, ['phone' => '110']));
    }

    /**
     * @param token
     * @param niname [名字]
     * @param qq [qq号]
     * @param wechat [微信号]
     * @param email [邮箱]
     * @return [编辑资料]
     */
    public function editData()
    {
        if (empty($this->value['niname'])) exit(\Response::json(PARAM_FAIL, '名字不能为空'));
        $user = new User();
        $data = $user->allowField(true)->save($this->value, ['uid' => $this->value['uid']]);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @return [资料详细]
     */
    public function detailData()
    {
        $user_model = new User();
        $data = $user_model->where('uid', $this->value['uid'])->field('niname,qq,wechat,email')->find();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @return [认证投资人说明]
     */
    public function investorTip()
    {
        $data = Db::name('tip')->field('investor_tip')->find()->toArray();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @return [认证发起人说明]
     */
    public function initiatorTip()
    {
        $data = Db::name('tip')->field('initiator_tip')->find()->toArray();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @return [投资人认证的详细]
     */
    public function investorAuthenticationDetail()
    {
        $investor_model = new Investor();
        $data = $investor_model->where('uid', $this->value['uid'])->find()->toArray();
//        $data['idcard'] = strlen($data['idcard']) == 15 ? substr_replace($data['idcard'], "****", 8, 4) : (strlen($data['idcard']) == 18 ? substr_replace($data['idcard'], "****", 10, 4) : "身份证位数不正常！");
        $data['invest_intention'] = ProjectCategory::all($data['invest_intention']);
        $data['invest_address'] = City::all($data['invest_address']);
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param investor_id [投资者id]
     * @param true_img [真实头像]
     * @param name [姓名]
     * @param idcard [身份证号码]
     * @param email [邮箱]
     * @param idimg [手持身份证]
     * @param idcard_img [身份证正面照]
     * @param company_name [公司名称]
     * @param company_job [公司职位]
     * @param card [名片]
     * @return [投资人认证第一步]
     */
    public function investorAuthenticationOne()
    {
        //如果上传了投资者id，就代表更新
        if (!empty($this->value['investor_id'])) {
            $true_img = request()->file('true_img');
            if (!empty($true_img)) $this->value['true_img'] = uploadImgOne($true_img, 'face');
            $idcard_img = request()->file('idcard_img');
            if (!empty($idcard_img)) $this->value['idcard_img'] = uploadImgWatermark($idcard_img, 'idcard');
            $idimg = request()->file('idimg');
            if (!empty($idimg)) $this->value['idimg'] = uploadImgWatermark($idimg, 'idcard');
            $card = request()->file('card');
            if (!empty($card)) $this->value['card'] = uploadImgWatermark($card, 'idcard');
            $investor_model = new Investor();
            $data = $investor_model->allowField(true)->save($this->value, ['iid' => $this->value['investor_id']]);
            if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
            exit(\Response::json(FAIL, SERVICE_WRONG));
        }
        //没有就代表新增
        if (Investor::checkIsInvestor($this->value['uid'])) exit(\Response::json(FAIL, '您已经认证过了投资人'));
        $true_img = request()->file('true_img');
        if (empty($true_img)) exit(\Response::json(PARAM_FAIL, '请上传您的真实头像'));
        $this->value['true_img'] = uploadImgOne($true_img, 'face');
        $idcard_img = request()->file('idcard_img');
        if (empty($idcard_img)) exit(\Response::json(PARAM_FAIL, '请上传您的身份证正面照'));
        $this->value['idcard_img'] = uploadImgWatermark($idcard_img, 'idcard');
        $idimg = request()->file('idimg');
        if (empty($idimg)) exit(\Response::json(PARAM_FAIL, '请上传您的手持身份证照'));
        $this->value['idimg'] = uploadImgWatermark($idimg, 'idcard');
        $card = request()->file('card');
        if (empty($card)) exit(\Response::json(PARAM_FAIL, '请上传您的名片照'));
        $this->value['card'] = uploadImgWatermark($card, 'idcard');
        $this->value['add_time'] = time();
        $investor_model = new Investor($this->value);
        if ($investor_model->allowField(true)->save()) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(SUCCESS, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param bank_card [银行卡存款证明]
     * @param fixation_money [固定资产证明]
     * @param self_money [个人资产证明]
     * @return [投资人认证第二步]
     */
    public function investorAuthenticationTwo()
    {
        if (!Investor::checkIsInvestor($this->value['uid'])) exit(\Response::json(FAIL, '您还没认证过投资人,请从第一步开始'));
        $bank_card = request()->file('bank_card');
        if (!empty($bank_card)) $this->value['bank_card'] = uploadImgWatermark($bank_card);
        $fixation_money = request()->file('fixation_money');
        if (!empty($fixation_money)) $this->value['fixation_money'] = uploadImgWatermark($fixation_money);
        $self_money = request()->file('self_money');
        if (!empty($self_money)) $this->value['self_money'] = uploadImgWatermark($self_money);
        $investor_model = new Investor();
        $data = $investor_model->allowField(true)->save($this->value, ['uid' => $this->value['uid']]);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param invest_intention [投资意向]
     * @param invest_address [投资地区]
     * @param money_top [投资上限]
     * @param money_down [投资下限]
     * @param invest_experience [投资经历]
     * @return [投资人认证最后一步]
     */
    public function investorAuthenticationEnd()
    {
        if (!Investor::checkIsInvestor($this->value['uid'])) exit(\Response::json(FAIL, '您还没认证过投资人,请从第一步开始'));
        $investor_model = new Investor();
        $investor_id = $investor_model->where('uid', $this->value['uid'])->value('iid');
        $invest_intention = explode(',', $this->value['invest_intention']);
        foreach ($invest_intention as $i) {
            Db::name('categoryRelation')->insert(['investor_id' => $investor_id, 'category_id' => $i]);
        }
        $invest_address = explode(',', $this->value['invest_address']);
        foreach ($invest_address as $a) {
            Db::name('cityRelation')->insert(['investor_id' => $investor_id, 'city_id' => $a]);
        }
        $this->value['status'] = -1;//状态改为待审核
        $data = $investor_model->allowField(true)->save($this->value, ['uid' => $this->value['uid']]);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @return [项目发起人认证]
     */
    public function promulgatorAuthenticationDetail()
    {
        $promulgator_model = new Promulgator();
        $data = $promulgator_model->where('uid', $this->value['uid'])->find();
        if (empty($data)) exit(\Response::json(FAIL, '你还未认证项目发起人'));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param promulgator_id [项目者id]
     * @param name [名字]
     * @param idcard [身份证]
     * @param idimg [手持身份证]
     * @param idcard_img [身份证正面照]
     * @param company_name [公司名称]
     * @param company_job [公司职位]
     * @param card [名片]
     * @param business_license [营业执照]
     * @return [项目发起人认证]
     */
    public function promulgatorAuthentication()
    {
        $promulgator_model = new Promulgator();
        $idcard_img = request()->file('idcard_img');
        $idimg = request()->file('idimg');
        $card = request()->file('card');
        $business_license = request()->file('business_license');
        if (!empty($this->value['promulgator_id'])) {
            if (!empty($idcard_img)) $this->value['idcard_img'] = uploadImgWatermark($idcard_img, 'idcard');
            if (!empty($idimg)) $this->value['idimg'] = uploadImgWatermark($idimg, 'idcard');
            if (!empty($card)) $this->value['card'] = uploadImgWatermark($card, 'idcard');
            if (!empty($business_license)) $this->value['business_license'] = uploadImgWatermark($business_license, 'idcard');
            $this->value['status'] = -1;
            $promulgator_model = new Promulgator();
            $data = $promulgator_model->allowField(true)->save($this->value, ['iid' => $this->value['promulgator_id']]);
            if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG));
            exit(\Response::json(SUCCESS, SERVICE_WRONG));
        }
        $promulgator_data = $promulgator_model->where('uid', $this->value['uid'])->field('iid,status')->find();
        if ($promulgator_data['iid'] && $promulgator_data['status'] != 2) exit(\Response::json(FAIL, '您已经认证过了项目发起人'));
        if (empty($idcard_img)) exit(\Response::json(PARAM_FAIL, '请上传您的身份证正面照'));
        if (empty($idimg)) exit(\Response::json(PARAM_FAIL, '请上传您的手持身份证照'));
        if (empty($card)) exit(\Response::json(PARAM_FAIL, '请上传您的名片照'));
        if (empty($business_license)) exit(\Response::json(PARAM_FAIL, '请上传您的营业执照'));
        $this->value['idcard_img'] = uploadImgWatermark($idcard_img, 'idcard');
        $this->value['idimg'] = uploadImgWatermark($idimg, 'idcard');
        $this->value['card'] = uploadImgWatermark($card, 'idcard');
        $this->value['business_license'] = uploadImgWatermark($business_license, 'idcard');

//        if (!empty($idcard_img)) $this->value['idcard_img'] = uploadImgWatermark($idcard_img, 'idcard');
//        if (!empty($idimg)) $this->value['idimg'] = uploadImgWatermark($idimg, 'idcard');
//        if (!empty($card)) $this->value['card'] = uploadImgWatermark($card, 'idcard');
//        if (!empty($business_license)) uploadImgWatermark($business_license, 'idcard');
        $this->value['add_time'] = time();
        $this->value['status'] = -1;
        $promulgator_model = new Promulgator($this->value);
        if ($promulgator_model->allowField(true)->save()) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(SUCCESS, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param name [项目名称]
     * @param logo [项目logo]
     * @param ptype [项目状态]
     * @param slogan [项目口号]
     * @param intro [项目简介]
     * @param content [项目介绍]
     * @param pcid [项目行业分类]
     * @param best_pcid [第一级的行业分类]
     * @param address [项目发展区域]
     * @param best_address [第一级项目地址]
     * @param money [项目金额]
     * @param img_list [项目图片]
     * @param video [视频]
     * @return [发布项目第一步]
     */
    public function releaseProjectOne()
    {
        if (!Promulgator::checkIsPromulgator($this->value['uid'])) exit(\Response::json(FAIL, '您还没有通过项目认证'));
        if (!is_numeric($this->value['money'])) exit(\Response::json(PARAM_FAIL, '项目金额只能为数字'));
        $logo = request()->file('logo');
        $img = request()->file('img_list');
        $video = request()->file('video');
        if (empty($logo)) exit(\Response::json(PARAM_FAIL, '请上传logo图片'));
        if (empty($img)) exit(\Response::json(PARAM_FAIL, '请上传项目图片'));
        if (empty($video)) exit(\Response::json(PARAM_FAIL, '请上传项目视频'));
        $this->value['logo'] = uploadImgOne($logo, 'logo');
        $this->value['img_list'] = '';
        foreach ($img as $i) {
            $info = $i->validate(['size' => 1565578, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/upload/project');
            if ($info) {
                $file_name = "/upload/project/" . $info->getSaveName();
                if (addImgLogo('.' . $file_name, $this->getLogoUrl())) {
                    $this->value['img_list'] .= IMG_PATH . $file_name . ",";
                } else {
                    exit(\Response::json(FAIL, SERVICE_WRONG));
                }
            } else {
                exit(\Response::json(FAIL, $i->getError()));
            }
        }

        $this->value['video'] = uploadVideo($video);
        $project_model = new Project($this->value);
        if ($project_model->allowField(true)->save()) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $project_model->pid]));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param type [1-未通过修改 2-通过修改]
     * @param project_id [项目id]
     * @param img_list [原图片地址]
     * @param now_img_list [修改的图片 表单提交]
     */
    public function projectEditOne()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
        $project_model = new Project();
        $status = $project_model->where('pid', $this->value['project_id'])->value('status');
        if ($this->value['type'] == 1) {
            if ($status != 4 && $status != 1) exit(\Response::json(FAIL, '状态错误，无法修改1'));
        } else {
            if ($status != 3) exit(\Response::json(FAIL, '状态错误，无法修改2'));
        }
        $logo = request()->file('logo');
        if (!empty($logo)) $this->value['logo'] = uploadImgOne($logo, 'logo');
        $images = request()->file('now_img_list');
        if (!empty($images)) {
            foreach ($images as $i) {
                $info = $i->validate(['size' => 1565578, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/upload/project');
                if ($info) {
                    $file_name = "/upload/project/" . $info->getSaveName();
                    if (addImgLogo('.' . $file_name, $this->getLogoUrl())) {
                        $this->value['img_list'] .= IMG_PATH . $file_name . ",";
                    } else {
                        exit(\Response::json(FAIL, SERVICE_WRONG));
                    }
                } else {
                    exit(\Response::json(FAIL, $i->getError()));
                }
            }
        }
        $video = request()->file('video');
        if (!empty($video)) $this->value['video'] = uploadVideo($video);
        if ($this->value['type'] == 1) {//修改 未通过的
            $data = $project_model->allowField(true)->save($this->value, ['pid' => $this->value['project_id']]);
            if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $this->value['project_id']]));
        } else {//修改
            $this->value['pro_id'] = $this->value['project_id'];
            $project_model = new Project($this->value);
            $data = $project_model->allowField(true)->save();
            if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $project_model->pid]));
        }
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }


    /**
     * @param token
     * @param project_id [项目id]
     * @param member [未删除成员id,多个用，分割]
     * @param member_id [被删除的成员id]
     * @return [发布项目第二步]
     */
    public function releaseProjectTwo()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
//        if (empty($this->value['member'])) exit(\Response::json(PARAM_FAIL, '未删除成员id为空'));
        if (empty($this->value['member_id'])) exit(\Response::json(PARAM_FAIL, '被删除的成员id为空'));
        Db::startTrans();
        $flag = true;
        $project_model = Project::get($this->value['project_id']);
        $project_model->member = $this->value['member'];
        if ($project_model->save()) {
            $projectMember_model = new ProjectMember();
            $work = $projectMember_model->where('mid', $this->value['member_id'])->value('work');
            if (!empty($work)) {
                $wid = substr($work, 0, strlen($work) - 1);
                if (!Db::name('memberWork')->where('wid', 'in', $wid)->delete()) {
                    $flag = false;
                }
            }
            if (!$projectMember_model->where('mid', $this->value['member_id'])->delete()) {
                $flag = false;
            }
        } else {
            $flag = false;
        }
        if ($flag) {
            Db::commit();
            exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $this->value['project_id']]));
        }
        Db::rollback();
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param project_id [项目id]
     * @param head_img [头像]
     * @param name [名字]
     * @param position [项目职位]
     * @param school [学校]
     * @param specialty [专业]
     * @param education [学历]
     * @param start_time [学历开始时间]
     * @param end_time [学历结束时间]
     * @param company [公司名字]
     * @param job [工作职位]
     * @param start_time [工作经历开始时间]
     * @param end_time [工作经历结束时间]
     * @param work_data [工作经历拼装json]
     * @param intro [介绍]
     */
    public function projectAddMember()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
        $memberWork_model = new MemberWork();
        $projectMember_model = new ProjectMember();
        $head_img = request()->file('head_img');
        if (empty($head_img)) exit(\Response::json(PARAM_FAIL, '请上传头像'));
        $this->value['head_img'] = uploadImgOne($head_img, 'face');

        if (!empty($this->value['work_data'])) {
            $work_data = json_decode($this->value['work_data'], true);
            $this->value['work'] = '';
            foreach ($work_data as $w) {
                $this->value['work'] .= $memberWork_model->addMemberWork($w) . ",";
            }
        }
        $data = $projectMember_model->addProjectMember($this->value);
        $project_model = Project::get($this->value['project_id']);
        $project_model->member = $project_model->member . $data . ",";
        if ($project_model->save()) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['mid' => $data]));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param project_id [项目id]
     * @param company_name [公司名称]
     * @param company_address [公司地址]
     * @param company_contacts [联系人名字]
     * @param company_tel [联系人号码]
     * @param company_license [营业执照]
     * @param company_idimg [法人身份证正面]
     * @param company_hand_idimg [手持身份证正面]
     * @return [编辑项目第三步]
     */
    public function projectEditThree()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
        $company_license = request()->file('company_license');
        if (!empty($company_license)) $this->value['company_license'] = uploadImgOne($company_license);
        $company_idimg = request()->file('company_idimg');
        if (!empty($company_idimg)) $this->value['company_idimg'] = uploadImgOne($company_idimg);
        $company_hand_idimg = request()->file('company_hand_idimg');
        if (!empty($company_hand_idimg)) $this->value['company_hand_idimg'] = uploadImgOne($company_hand_idimg);
        $project_model = new Project();
        $data = $project_model->allowField(true)->save($this->value, ['pid' => $this->value['project_id']]);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $this->value['project_id']]));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param project_id [项目id]
     * @param company_name [公司名称]
     * @param company_address [公司地址]
     * @param company_contacts [联系人名字]
     * @param company_tel [联系人号码]
     * @param company_license [营业执照]
     * @param company_idimg [法人身份证正面]
     * @param company_hand_idimg [手持身份证正面]
     * @return [发布项目第三步]
     */
    public function releaseProjectThree()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
        $company_license = request()->file('company_license');
//        if (empty($company_license)) exit(\Response::json(PARAM_FAIL, '请上传营业执照'));
        $company_idimg = request()->file('company_idimg');
//        if (empty($company_idimg)) exit(\Response::json(PARAM_FAIL, '请上传法人身份证正面'));
        $company_hand_idimg = request()->file('company_hand_idimg');
//        if (empty($company_hand_idimg)) exit(\Response::json(PARAM_FAIL, '请上传手持身份证正面'));
        if (!empty($company_license)) $this->value['company_license'] = uploadImgOne($company_license);
        if (!empty($company_idimg)) $this->value['company_idimg'] = uploadImgOne($company_idimg);
        if (!empty($company_hand_idimg)) $this->value['company_hand_idimg'] = uploadImgOne($company_hand_idimg);
        $project_model = new Project();
        $data = $project_model->allowField(true)->save($this->value, ['pid' => $this->value['project_id']]);
        if ($data || $data === 0) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['project_id' => $this->value['project_id']]));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param project_id [项目id]
     * @return [发布项目]
     */
    public function releaseProjectEnd()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '项目id不能为空'));
        $project = new Project;
        if ($project->save(['status' => 2, 'add_time' => time()], ['pid' => $this->value['project_id']])) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param project_id
     * @return [项目编辑详情第一步]
     */
    public function projectDetailOne()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '请选择项目'));
        $project_model = new Project();
        $data = $project_model->where('pid', $this->value['project_id'])->field('pcid,address,name,intro,content,logo,money,img_list,video,ptype,slogan')->find();
        $data['ptype'] = ['type' => $data['ptype'], 'name' => getPType($data['ptype'])];
        $data['pcid'] = ProjectCategory::all($data['pcid']);
        $data['address'] = City::all($data['address']);
        if (empty($data)) exit(\Response::json(FAIL, '该项目不存在'));
        $img_list = explode(',', $data['img_list']);
        array_pop($img_list);
        $data['img_list'] = $img_list;
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param project_id
     * @return [项目编辑详情第二步]
     */
    public function projectDetailTwo()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '请选择项目'));
        $project_model = new Project();
        $member = $project_model->where('pid', $this->value['project_id'])->value('member');
        if (empty($member)) exit(\Response::json(SUCCESS, SUCCESS_MSG, []));
        $member_data = ProjectMember::all(substr($member, 0, strlen($member) - 1));
        $data = [];
        foreach ($member_data as $m) {
            if (empty($m['work'])) {
                $m['work'] = [];
            } else {
                $m['work'] = MemberWork::all(substr($m['work'], 0, strlen($m['work']) - 1));
            }
            $data[] = $m;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param project_id
     * @return [项目编辑详情第三步]
     */
    public function projectDetailThree()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '请选择项目'));
        $project_model = new Project();
        $data = $project_model->where('pid', $this->value['project_id'])->field('company_name,company_address,company_license,company_idimg,company_hand_idimg,company_contacts,company_tel')->find();
        if (empty($data)) exit(\Response::json(FAIL, '该项目不存在'));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param p [分页码]
     * @return [我的项目列表]
     */
    public function projectList()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请上传分页页码'));
        $project_model = new Project();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getProjectList($this->value['uid'], $this->value['p'])));
    }

    /**
     * @param token
     * @return [投资意向]
     */
    public function investmentIntention()
    {
        $projectCategory_model = new ProjectCategory();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $projectCategory_model->getCategoryList()));
    }

    /**
     * @param token
     * @return [投资地区]
     */
    public function investmentAddress()
    {
        if (Cache::get('city')) exit(\Response::json(SUCCESS, SUCCESS_MSG, Cache::get('city')));
        $city = new City();
        $province = $city->where('parent_id', 0)->field('id,parent_id,name')->select();
        $arr = [];
        foreach ($province as $p) {
            $p['city'] = $city->where('parent_id', $p['id'])->field('id,parent_id,name')->select();
            $arr[] = $p;
        }
        Cache::set('city', $arr);
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $arr));
    }

    public function investmentStatus()
    {
        // 1：概念阶段，2：研发阶段，3：启动阶段，4：运营阶段，5：盈利阶段

        $data = ['1' => '概念阶段', '2' => '研发阶段', '3' => '启动阶段', '4' => '运营阶段', '5' => '盈利阶段'];
        $arr = $res = [];
        foreach ($data as $k => $v) {
            $arr['id'] = $k;
            $arr['name'] = $v;
            $res[] = $arr;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $res));
    }

    /**
     * token
     * @param time [发布时间1-发布时间 2-项目金额递减 3-项目金额递增]
     * @param status [状态1-概念阶段 2-研发阶段 3-启动阶段 4-运营阶段 5-盈利阶段]
     * @param industry [行业]
     * @param address [地区]
     * @param keyword [关键字]
     * @param p [分页页码]
     * @return [首页列表]
     */
    public function homeProjectList()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请上传分页页码'));
        $project_model = new Project();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getHomeProjectList($this->value)));
    }

    /**
     * @return [获取第一级行业]
     */
    public function categoryOne()
    {
        $projectCategory_model = new ProjectCategory();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $projectCategory_model->getCateGoryOne()));
    }

    /**
     * @return [获取第一级地址]
     */
    public function addressOne()
    {
        $city = new City();
        $data = $city->where('parent_id', 0)->field('id,name')->select();
        $arr[0] = ['id' => 0, 'name' => '全国'];
        $res = array_merge($arr, collection($data)->toArray());
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $res));
    }

    /**
     * @param token
     * @param project_id [项目id]
     * @return [项目详情]
     */
    public function projectDetail()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '请上传项目id'));
        $project_model = new Project();
        $browse_model = new Browse();
        $browse_model->addBrowseNum($this->value);
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getProjectDetail($this->value)));
    }

    /**
     * @param token
     * @param pid [项目id]
     * @return [收藏按钮]
     */
    public function collect()
    {
        if (empty($this->value['pid'])) exit(\Response::json(PARAM_FAIL, '请上传项目id'));
        $projectCollect_model = new ProjectCollect();
        if ($projectCollect_model->collectProject($this->value)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @return [是否为认证者  0：未认证，1：投资者，2：项目者 3：两者都是]
     */
    public function isCertification()
    {
        $user_model = new User();
        $data['type'] = $user_model->where('uid', $this->value['uid'])->value('type');
        $draft = Db::name('project')->where('status', 1)->where('pro_id', 0)->where('uid', $this->value['uid'])->value('pid');
        $promulgator_model = new Promulgator();
        $promulgator_data = $promulgator_model->where('uid', $this->value['uid'])->field('status')->find();
        if (!empty($promulgator_data)) {
            $data['promulgator_status'] = $promulgator_data['status'];//状态 -1 待审核0：草稿，1：通过，2：拒绝
        } else {
            $data['promulgator_status'] = -2;//项目状态-2没有草稿
        }
        if (!empty($draft)) {
            $data['status'] = $draft;
        } else {
            $data['status'] = 0;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param project_id
     * @param type [1-索要项目计划书 2-索要联系方式]
     * @return [项目计划书]
     */
    public function askProject()
    {
        if (empty($this->value['project_id'])) exit(\Response::json(PARAM_FAIL, '请选择项目'));
        if (empty($this->value['type'])) exit(\Response::json(PARAM_FAIL, '请上传type类型'));
        $user_data = User::get($this->value['uid']);
        if ($user_data['type'] != 1 && $user_data['type'] != 3) {
            $investor_model = new Investor();
            $investor_status = $investor_model->where('uid', $this->value['uid'])->value('status');
            if (empty($investor_status)) {
                $investor_status = -2;//状态-2没有草稿 -1待审核  0：草稿，1：通过，2：拒绝
            }
            exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 1, 'investor_status' => $investor_status]));
        }
        if (Db::name('projectAsk')->where('pid', $this->value['project_id'])->where('uid', $this->value['uid'])->where('type', $this->value['type'])->value('paid')) {
            if ($this->value['type'] == 1) exit(\Response::json(FAIL, '您已经索要过了计划书！'));
            exit(\Response::json(FAIL, '您已经索要过了手机号码'));
        }
        $user_id = Db::name('project')->where('pid', $this->value['project_id'])->value('uid');
        $data = ['pid' => $this->value['project_id'], 'uid' => $this->value['uid'], 'type' => $this->value['type'], 'add_time' => time(), 'user_id' => $user_id];
        if (Db::name('projectAsk')->insert($data)) {
            //推送
            $client = new Client(config('jpush_key'), config('master_secret'));
            $pusher = $client->push();
            $pusher->setPlatform('all');
            $pusher->addAlias(md5($user_id));
            $pusher->setNotificationAlert($user_data['niname'] . "对您的项目感兴趣");
            try {
                $pusher->send();
            } catch (\JPush\Exceptions\JPushException $e) {
                // try something else here
//                exit(\Response::json(FAIL, $e));
//                print $e;
            }
            if ($this->value['type'] == 1) exit(\Response::json(SUCCESS, '您已经索要成功，我们会把计划书发到您的认证邮箱', ['status' => 2]));
            if ($this->value['type'] == 2) exit(\Response::json(SUCCESS, '您已经索要成功，我们会在24小时内与您联系', ['status' => 2]));
        }
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param tip [investor_tip-首次认证投资人tips initiator_tip-首次认证发起人tip investor-投资人认证 initiator-发起人认证 about-关于我们 notice-发布须知 project_detail-首次查看弹框提示API user_agreement-用户协议]
     */
    public function tips()
    {
        $arr = ['investor_tip', 'initiator_tip', 'investor', 'initiator', 'about', 'notice', 'project_detail', 'user_agreement'];
        if (!in_array($this->value['tip'], $arr)) exit(\Response::json(PARAM_FAIL, '请选择正确的类型'));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, ['tip' => $data = Db::name('tip')->value($this->value['tip'])]));
    }

    /**
     * @param token
     * @param p [分页]
     * @param intention [投资意向]
     * @param address [投资地区]
     * @return [投资人]
     */
    public function investorList()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '没上传分页'));
        $investor_model = new Investor();
        $data['investor_list'] = $investor_model->getInvestorList($this->value);
        $data['img'] = Db::name('slideshow')->where('id', 1)->field('img,url')->find();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param investor_id [投资人id]
     */
    public function investorDetail()
    {
        if (empty($this->value['investor_id'])) exit(\Response::json(PARAM_FAIL, '请选择要查看的投资人'));
        $investor_model = new Investor();
        $browser_model = new Browse();
        if (!$browser_model->addBrowseNumInvestor($this->value)) exit(\Response::json(FAIL, SERVICE_WRONG));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $investor_model->getInvestorDetail($this->value['investor_id'])));
    }

    /**
     * @param token
     * @param investor_id [投资人id]
     * @return [增加投资人的浏览]
     */
    public function addBrowserInvestor()
    {
        if (empty($this->value['investor_id'])) exit(\Response::json(PARAM_FAIL, '请选择您要查看的投资人'));
        $browser_model = new Browse();
        if ($browser_model->addBrowseNumInvestor($this->value)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param pid [项目id,多个用逗号隔开 末尾不加逗号]
     * @param user_id [投资人的id]
     * @return [投递项目]
     */
    public function sendProject()
    {
        $this->value['add_time'] = time();
        $user_data = User::all($this->value['uid']);
        unset($this->value['uid']);
        $pid = explode(',', $this->value['pid']);
        Db::startTrans();
        $flag = true;
        foreach ($pid as $id) {
            $this->value['pid'] = $id;
            if (Db::name('projectSend')->where(['pid' => $id, 'user_id' => $this->value['user_id']])->value('psid')) {
                Db::rollback();
                exit(\Response::json(FAIL, '您已经向这个投资者投递过这个项目'));
            }
            $data = Db::name('projectSend')->insert($this->value);
            if (!$data) $flag = false;
        }
        if ($flag) {
            //推送
            $client = new Client(config('jpush_key'), config('master_secret'));
            $pusher = $client->push();
            $pusher->setPlatform('all');
            $pusher->addAlias(md5($this->value['user_id']));
            $pusher->setNotificationAlert($user_data['niname'] . "向您投递了项目");
            try {
                $pusher->send();
            } catch (\JPush\Exceptions\JPushException $e) {
                // try something else here
//                exit(\Response::json(FAIL, $e));
//                print $e;
            }
            Db::commit();
            exit(\Response::json(SUCCESS, SUCCESS_MSG));
        }
        Db::rollback();
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @return [轮播图]
     */
//    public function banner()
//    {
//        $data = Db::name('slideshow')->field('img,url')->select();
//        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
//    }

    /**
     * @param token
     * @param p
     * @return [我的投递项目列表]
     */
    public function sendProjectList()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, "请上传分页"));
        $project_model = new Project();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getSendProjectList($this->value)));
    }

    /**
     * @param token
     * @param type [1-项目浏览记录 2-投资者浏览记录]
     * @return [浏览记录]
     */
    public function browseList()
    {
        if (empty($this->value['type'])) exit(\Response::json(PARAM_FAIL, 'type不能为空'));
        $browse_model = new Browse();
        if ($this->value['type'] == 1) {//项目浏览记录
            $join = [['tpn_project p', 'p.pid=a.project_id']];
            $browse_project = $browse_model->alias('a')->where('a.uid', $this->value['uid'])->where('a.type', 1)->join($join)->order('bid', 'desc')->field('a.bid,p.pid,p.name,p.logo,p.status,p.ptype')->limit(20)->select();
            if (empty($browse_project)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
//            $project_data = [];
            $project_model = new Project();
//            foreach ($browse_project as $b) {
//                $project_data[] = $b['project_id'];
//            }
            $data = $project_model->getBrowseProjectList($browse_project);

        } elseif ($this->value['type'] == 2) {//投资者浏览记录
            $join = [['tpn_investor i', 'i.iid=a.project_id']];
            $browse_investor = $browse_model->alias('a')->where('a.uid', $this->value['uid'])->where('a.type', 2)->join($join)->order('bid', 'desc')->field('a.bid,i.iid,i.uid,i.name,i.true_img,i.invest_intention,i.invest_address,i.money_top,i.money_down,i.invest_experience,i.status')->limit(20)->select();
            if (empty($browse_investor)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
            $investor_model = new Investor();
//            $investor_data = [];
//            foreach ($browse_investor as $b) {
//                $investor_data[] = $b['project_id'];
//            }
            $data = $investor_model->getBrowseInvestorList($browse_investor);
        } else {
            exit(\Response::json(PARAM_FAIL, '请选择正确的类型'));
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param bid [历史记录id]
     * @return [删除历史记录]
     */
    public function deleteBrowse()
    {
        if (empty($this->value['bid'])) exit(\Response::json(PARAM_FAIL, "请选择要删除的项目"));
        $browse_model = new Browse();
        if ($browse_model->where('bid', $this->value['bid'])->where('uid', $this->value['uid'])->delete()) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param p
     * @return [收藏的项目]
     */
    public function collectProject()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, "请上传分页"));
        $projectCollect_model = new ProjectCollect();
        $collect_data = $projectCollect_model->where('uid', $this->value['uid'])->where('status', 1)->order('psid desc')->page($this->value['p'], config('page'))->field('pid')->select();
        if (empty($collect_data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        $collect_id = [];
        $project_model = new Project();
        foreach ($collect_data as $c) {
            $collect_id[] = $c['pid'];
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getCollectProjectList($collect_id)));
    }


    /**
     * @param token
     * @param p
     * @return [获取收到的项目]
     */
    public function myGetProject()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, "请上传分页"));
        $sendData = Db::name('projectSend')->where('user_id', $this->value['uid'])->page($this->value['p'], config('page'))->field('pid')->order('psid desc')->select();
        if (empty($sendData)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        $send_id = '';
        foreach ($sendData as $s) {
            $send_id .= $s['pid'] . ",";
        }
        $project_model = new Project();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $project_model->getCollectProjectList(substr($send_id, 0, strlen($send_id) - 1))));
    }

    /**
     * @param token
     * @return [我的]
     */
    public function my()
    {
        $user_model = new User();
        $investor_model = new Investor();
        $promulgator_model = new Promulgator();
        $promulgator_data = $promulgator_model->where('uid', $this->value['uid'])->field('status')->find();
        $investor_data = $investor_model->where('uid', $this->value['uid'])->field('name,true_img,status')->find();
        if (!empty($investor_data)) {
            $data['investor_status'] = $investor_data['status'];//状态-1待审核  0：草稿，1：通过，2：拒绝
        } else {
            $data['investor_status'] = -2;//投资者状态-2没有草稿
        }
        if (!empty($promulgator_data)) {
            $data['promulgator_status'] = $promulgator_data['status'];//状态 -1 待审核0：草稿，1：通过，2：拒绝
        } else {
            $data['promulgator_status'] = -2;//项目状态-2没有草稿
        }
        $user_data = $user_model->where('uid', $this->value['uid'])->field('niname,type')->find()->toArray();
        if ($user_data['type'] == 1 || $user_data['type'] == 3) {
            $data['name'] = $investor_data['name'];
            $data['head_img'] = $investor_data['true_img'];
        } else {
            $data['name'] = $user_data['niname'];
            $data['head_img'] = '';
        }
        $data['type'] = $user_data['type'];
        $project_model = new Project();
        $data['project'] = $project_model->getOneProject($this->value['uid']);
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @return [资讯分类]
     */
    public function informationCategory()
    {
        $category = Db::name('newsCategory')->select();
        if (empty($category)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $category));
    }

    /**
     * @param token
     * @param p
     * @param ncid [资讯id]
     * @return [资讯]
     */
    public function information()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请上传分页'));
        if (empty($this->value['ncid'])) exit(\Response::json(PARAM_FAIL, '请上传资讯分类id'));
        $news_model = new News();
        $data = $news_model->getInformationByCategory($this->value);
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @return [资讯轮播]
     */
    public function informationSlide()
    {
        $data = Db::name('newsSlideshow')->field('img,url')->select();
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @return [消息]
     */
    public function messageOne()
    {
        $data['system_message'] = Db::name('message')->field('title,add_time')->order('mid desc')->limit(1)->find();
        $data['system_message']['add_time'] = date("m-d", $data['system_message']['add_time']);
        $data['system_message']['read'] = Db::name('user')->where('uid', $this->value['uid'])->count();
        $project_model = new Project();
        $uid = Db::name('projectAsk')->where('user_id', $this->value['uid'])->order('paid desc')->field('uid,add_time')->find();
        if (empty($uid)) {
            $data['ask_message'] = null;
        } else {
            $data['ask_message']['title'] = Db::name('investor')->where('uid', $uid['uid'])->value('name') . "对你的项目感兴趣";
            $data['ask_message']['add_time'] = date("m-d", $uid['add_time']);
            $data['ask_message']['read'] = Db::name('projectAsk')->where('user_id', $this->value['uid'])->where('read', 1)->count();
        }

        $pid = Db::name('projectSend')->where('user_id', $this->value['uid'])->order('psid desc')->field('pid,add_time')->find();
        if (empty($pid)) {
            $data['send_message'] = null;
        } else {
            $data['send_message']['title'] = $project_model->getUsername($pid['pid']) . "向你投递了一个项目";
            $data['send_message']['add_time'] = date("m-d", $pid['add_time']);
            $data['send_message']['read'] = Db::name('projectSend')->where('user_id', $this->value['uid'])->where('user_id', $this->value['uid'])->count();
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param p
     * @return [系统消息接口]
     */
    public function systemMessage()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请输入当前分页页数'));
        $data = Db::name('message')->order('mid desc')->field('title,content,img,add_time,url')->order('mid desc')->page($this->value['p'], config('page'))->select();
        if (empty($data)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        Db::name('user')->where('uid', $this->value['uid'])->update(['read_num' => 0]);
        $arr = [];
        foreach ($data as $v) {
            $v['add_time'] = date('m-d H:i', $v['add_time']);
            $arr[] = $v;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $arr));
    }

    /**
     * @param token
     * @param p
     * @return [索要消息]
     */
    public function askMessage()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请输入当前分页页数'));
        $uid = Db::name('projectAsk')->where('user_id', $this->value['uid'])->field('paid,uid,add_time')->order('paid desc')->page($this->value['p'], config('page'))->select();
        if (empty($uid)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        Db::name('projectAsk')->where('user_id', $this->value['uid'])->update(['read' => 2]);//设置已读
        $data = [];
        foreach ($uid as $u) {
            $u['message'] = Db::name('investor')->where('uid', $u['uid'])->value('name') . "对你的项目感兴趣";
            $u['uid'] = Db::name('investor')->where('uid', $u['uid'])->value('iid');
            $u['add_time'] = date("m-d H:i", $u['add_time']);
            $data[] = $u;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param p
     * @return [投递项目的消息]
     */
    public function sendMessage()
    {
        if (empty($this->value['p'])) exit(\Response::json(PARAM_FAIL, '请输入当前分页页数'));
        $pid = Db::name('projectSend')->where('user_id', $this->value['uid'])->field('psid,pid,add_time')->order('psid desc')->page($this->value['p'], config('page'))->select();
        if (empty($pid)) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        Db::name('projectSend')->where('user_id', $this->value['uid'])->update(['read' => 2]);//设置已读
        $data = [];
        $project_model = new Project();
        foreach ($pid as $p) {
            $p['message'] = $project_model->getUsername($p['pid']) . "向你投递了一个项目";
            $p['add_time'] = date("m-d H:i", $p['add_time']);
            $data[] = $p;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param password
     * @return [检测手机号密码 1-没有手机号 2-没有密码 3-不通过 4-和前面相反]
     */
    public function checkPass()
    {
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '请输入密码'));
        $user_model = new User();
        $user_data = $user_model->where('uid', $this->value['uid'])->field('phone,password')->find();
//        if (empty($user_data['phone'])) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 1]));
//        if (empty($user_data['password'])) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 2]));
        if ($user_data['password'] == md5($this->value['password'])) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 1]));
        exit(\Response::json(SUCCESS, SUCCESS, ['status' => 2]));
    }

    /**
     * @param token
     * @return [tel 1-未设置 2-已设置 pass 1-未设置 2-已设置]
     */
    public function checkTelAndPass()
    {
        $user_model = new User();
        $user_data = $user_model->where('uid', $this->value['uid'])->field('phone,password')->find();
        if (empty($user_data['phone'])) {
            $data['tel'] = 1;
        } else {
            $data['tel'] = 2;
        }
        if (empty($user_data['password'])) {
            $data['pass'] = 1;
        } else {
            $data['pass'] = 2;
        }
        exit(\Response::json(SUCCESS, SUCCESS_MSG, $data));
    }

    /**
     * @param token
     * @param password
     * @param confirm_pass
     * @return [设置密码]
     */
    public function setPass()
    {
        if (empty($this->value['password'])) exit(\Response::json(PARAM_FAIL, '请输入密码'));
        if ($this->value['password'] != $this->value['confirm_pass']) exit(\Response::json(PARAM_FAIL, '两次输入的密码不一致'));
        $data = Db::name('user')->where('uid', $this->value['uid'])->update(['password' => md5($this->value['password'])]);
        if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * @param token
     * @param phone [手机号]
     * @param verify [验证码]
     * @return [设置手机号]
     */
    public function setPhone()
    {
        $user_model = new User();
        if (empty($this->value['phone'])) exit(\Response::json(PARAM_FAIL, '请输入手机号'));
        if ($user_model->where('phone', $this->value['phone'])->value('uid')) exit(\Response::json(FAIL, '手机号已经存在'));
        if (empty($this->value['verify'])) exit(\Response::json(PARAM_FAIL, '请输入验证码'));
        $verify = Db::name('code')->where('phone', $this->value['phone'])->order('id', 'desc')->limit(1)->find();
        if ($verify['time'] + 900 < time()) exit(\Response::json(FAIL, '验证码超过有效期，请重新获取'));
        if ($this->value['verify'] != $verify['code']) exit(\Response::json(FAIL, '验证码不正确!'));
        if ($user_model->save(['phone' => $this->value['phone']], ['uid' => $this->value['uid']])) exit(\Response::json(SUCCESS, SUCCESS_MSG));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    /**
     * niname [名字]
     * source [1-qq 2-微信 3-支付宝]
     * openid
     */
    public function thirdLogin()
    {
//        dump($this->value);die;
        $user_model = new User();
        $user_id = $user_model->where('openid', $this->value['openid'])->value('uid');
        if (empty($user_id)) {//如果不存在该第三方用户 就注册
            $this->value['add_time'] = time();
            $this->value['login_time'] = time();
            $data = $user_model->insert($this->value);
            if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['token' => md5($data . time()), 'unique' => md5($data)]));
        }
        //如果存在 则登录
        $token = md5($this->value['openid'] . time());
        $data = $user_model->save(['logintoken' => $token, 'login_time' => time()], ['uid' => $user_id]);
        if ($data) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['token' => $token, 'unique' => md5($user_id)]));
        exit(\Response::json(FAIL, SERVICE_WRONG));
    }

    public function versionUpdate()
    {
        exit(\Response::json(SUCCESS, SUCCESS_MSG, ['url' => config('url'), 'version' => config('version')]));
    }

    /**
     * @param token
     * @return [消息提醒 status 1-有消息 2-没有消息]
     */
    public function messageReminder()
    {
        $flag = false;
        if (Db::name('user')->where('uid', $this->value['uid'])->value('read_num')) $flag = true;
        if (Db::name('projectSend')->where('user_id', $this->value['uid'])->where('read', 1)->count()) $flag = true;
        if (Db::name('projectAsk')->where('user_id', $this->value['uid'])->where('read', 1)->count()) $flag = true;
        if ($flag) exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 1]));
        exit(\Response::json(SUCCESS, SUCCESS_MSG, ['status' => 2]));
    }
}