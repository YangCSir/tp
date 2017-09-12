<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//图片路径
define('IMG_PATH', 'http://touzipingtai.zpftech.com');
define('POST_PATH', 'http://touzipingtai.zpftech.com/news/');
define('SHARE_PATH','http://touzipingtai.zpftech.com/share/');
//接口参数报错数字
define('SUCCESS', 200);
define('NOT_FOUND', 404);
define('API_FAIL', 101);
define('NO_PASS', 500);
define('BAD_REQUEST', 400);
define('PARAM_FAIL', 502);
define('FAIL', 503);
define('LOGIN_AGAIN', 999);
define('SUCCESS_MSG', 'success');
define('SERVICE_WRONG', '服务器出了一点小问题！');

/**
 * 公用上传单张图片方法
 * @param $img_base [图片]
 * @param string $img_type [上传的目录]
 * @return bool|string [文件路径或者false]
 */
function uploadImgOne($img, $img_type = "project")
{
    $data = $img->move(ROOT_PATH . 'public/upload/' . $img_type);
    if ($data) {
        return IMG_PATH . "/upload/" . $img_type . "/" . $data->getSaveName();
    } else {
        exit(Response::json(FAIL, $data->getError()));
    }
}

/**
 * 公用上传多张图片方法
 * @param $img
 * @param string $img_type
 * @return bool|string
 */
