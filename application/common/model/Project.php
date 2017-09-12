<?php
/**
 * [项目列表模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 15:11:15
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class Project extends CommonModel{
    public $err;
    public $data;
    public $pk = 'pid';

    /**
     * [checkData 验证数据]
     */
    public function checkData($data, $noverify = []){
        // 验证规则
        $rule = [
       ];

        // 去除不需要验证的字段
        if (!empty($noverify)) {
            foreach ($noverify as $v) {
                unset($rule[$v]);
            }
        }

        // 错误提示
        $message = [
            'pid.require' => '请填写主键',
            'uid.require' => '请填写发布者ID',
            'pcid.require' => '请填写项目分类ID',
            'best_pcid.require' => '请填写行业的第一级',
            'address.require' => '请填写发展区域',
            'best_address.require' => '请填写第一级的项目地址',
            'name.require' => '请填写项目名称',
            'add_time.require' => '请填写添加时间',
            'status.require' => '请填写状态',
            'err.require' => '请填写拒绝理由',
            'logo.require' => '请填写项目LOGO',
            'intro.require' => '请填写项目简介',
            'content.require' => '请填写项目介绍',
            'money.require' => '请填写项目金额',
            'img_list.require' => '请填写项目图册',
            'video.require' => '请填写视频',
            'video_img.require' => '请填写视频图片',
            'ptype.require' => '请填写项目状态  1：概念阶段，2：研发阶段，3：启动阶段，4：运营阶段，5：盈利阶段',
            'company_name.require' => '请填写公司名称',
            'company_address.require' => '请填写公司地址',
            'company_license.require' => '请填写营业执照',
            'company_idimg.require' => '请填写法人身份证',
            'company_hand_idimg.require' => '请填写手持身份证',
            'member.require' => '请填写项目成员',
            'slogan.require' => '请填写项目口号',
            'company_contacts.require' => '请填写公司联系人',
            'company_tel.require' => '请填写联系人电话',
            'pro_id.require' => '请填写项目的id 用于通过的修改临时',
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