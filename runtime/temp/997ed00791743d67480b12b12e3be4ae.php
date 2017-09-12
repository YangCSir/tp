<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"D:\www\touzipingtai\code\php\tzpt\public/../application/index\view\posts\news.html";i:1502791978;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
    <meta name="format-detection" content="telephone=no,email=no,date=no,address=no">
    <title><?php echo $data['title']; ?></title>
    <link rel="stylesheet" href="/static/css/index.css">
    <!--<link rel="stylesheet" type="text/css" href="__static__/css/index.css" />-->
</head>
<body>
<div class="headline"><?php echo $data['title']; ?></div>
<div class="middle">
    <div class="time"><?php echo date("m-d H:i",$data['add_time']); ?></div>
    <div class="browser">阅读量 <?php echo $data['browse']; ?></div>
    <div style="clear: both;"></div>
</div>

<div class="intro">
    <div>
        简介：<?php echo $data['intro']; ?>
    </div>
</div>
<div class="content">
    <div>
        <?php echo $data['detail']; ?>
    </div>
</div>

<!--<div class="ad">-->
<!--<img src="__static__/img/305463.jpg" height="172">-->
<!--</div>-->
<div class="recommend">
    <div class="head">相关文章</div>
    <?php if(is_array($recommend) || $recommend instanceof \think\Collection || $recommend instanceof \think\Paginator): if( count($recommend)==0 ) : echo "" ;else: foreach($recommend as $key=>$v): ?>
        <div class="title"><a href="http://touzipingtai.zpftech.com/news/<?php echo $v['nid']; ?>"><?php echo $v['title']; ?></a></div>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</div>
</body>
</html>