function uploadImgMore($img, $img_type = "project")
{
    $data = "";
    foreach ($img as $i) {
        $info = $i->validate(['ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/upload/' . $img_type);
        if ($info) {
            $data .= IMG_PATH . "/upload/" . $img_type . "/" . $info->getSaveName() . ",";
        } else {
            exit(Response::json(FAIL, $info->getError()));
        }
    }
    if (!empty($data)) return $data;
    return false;
}

/**
 * 上传图片并且加文字水印
 * @param $img_base
 * @param string $img_type
 * @return bool|string
 */
function uploadImgWatermark($img, $img_type = "project")
{
    $data = $img->move(ROOT_PATH . 'public/upload/' . $img_type);
//    dump($img);
    if ($data) {
        $img_path = "./upload/" . $img_type . "/" . $data->getSaveName();
        $image = \think\Image::open($img_path);
        $image->text('仅供平台身份认证使用', '../GB2312.ttf', 40)->save($img_path);
        return IMG_PATH . "/upload/" . $img_type . "/" . $data->getSaveName();
    } else {
        exit(Response::json(FAIL, $data->getError()));
    }
}

/**
 * 添加图片logo
 * @param $img_url
 * @param $logo_url
 * @return bool
 */
function addImgLogo($img_url, $logo_url)
{
    $image = \think\Image::open($img_url);
    $image_name = substr($img_url, 0, strrpos($img_url, '.'));
    $ext=substr(strrchr($img_url, '.'), 1);
    $new_name = $image_name . "_logo.".$ext;
    if ($image->water($logo_url, \think\Image::WATER_SOUTHEAST)->save($new_name)) return true;
    return false;
}

/**
 * 上传视频
 * @param $video
 * @param string $video_path
 * @return string
 */
function uploadVideo($video, $video_path = "video")
{
    $data = $video->move(ROOT_PATH . 'public/upload/' . $video_path);
    if ($data) {
        return IMG_PATH . "/upload/" . $video_path . "/" . $data->getSaveName();
    } else {
        exit(Response::json(FAIL, $data->getError()));
    }
}

// 生成随机文件名
function createRandomFileName($extname)
{
    $str = "0123456789abcdefghijklmnopqrstuvwxyz";
    $randname = '';
    for ($j = 1; $j <= 4; $j++) {
        $randname .= $str[rand(0, strlen($str) - 1)];
    }
    $newname = time() . $randname . $extname;
    return $newname;
}

//创建目录的方法
function createFolder($Folder)
{

    if (!is_readable($Folder)) {

        createFolder(dirname($Folder));

        if (!is_file($Folder)) mkdir($Folder, 0777);
        $Folder = "";
    }
    return $Folder;
}

function getPType($type)
{
    switch ($type) {
        case 1:
            $name = '概念阶段';
            break;
        case 2:
            $name = '研发阶段';
            break;
        case 3:
            $name = '启动阶段';
            break;
        case 4:
            $name = '运营阶段';
            break;
        case 5:
            $name = '盈利阶段';
            break;
        default:
            $name = '';
            break;
    }
    return $name;
}

function getStatus($status)
{
    switch ($status) {
        case 2:
            $name = "待审核";
            break;
        case 3:
            $name = "已通过";
            break;
        case 4:
            $name = "未通过";
            break;
        default:
            $name = "";
            break;
    }
    return $name;
}

/**
 * 生成验证码
 * @param $count
 * @param string $numtype
 * @return string
 */
function build_verify($count, $numtype = "all_lower")
{
    if ($numtype == "num") {
        $randstr = "0123456789";
    } elseif ($numtype == "zimu") {
        $randstr = "abcdefghijklmnopqrstuvwxyz";
    } elseif ($numtype == "allzimu") {
        $randstr = "abcdefghijklmnopqrstuvwxyz";
        $randstr = $randstr . strtoupper($randstr);
    } elseif ($numtype == "all_lower") {
        $randstr = "0123456789abcdefghijklmnopqrstuvwxyz";
        $randstr = $randstr . strtoupper($randstr);
    } elseif ($numtype == "all") {
        $randstr = "0123456789abcdefghijklmnopqrstuvwxyz";
        $randstr = $randstr . strtoupper("abcdefghijklmnopqrstuvwxyz");
    }
    $rand_string = "";
    for ($i = 1; $i <= $count; $i++) {
        $rand_string .= $randstr[rand(0, strlen($randstr) - 1)];
    }
    return $rand_string;
}


//function sendMsg($tel, $type)
//{
//    $code = build_verify(6, 'num');
//    $time = time();
//    $msg = "您的验证码是" . $code . ",请不要告诉别人哦";
//    if ($type == 1) {
//        $uid = \think\Db::name('user')->where('phone', $tel)->value('uid');
//        if ($uid) exit(Response::json(FAIL, '该手机号已经注册'));
//        $verify = \think\Db::name('code')->where('phone', $tel)->order('id', 'desc')->limit(1)->field('code,time')->find();
//        if ($verify['time'] + 120 > $time) exit(Response::json(FAIL, '验证码已经发送！请勿重复发送！'));
//    } elseif ($type == 2) {
//        if (!\think\Db::name('user')->where('phone', $tel)->value('uid')) exit(Response::json(FAIL, '该手机号还未注册，请先注册'));
//    } else {
//        exit(Response::json(FAIL, '短信类型不正确'));
//    }
//    $statusStr = array(
//        "0" => "短信发送成功",
//        "-1" => "参数不全",
//        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
//        "30" => "密码错误",
//        "40" => "账号不存在",
//        "41" => "余额不足",
//        "42" => "帐户已过期",
//        "43" => "IP地址限制",
//        "50" => "内容含有敏感词"
//    );
//    $smsapi = "http://api.smsbao.com/";
//    $user = "jxd001"; //短信平台帐号
//    $pass = md5("zpf@123"); //短信平台密码
//    $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $tel . "&c=" . urlencode($msg);
//    $result = file_get_contents($sendurl);
//    if (!empty($result)) exit(Response::json(FAIL, $statusStr[$result]));
//    $data = ['phone' => $tel, 'code' => $code, 'time' => $time];
//    if (think\Db::name('code')->insert($data)) return true;
//    return false;
//}

/**
 * 对二位数组排序
 * @param $arrays
 * @param $sort_key
 * @param int $sort_order
 * @param int $sort_type
 * @return array|bool
 */
function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
{
    if (is_array($arrays)) {
        foreach ($arrays as $k=>$array) {
            if (is_array($array)) {
                $key_arrays[$k] = $array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

/**
 * 递归将数组里的null转为空字符串
 * @param $arr
 * @return array|string
 */
function _unsetNull($arr)
{
    if ($arr !== null) {
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                if ($value === null) {
                    $arr[$key] = '';
                } else {
                    $arr[$key] = _unsetNull($value);//递归再去执行
                }
            }
        } else {
            if ($arr === null) {
                $arr = '';
            }
        }
    } else {
        $arr = '';
    }
    return $arr;
}


/**
 * [公共函数]
 * @Author: Careless
 * @Date:   2016-06-28 10:45:16
 * @Email:  965994533@qq.com
 * @Copyright:
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
/**
 * [打印输出数据]
 * @param void $var
 */
function p($var)
{
    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)) {
        var_dump(NULL);
    } else {
        echo "<pre style='padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;'>" . print_r($var, true) . "</pre>";
    }
}

/**
 * 创建密码
 * @param $pwd
 * @return string
 */
function create_pwd($pwd)
{
    return md5(md5($pwd) . sha1('my-ioc-!@#$%^&*()'));
}

/**
 * [ajax 返回ajax数据]
 */
function ajax($msg = '', $status = 1, $data = null, $time = 1)
{
    return json([
        'status' => $status,
        'msg' => $msg,
        'data' => $data,
        'time' => $time * 1000
    ]);
}

/**
 * [admin_page 后台分页]
 */
function admin_page($model, $where = '', $orderby = '', $field = '', $size = 10)
{
    // 获取数据
    $data = $model->field($field)->where($where)->order($orderby)->paginate($size);
    // 获取分页显示
    $page = $data->render();
    $tmp = $data->toArray();
    return [
        'data' => $tmp['data'],
        'page' => $page
    ];
}

/**
 * [api_page api分页]
 */
function api_page($model, $where = '', $orderby = '', $field = '')
{
    $data = input();
    // 分页信息
    $page = !empty($data['page']) ? $data['page'] : 1;
    $pageSize = !empty($data['page_size']) ? $data['page_size'] : 10;
    // 起始数据
    $ofset = ($page - 1) * $pageSize;
    $limit = $ofset . ',' . $pageSize;
    // 获取总数
    $total = $model->where($where)->count();
    // 获取数据
    $data = $model->where($where)->field($field)->order($orderby)->limit($limit)->select()->toArray();
    // 组合返回数据
    $return = [
        'total' => $total,
        'page' => $page,
        'page_size' => $pageSize,
        'all_page' => ceil($total / $pageSize),
        'data' => $data,
    ];
    return $return;
}

/**
 * [underline_to_hump 将下划线命名转换为驼峰式命名]
 * @param  string $str [要转换的字符串]
 * @param  boolean $ucfirst [首字母是否大写]
 */
function underline_to_hump($str = '', $ucfirst = true)
{
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ', '', lcfirst($str));
    return $ucfirst ? ucfirst($str) : $str;
}

/**
 * [hump_to_underline 将驼峰命名转换为下划线命名]
 * @param  string $str [要转换的字符串]
 */
function hump_to_underline($str = '')
{
    $new = strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $str));
    return $new;
}

