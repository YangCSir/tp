<?php
/**
 * [资讯列表控制器]
 * @Author: Careless
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\News as model;

class News extends Common{
    protected $checkVal = [];
    /**
     * [_initialize 初始化]
     */
    public function _initialize(){
        parent::_initialize();
        $this -> checkVal = [
        ];
    }
    /**
     * [index 列表]
     */
    public function index(){
        // 搜索关键词
        $keywords = input('get.keywords');
        //实例化模型
        $model = new model;
        $where = '';
        if (!empty($keywords)) {
            $where['title|author'] = ['like', "%{$keywords}%"];
        }
        $begin = $this -> param['begin'] ? $this -> param['begin'] : '';
        $end = $this -> param['end'] ? $this -> param['end'] : '';
        if (!empty($begin)) {
            $where['add_time'] = ['gt', strtotime($begin . ' 00:00:00')];
        }
        if (!empty($end)) {
            $where['add_time'] = ['lt', strtotime($end . ' 23:59:59')];
        }
        if (!empty($begin) && !empty($end)) {
            $where['add_time'] = [
                ['gt', strtotime($begin . ' 00:00:00')],
                ['lt', strtotime($end . ' 23:59:59')],
            ];
        }

        // 获取数据
        $data  = admin_page($model, $where, 'sort DESC,add_time DESC');
        
        // 模板
        return view('',[
            'data'     => $data['data'],
            'page'     => $data['page'],
            'keywords' => $keywords,
            'checkVal' => $this -> checkVal,
            'begin'     => $begin,
            'end'       => $end
        ]);
    }

    /**
     * [add 添加数据]
     */
    public function add(){
        if ($this -> isPost) {
            // 实例化模型
            $model = new model;
            // 获取post数据
            $data = input('post.');
            $data['add_time'] = time();
            
            // 验证
            if (!$model -> checkData($data, ['nid','ncid','img','praise','browse','add_time'])) {
                return ajax($model -> err, 2);
            }
            // 添加数据
            if (!$model -> addData()) return ajax('添加失败', 2);
            return ajax('添加成功');
        }

        return view('', [
            'checkVal' => $this -> checkVal
        ]);
    }

    /**
     * [edit 编辑]
     */
    public function edit(){
        $model = new model;
        // POST提交处理
        if ($this -> isPost) {
            // 获取post数据
            $data = input('post.');
            
            
            // 验证
            if (!$model -> checkData($data, [])) {
                return ajax($model -> err, 2);
            }
            // 修改数据
            if (!$model -> editData(['nid'=>$data['nid']])) return ajax('没有数据被修改', 2);
            return ajax('修改成功');
        }
        
        // 获取旧数据
        $nid = (int)$this -> param['nid'];
        $data = $model -> getOne(['nid'=>$nid]);
        
        return view('',[
            'data' => $data,
            'nid' => $nid,
            'checkVal' => $this -> checkVal
        ]);
    }

    /**
     * [del 删除]
     */
    public function del(){
        // 实例化模型
        $model = new model;
        if (!$this -> isPost) return ajax('非法请求');
        // 定义条件
        $nid = $this -> param['id'];
        // 可批量删除
        $where['nid'] = ['in', $nid];
        if (!$model -> delData($where)) return ajax('删除失败');
        return ajax('删除成功');
    }


}