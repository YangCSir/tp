<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]' => [
        ':id' => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'uploadVideo' => 'Upload/uploadVideo',
    'test' => 'Test/index',
    'news/:id' => 'Posts/news',
    'uploadImg' => 'Upload/uploadImg',
    'share/:id' => 'Posts/share'

];
//\think\Route::rule(['news/:id' => 'Posts/news']);
