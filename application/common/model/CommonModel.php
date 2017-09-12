<?php
/**
 * [公共模型]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Date:   2017-04-17 10:18:50
 * @Copyright:
 */
namespace app\common\model;
use think\Model;

class CommonModel extends Model{
    /**
     * [addData 添加数据]
     */
    public function addData($data = []){
        // 不传数据时，用验证的数据
        if (empty($data)) $data = $this -> data;
        if (!$this -> data($data) -> allowField(true) -> save()) return false;
        $pk = $this -> pk;
        return $this -> data[$pk];
    }

    /**
     * [editData 修改数据]
     * @param  [type] $where [条件]
     * @param  array  $data  [数据]
     */
    public function editData($where = '', $data = []){
        if (empty($where)) return false;
        if (empty($data)) $data = $this -> data;
        return $this -> where($where) -> update($data);
    }

    /**
     * [delData 删除数据]
     * @param  [type] $where [条件]
     */
    public function delData($where){
        return $this -> destroy($where);
    }

    /**
     * [getAll 获取全部数据]
     * @param  array  $where [where条件]
     */
    public function getAll($where = [], $order = '', $k = ''){
        $res = $this -> where($where) -> order($order) -> select() -> toArray();
        if (empty($res)) return false;

        if (!empty($k)) {
            $tmp = [];
            foreach ($res AS $v) {
                $tmp[$v[$k]] = $v;
            }
            return $tmp;
        }
        return $res;
    }

    /**
     * [getOne 获取一条数据]
     * @param  array  $where [where条件]
     */
    public function getOne($where = []){
        $data = $this -> get($where);
        if (empty($data)) return false;
        return $data -> toArray();
    }
}
