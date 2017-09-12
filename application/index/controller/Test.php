<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/20
 * Time: 16:14
 */

namespace app\index\controller;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Test
{
    public function index()
    {
        $b = array('4', '3', '8', '9', '2', '1');
        dump($this->getPao($b));
    }


    //冒泡排序法
    public function getPao($arr)
    {
        $len = count($arr);
        //设置一个空数组 用来接收冒出来的泡
        //该层循环控制 需要冒泡的轮数
        for ($i = 1; $i < $len; $i++) { //该层循环用来控制每轮 冒出一个数 需要比较的次数
            for ($k = 0; $k < $len - $i; $k++) {
                if ($arr[$k] > $arr[$k + 1]) {
                    $tmp = $arr[$k + 1];
                    $arr[$k + 1] = $arr[$k];
                    $arr[$k] = $tmp;
                }
            }
        }
        return $arr;
    }

    //选择排序法
    function select_sort($arr) {
//实现思路 双重循环完成，外层控制轮数，当前的最小值。内层 控制的比较次数
        //$i 当前最小值的位置， 需要参与比较的元素
        for($i=0, $len=count($arr); $i<$len-1; $i++) {
            //先假设最小的值的位置
            $p = $i;
            //$j 当前都需要和哪些元素比较，$i 后边的。
            for($j=$i+1; $j<$len; $j++) {
                //$arr[$p] 是 当前已知的最小值
                if($arr[$p] > $arr[$j]) {
                    //比较，发现更小的,记录下最小值的位置；并且在下次比较时，
                    // 应该采用已知的最小值进行比较。
                    $p = $j;
                }
            }
            //已经确定了当前的最小值的位置，保存到$p中。
            //如果发现 最小值的位置与当前假设的位置$i不同，则位置互换即可
            if($p != $i) {
                $tmp = $arr[$p];
                $arr[$p] = $arr[$i];
                $arr[$i] = $tmp;
                dump($arr);
            }
        }
        //返回最终结果
        return $arr;
    }

    //插入排序法
    function insert_sort($arr) {
        //区分 哪部分是已经排序好的
        //哪部分是没有排序的
        //找到其中一个需要排序的元素
        //这个元素 就是从第二个元素开始，到最后一个元素都是这个需要排序的元素
        //利用循环就可以标志出来
        //i循环控制 每次需要插入的元素，一旦需要插入的元素控制好了，
        //间接已经将数组分成了2部分，下标小于当前的（左边的），是排序好的序列
        for($i=1, $len=count($arr); $i<$len; $i++) {
            //获得当前需要比较的元素值。
            $tmp = $arr[$i];
            //内层循环控制 比较 并 插入
            for($j=$i-1;$j>=0;$j--) {
                //$arr[$i];//需要插入的元素; $arr[$j];//需要比较的元素
                if($tmp < $arr[$j]) {
                    //发现插入的元素要小，交换位置
                    //将后边的元素与前面的元素互换
                    $arr[$j+1] = $arr[$j];
                    //将前面的数设置为 当前需要交换的数
                    $arr[$j] = $tmp;
                    dump($arr);
                } else {
                    //如果碰到不需要移动的元素
                    //由于是已经排序好是数组，则前面的就不需要再次比较了。
                    break;
                }
            }
        }
        //将这个元素 插入到已经排序好的序列内。
        //返回
        return $arr;
    }
    //快速排序法
    function quick_sort($arr) {
        //先判断是否需要继续进行
        $length = count($arr);
        if($length <= 1) {
            return $arr;
        }
        //如果没有返回，说明数组内的元素个数 多余1个，需要排序
        //选择一个标尺
        //选择第一个元素
        $base_num = $arr[0];
        //遍历 除了标尺外的所有元素，按照大小关系放入两个数组内
        //初始化两个数组
        $left_array = array();//小于标尺的
        $right_array = array();//大于标尺的
        for($i=1; $i<$length; $i++) {
            if($base_num > $arr[$i]) {
                //放入左边数组
                $left_array[] = $arr[$i];
            } else {
                //放入右边
                $right_array[] = $arr[$i];
            }
        }
        //再分别对 左边 和 右边的数组进行相同的排序处理方式
        //递归调用这个函数,并记录结果
        $left_array = $this->quick_sort($left_array);
        $right_array = $this->quick_sort($right_array);
        //合并左边 标尺 右边
        return array_merge($left_array, array($base_num), $right_array);
    }


    /**
     * @param $dir
     * @return array
     */
    public function ls($dir)
    {
        $arr = array();
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $v) {
            $type = ($v->isDir()) ? '/' : '';
//            $arr[] = $v->getRealPath() . $type;
            $arr[] = $v->getRealPath() . $type;
        }
        return $arr;
    }

    public function getResult($arr)
    {
        $new_arr = array();
        if (!is_array($arr)) {
            return $new_arr;
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $new_arr = array_merge($new_arr, $this->getResult($value));
            } else {
                $new_arr[] = $value;
            }
        }
        return $new_arr;
    }

}