<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/7/26
 * Time: 16:33
 */

namespace app\index\controller;


use app\index\model\News;
use app\index\model\Project;
use think\Controller;
use think\Db;

class Posts extends Controller
{
    public function news($id)
    {
        $news_model = new News();
        $post = $news_model->where('nid', $id)->find();
        if (!empty($post)) {
            $news_model->where('nid', $id)->setInc('browse');
        } else {
            echo '<div style="color: red;">该文章不存在</div>';
        }
        $recommend = Db::query("select nid,title from tpn_news where nid !=" . $id . " order by rand() limit 3");
        $this->assign('data', $post);
        $this->assign('recommend', $recommend);
        return $this->fetch();
    }

    public function share($id)
    {
        $project_model = new Project();
        $data = $project_model->getProjectDetail(['project_id' => $id]);
//        dump(collection($data)->toArray());die;
        $this->assign('data', collection($data)->toArray());
        return $this->fetch();
    }
}