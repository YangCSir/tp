<?php
/**
 * [菜单分类管理]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-04-17 10:18:50
 * @Copyright:
 */
namespace app\admin\model;
use app\common\model\CommonModel;
use think\Validate;

class MenuCategory extends CommonModel{
    public $err;
    public $data;
    public $pk = 'id';

    /**
     * [checkData 验证数据]
     */
    public function checkData($data){
        // 验证规则
        $rule = [
            'name'  =>  'require',
            'icon'  =>  'require',
        ];
        // 错误提示
        $message  =   [
            'name.require' => '请填写名称',
            'icon.require' => '请填写图标',
        ];
        // 创建验证
        $validate = new Validate($rule, $message);
        if (!$validate -> check($data)) {
            $this -> err = $validate -> getError();
            return false;
        }
        $this -> data = $data;
        return true;
    }
}
