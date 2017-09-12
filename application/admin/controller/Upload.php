<?php
/**
 * [文件上传]
 * @Author: Careless
 * @Date:   2017-04-09 21:38:30
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use think\Image;

class Upload extends Common{
    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request() -> file('file');
        // 判断图片大小是否超限
        $info = $file -> getInfo();
        // 图片大小限制 3145728
        if ($info['size'] > 11457280) {
            return ajax('上传的图片过大', 2);
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file -> move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $path = $info -> getRealPath();
            $fname = strstr($path, 'uploads');
            $fname = '/' . str_replace('\\', '/', $fname);

            // 是否需要裁剪图片
            $cj = input('post.cj');
            if (!empty($cj)) {
                // 执行裁剪
                $this -> _tailorImg($path, $cj);
            }
            return ajax('上传成功', 1, ['path'=>$fname]);
        }else{
            // 上传失败获取错误信息
            return ajax($file->getError(), 2);
        }
        
    }

    /**
     * 裁剪图片
     * @param $fname
     * @param $cj
     */
    private function _tailorImg($fname, $cj){
        // 打开图片
        $image = Image::open($fname);
        // 获取原图宽高
        $width  = $image -> width();
        $height = $image -> height();

        // 裁剪
        foreach (explode(',', $cj) as $v) {
            // 计算比例
            $ratio = $v / $width;
            // 计算高度
            $h = round($height * $ratio);
            // 生成缩略 
            $image -> thumb($v, $h, 2) -> save($fname . '_' . $v . '.jpg');
        }
    }
}