/**
 * [write_file 文件缓存]
 */
function write_file($name, $data = '')
{
    // 检测名称是否存在
    if (empty($name)) return false;
    // 文件路径
    $file = RUNTIME_PATH . 'cache/' . $name . '.php';
    $dir = dirname($file);

    // 设置缓存
    if ($data) {
        // 创建目录
        is_dir($dir) || mkdir($dir, 0777, true);
        file_put_contents($file, json_encode($data));
        return true;
    }

    // 读取缓存
    if ($data === '') {
        if (!is_file($file)) return false;
        $res = json_decode(file_get_contents($file), true);
        return $res;
    }

    // 删除缓存
    if ($data === NULL) {
        @unlink($file);
        return true;
    }
}

/**
 * [get_ip 获取IP地址]
 */
function get_ip()
{
    static $realip;
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

/**
 * [create_order_num 创建唯一订单号]
 * @return [string]
 */
function create_order_num()
{
    // 截取当前微秒数的后6位
    $num = substr(uniqid(), 7, 13);
    // 将截取的微秒数分割为数组
    $numArr = str_split($num);
    // 用ord获取ASCII码，避免字母的出现
    $numArr = array_map('ord', $numArr);
    // 组合为字符串 规范长度
    $str = substr(implode('', $numArr), 0, 8);
    // 生成随机数
    $rand = '';
    for ($i = 0; $i < 5; $i++) {
        $rand .= mt_rand(0, 9);
    }
    // 组合订单号
    return date('Y') . $str . $rand;
}

/**
 * 获取一段时间内的周
 * @param $s 开始时间
 * @param $e 结束时间
 * @param $m_or_w 类型 week 周 | month 月
 * @return array
 */
function get_week_arr($s, $e, $m_or_w = 'week')
{
    $arr = array();
//    $start=strtotime($s." 00:00:00");
//    $end=strtotime($e." 23:59:59");
    $start = $s;
    $end = $e;
    if ($m_or_w == 'week') {
        $s_w = date('w', $start);
        $f_w = 8 - $s_w;
    } else {
        $allday = date('t', $start);
        $today = date('d', $start);
        $f_w = $allday - $today + 1;
    }
    if ($f_w) {
        $f_end = $start + 86400 * $f_w - 1;
    } else {
        $f_end = $start + 86400 - 1;
    }
    $new_end = $f_end;
    if ($end < $new_end) {
        $arr[] = array($start, $end);
        return $arr;
    }
    while ($end > $new_end) {
        $arr[] = array($start, $new_end);
        $start = $new_end + 1;
        if ($m_or_w == 'week') {
            $day = 7;
        } else {
            $day = date('t', $new_end + 10);
        }
        $new_end = $new_end + $day * 86400;
    }
    if ($m_or_w == 'week') {
        $fullday = 7;
    } else {
        $fullday = date('t', $new_end);
    }
    $arr[] = array($new_end - $fullday * 86400 + 1, $end);
    return $arr;
}

/**
 * [sql_keyupdate_one 有则修改，无则添加 sql 语句（一条）]
 */
function sql_keyupdate_one($data, $table)
{
    // 组合sql语句
    $sql = 'INSERT INTO `' . $table . '` (';
    $b = '';
    $insert = '';
    $values = '';
    $update = '';
    foreach ($data as $k => $v) {
        $insert .= $b . '`' . $k . '` ';
        $values .= $b . '"' . $v . '"';
        $update .= $b . '`' . $k . '`="' . $v . '"';
        $b = ',';
    }
    $sql .= $insert . ') VALUES (' . $values . ') ON DUPLICATE KEY UPDATE ' . $update;
    return $sql;
}

/**
 * 格式化日期
 * @param $time
 * @return string
 */
function format_date($time)
{
    $t = time() - $time;
    $f = array(
        '31536000' => '年',
        '2592000' => '个月',
        '604800' => '星期',
        '86400' => '天',
        '3600' => '小时',
        '60' => '分钟',
        '1' => '秒'
    );
    foreach ($f as $k => $v) {
        if (0 != $c = floor($t / (int)$k)) {
            return $c . $v . '前';
        }
    }
}

/**
 * [qrcode 生成二维码]
 * @param    string $value [二维码内容]
 * @param    string $qrcode [保存地址]
 * @param    integer $size [大小]
 * @param    string $errorCorrectionLevel [容错级别 [L M Q H][]]
 * @param    string $margin [边距]
 * @param    boolean $logo [logo图片]
 */
function qrcode($value = '', $qrcode = '', $size = 10, $errorCorrectionLevel = 'L', $margin = '0', $logo = false)
{
    require APP_PATH . 'common/extend/phpqrcode.php';
    $path = ROOT_PATH . 'public' . DS . 'uploads/agency/';
    if (!is_dir(dirname($qrcode))) {
        mkdir($path, 777, true);
    }
    //生成二维码图片
    \QRcode::png($value, $path . $qrcode, $errorCorrectionLevel, $size, $margin);
    if ($logo !== false) {
        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
    }
    return $qrcode;
    //输出图片
    //    imagepng($QR,$qrcode);
}


/**
 * curl 请求
 */
function curl($url, $data = [], $metnod = 'get', $headers = [])
{
    // 初始化curl
    $ch = curl_init();
    // 设置请求地址
    curl_setopt($ch, CURLOPT_URL, $url);
    // 返回的数据自动显示
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($metnod == 'post') {
        // 请求方式为POST
        curl_setopt($ch, CURLOPT_POST, 1);
        // POST数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    if (!empty($headers)) {
        // header头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    // 抓取跳转后的页面
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    // 执行请求
    $return = curl_exec($ch);
    // 关闭curl资源
    curl_close($ch);
    // 返回数据
    return json_decode($return, true);
}

// select concat('truncate table ' ,table_name,';') from information_schema.tables;




/**
 * 创建时间区间数组
 * @param string $hisStart
 * @param string $hisEnd
 * @param int $range
 * @return array
 */
function create_his_range($hisStart = '00:00:00', $hisEnd = '23:59:59', $range = 3600)
{
    return array_map(function ($time) {
        return date('H:00:00', $time);
    }, range(strtotime($hisStart), strtotime($hisEnd), $range));
}

/**
 * 创建日期区间数组
 * @param $ymdStart
 * @param bool $ymdEnd
 * @param int $range
 * @return array
 */
function create_ymd_range($ymdStart, $ymdEnd = true, $range = 86400)
{
    if ($ymdEnd === true) $ymdEnd = date('Y-m-d');

    return array_map(function ($time) {
        return date('m-d', $time);
    }, range(strtotime($ymdStart), strtotime($ymdEnd), $range));
}

/**
 * 写入日志
 * @param string $word
 * @param string $name
 */
function write_log($word = '', $name = 'log.txt')
{
    $fname = RUNTIME_PATH . $name;
    if (!is_dir(dirname($fname))) mkdir(dirname($fname), 0777, true);
    $fp = fopen($fname, 'a');
    flock($fp, LOCK_EX);
    fwrite($fp, '[' . date('Y-m-d H:i:s') . "] " . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * [runtime 获得脚本时间]
 * @param  integer $switch [description]
 */
function runtime($switch = 0)
{
    static $start;
    if ($switch == 1) {
        //开始，保存第一次时间
        $start = microtime(true);
    } else {
        //所算的的时间差
        return (microtime(true) - $start) . 's';
    }
}
// select concat('truncate table ' ,table_name,';') from information_schema.tables;


