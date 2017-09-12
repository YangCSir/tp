<?php
/**
 * [反馈列表模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 14:44:58
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class Feedback extends CommonModel{
    public $err;
    public $data;
    public $pk = 'fid';

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
            'fid.require' => '请填写主键',
            'uid.require' => '请填写用户id',
            'content.require' => '请填写内容',
            'img.require' => '请填写图片',
            'add_time.require' => '请填写添加时间',
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