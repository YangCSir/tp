<?php
/**
 * [投资人管理模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 11:16:45
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class Investor extends CommonModel{
    public $err;
    public $data;
    public $pk = 'iid';

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
            'iid.require' => '请填写主键',
            'name.require' => '请填写姓名',
            'true_img.require' => '请填写真实头像',
            'idcard.require' => '请填写身份证号码',
            'email.require' => '请填写邮箱',
            'idimg.require' => '请填写手持身份证',
            'idcard_img.require' => '请填写身份证正面照',
            'company_name.require' => '请填写公司名称',
            'company_job.require' => '请填写公司职位',
            'card.require' => '请填写名片',
            'bank_card.require' => '请填写银行卡存款证明',
            'fixation_money.require' => '请填写固定资产证明',
            'self_money.require' => '请填写个人资产证明',
            'invest_intention.require' => '请填写投资意向',
            'invest_address.require' => '请填写投资地区',
            'money_top.require' => '请填写投资上限',
            'money_down.require' => '请填写投资下限',
            'uid.require' => '请填写用户ID',
            'status.require' => '请填写状态-1待审核  0：草稿，1：通过，2：拒绝 ',
            'err.require' => '请填写拒绝理由',
            'add_time.require' => '请填写添加时间',
            'up_time.require' => '请填写审核时间',
            'invest_experience.require' => '请填写投资经历',
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