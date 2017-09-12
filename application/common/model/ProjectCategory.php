<?php
/**
 * [项目行业管理模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-08-07 13:59:31
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class ProjectCategory extends CommonModel{
    public $err;
    public $data;
    public $pk = 'pcid';

    /**
     * [checkData 验证数据]
     */
    public function checkData($data, $noverify = []){
        // 验证规则
        $rule = [
            'name' => 'require',
            'pid' => 'require|number',
       ];

        // 去除不需要验证的字段
        if (!empty($noverify)) {
            foreach ($noverify as $v) {
                unset($rule[$v]);
            }
        }

        // 错误提示
        $message = [
            'pcid.require' => '请填写项目分类id',
            'name.require' => '请填写名称',
            'status.require' => '请填写状态 0：禁用，1：正常',
            'pid.number' => '请填写正确的父id',
            'pid.require' => '请填写父id',
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