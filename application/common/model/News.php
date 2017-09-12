<?php
/**
 * [资讯列表模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\common\model;
use think\Validate;

class News extends CommonModel{
    public $err;
    public $data;
    public $pk = 'nid';

    /**
     * [checkData 验证数据]
     */
    public function checkData($data, $noverify = []){
        // 验证规则
        $rule = [
            'title' => 'require',
            'intro' => 'require',
            'detail' => 'require',
            'author' => 'require',
            'sort' => 'require|number',
       ];

        // 去除不需要验证的字段
        if (!empty($noverify)) {
            foreach ($noverify as $v) {
                unset($rule[$v]);
            }
        }

        // 错误提示
        $message = [
            'nid.require' => '请填写主键',
            'ncid.require' => '请填写所属分类',
            'title.require' => '请填写标题',
            'img.require' => '请填写图片',
            'intro.require' => '请填写简介',
            'praise.require' => '请填写点赞次数',
            'browse.require' => '请填写浏览次数',
            'detail.require' => '请填写详情',
            'add_time.require' => '请填写添加时间',
            'author.require' => '请填写作者',
            'sort.number' => '请填写正确的排序',
            'sort.require' => '请填写排序',
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