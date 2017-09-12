<?php
/**
 * [后台首页]
 * @Author: Careless
 * @Date:   2017-04-09 18:07:15
 * @Email:  965994533@qq.com
 * @Copyright:
 */
namespace app\admin\controller;
use app\common\model\AccessGroup;
use think\Session;

class Index extends Common{
    /**
     * 后台首页
     * @return \think\response\View
     */
    public function index(){
        // 权限组模型
        $groupModel = new AccessGroup;

        // 当前登录用户所属组
        $group = $this -> access;
        $id = empty($this -> param['id']) ? $group[0]['id'] : $this -> param['id'];
        Session::set('gid', $id);

        $tmp = db('access_group') -> where(['id'=>$id]) -> find();
        $access = json_decode($tmp['access'], true);

        // 获取有权限的菜单
        $in = '';
        foreach ($access AS $k => $v) {
            $in .= $k . ',';
        }

        // 获取菜单
        $model = db('menu');
        $menu = [];
        $tmp = $model -> order('`mcid` ASC,`id` ASC') -> where(['controller'=>['in', rtrim($in, ',')]]) -> select();
        foreach ($tmp as $k => $v) {
            $v['method'] = json_decode($v['method'], true);
            $menu[$v['mcid']][] = $v;
        }

        // 获取菜单分类
        $nmc  = [];
        $mc = db('menu_category') -> order('`sort` ASC') -> select();
        foreach ($mc as $v) {
            $nmc[$v['id']] = $v;
        }

        return view('',[
            'user'      => $this -> user,
            'menu'      => $menu,
            'mc'        => $nmc,
            'group'     => $group,
            'gid'       => $id,
            'access'    => $access,
            'id'        => $id
        ]);
    }

    /**
     * [welcome 欢迎界面]
     */
    public function welcome(){
        $id = $this -> param['id'];
        return view('', [
            'id'    => $id,
        ]);
    }

    public function form(){
        if ($this -> isPost) {
            p($this -> param);die;
        }
        return view();
    }

    public function table(){
        return view();
    }

    public function chart(){
        return view();
    }

    public function delete(){
        sleep(1);
        $request = request();
        $data = $request -> param();
        $data['msg'] = '删除成功';
        $data['status'] = 1;
        return json($data);
    }
}
