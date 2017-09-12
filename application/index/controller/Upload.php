<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/19
 * Time: 17:41
 */

namespace app\index\controller;


class Upload
{
    public function uploadVideo()
    {
        dump(microtime(true));die;
        dump(base64_decode("YXBpdHlwZT1zZW5kUHJvamVjdExpc3QmdG9rZW49MzUwNmIzYmVhZmY1YjJjNTFmMjgyMTQxZDdjMjBjMGQmcD0xJmFwaWlkPVdKSk1lYUJYJmFwaWtleT1aSUtYZXhHZlZGYU5GUnkz")) ;die;
        $idcard='511324199001302862';
        echo strlen($idcard)==15?substr_replace($idcard,"****",8,4):(strlen($idcard)==18?substr_replace($idcard,"****",10,4):"身份证位数不正常！");
        if (empty($_FILES['video'])) exit(\Response::json(PARAM_FAIL, '请选择要上传的文件'));
        $file_suffix = substr(strrchr($_FILES['video']['name'], '.'), 1);
        $file_path = "../upload/video/" . date("Y-m-d") . "/";
        $file_name = createRandomFileName("." . $file_suffix);
        if (!file_exists($file_path)) {
            createFolder($file_path);
        }
        $path = $file_path . $file_name;
        if (move_uploaded_file($_FILES['video']['tmp_name'], $path)) {
            exit(\Response::json(SUCCESS, 'success', ['path' => "/upload/video/" . date("Y-m-d") . "/" . $file_name]));
        } else {
            exit(\Response::json(FAIL, $_FILES['video']['error']));
        }
    }

    public function uploadImg()
    {
        $img = request()->file('img');
        if (empty($img)) exit(\Response::json(PARAM_FAIL, '请上传图片'));
        $data = $img->move(ROOT_PATH . "public/upload/face");
        if ($data) {
            exit(\Response::json(SUCCESS, SUCCESS_MSG, ['path' => IMG_PATH . "/upload/face/" . $data->getSaveName()]));
        } else {
            exit(\Response::json(FAIL, $data->getError()));
        }
    }
}