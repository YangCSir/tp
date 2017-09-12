<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:83:"D:\www\touzipingtai\code\php\tzpt\public/../application/index\view\posts\share.html";i:1502869226;}*/ ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <link rel="stylesheet" href="__static__/common/bootstrap/css/bootstrap.css?1=1">
    <script type="text/javascript" src="__static__/common/jquery-3.2.1.min.js?a=1"></script>
    <script type="text/javascript" src="__static__/common/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="__static__/js/main.js"></script>
    <link rel="stylesheet" href="__static__/css/style.css"/>
    <title></title>
    <script type="text/javascript">
        window.onload = function () {
            var designWidth = 750,
                    rem2px = 40;
            document.documentElement.style.fontSize = ((window.innerWidth / designWidth) * rem2px) + 'px';
        }
    </script>
</head>

<body>
<div class="header">
    <!--<a href="javascript:;" class="back"><img src="__static__/img/back@2x.png"/></a>-->
    <h3>项目详情</h3>
    <!--<a href="javascript:;" class="collection"><img src="__static__/img/心nor@2x.png"/></a>-->
    <!--<a href="javascript:;" class="share"><img src="__static__/img/分享@2x.png"/></a>-->
</div>
<div id="banner">

    <!--<a href="javascript:;"><img src="__static__/img/banner@2x.png" /></a>-->
    <div class="item">
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <?php if(is_array($data['img_list']) || $data['img_list'] instanceof \think\Collection || $data['img_list'] instanceof \think\Paginator): $k = 0;$__LIST__ = is_array($data['img_list']) ? array_slice($data['img_list'],0,4, true) : $data['img_list']->slice(0,4, true); if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;if($k == 1): ?>
                <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                <?php else: ?>
                <li data-target="#carousel-example-generic" data-slide-to="<?php echo $k+1; ?>"></li>
                <?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </ol>

            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox" style="height: 260px;">
                <?php if(is_array($data['img_list']) || $data['img_list'] instanceof \think\Collection || $data['img_list'] instanceof \think\Paginator): $k = 0;$__LIST__ = is_array($data['img_list']) ? array_slice($data['img_list'],0,4, true) : $data['img_list']->slice(0,4, true); if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;if($k == 1): ?>
                <div class="item active">
                    <img src="<?php echo $vo; ?>" alt="...">
                </div>
                <?php else: ?>
                <div class="item">
                    <img src="<?php echo $vo; ?>" alt="...">
                </div>
                <?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </div>

            <!-- Controls -->
            <!--<a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">-->
            <!--<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>-->
            <!--<span class="sr-only">Previous</span>-->
            <!--</a>-->
            <!--<a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">-->
            <!--<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>-->
            <!--<span class="sr-only">Next</span>-->
            <!--</a>-->
        </div>
    </div>

    <div class="margin-l-r-15">
        <h3><?php echo $data['name']; ?></h3>
        <div class="title">
            <?php if(empty($data['pcid']) || (($data['pcid'] instanceof \think\Collection || $data['pcid'] instanceof \think\Paginator ) && $data['pcid']->isEmpty())): ?>
            <a href="javascript:;">不限</a>
            <?php else: if(is_array($data['pcid']) || $data['pcid'] instanceof \think\Collection || $data['pcid'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['pcid'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <a href="javascript:;"><?php echo $vo['name']; ?></a>
            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
        </div>
        <div style="clear: both;"></div>
        <div class="label">
            <?php if(empty($data['address']) || (($data['address'] instanceof \think\Collection || $data['address'] instanceof \think\Paginator ) && $data['address']->isEmpty())): ?>
            <a href="javascript:;">不限</a>
            <?php else: if(is_array($data['address']) || $data['address'] instanceof \think\Collection || $data['address'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['address'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <a href="javascript:;"><?php echo $vo['name']; ?></a>
            <?php endforeach; endif; else: echo "" ;endif; endif; ?>
        </div>
        <div style="clear: both;"></div>
        <div style="margin:0.25rem 0;">
            <div class="fl one" style="width: 80%;"><?php echo $data['company_name']; ?></div>
            <div class="fr"><a href="javascript:;" class="ptype"><?php echo $data['ptype']; ?></a></div>
            <div style="clear: both;"></div>
        </div>


    </div>
    <div class="xian"></div>
</div>
<div class="main">
    <div class="maim-introduce margin-l-r-15">
        <h3>项目口号：<?php echo $data['slogan']; ?></h3>

        <div class="panel-group down" id="accordion" role="tablist" aria-multiselectable="true">
            <h6>项目介绍</h6>
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"
               aria-expanded="false" aria-controls="collapseTwo">
                <img class="changeImg" src="__static__/img/b6下箭头@2x.png"/>
            </a>
            <div class="intro" style=" overflow : hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;">
                <?php echo $data['intro']; ?>
            </div>

            <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                <div>
                    <?php echo $data['intro']; ?>
                </div>
            </div>
        </div>
        <h6>团队介绍</h6>
        <div class="panel down">
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree"
               aria-expanded="false" aria-controls="collapseThree">
                <img class="changeImg" src="__static__/img/b6下箭头@2x.png"/>
            </a>
            <div class="intro" style=" overflow : hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;">
                <?php echo $data['content']; ?>
            </div>
            <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                <div>
                    <?php echo $data['content']; ?>
                </div>
            </div>
        </div>
    </div>

    <!--<div class="down">-->
    <!--<div class='text-box'>-->
    <!--Lorem ipsum dolor sit amet, consectetur adipisci ng elit. Aenean euismod bibendum laoreet. Proin gravida-->
    <!--dolor sit da dolor s da ...-->
    <!--<div class="collapse" id="collapseExample">-->
    <!--<div>-->
    <!--Lorem ipsum dolor sit amet, consectetur adipisci ng elit. Aenean euismod bibendum laoreet. Proin-->
    <!--gravida-->
    <!--dolor sit da dolor s da ...-->
    <!--</div>-->
    <!--</div>-->
    <!--</div>-->
    <!--<a role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false"-->
    <!--aria-controls="collapseExample">-->
    <!--<img class="changeImg" src="__static__/img/b6下箭头@2x.png"/>-->
    <!--</a>-->
    <!--</div>-->


    <div class="hx"></div>
    <div class="maim-information margin-l-r-15">
        <h6>核心成员</h6>
        <?php if(is_array($data['member']) || $data['member'] instanceof \think\Collection || $data['member'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['member'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <div class="div1">
            <div class="div1-title">
                <a href="" class="fl">
                    <img src="<?php echo $vo['head_img']; ?>"/>
                    <span><?php echo $vo['name']; ?></span>
                </a>
                <a href="" class="fr csr one"><?php echo $vo['position']; ?></a>
                <div style="clear: both;"></div>
            </div>
            <?php if(is_array($vo['work']) || $vo['work'] instanceof \think\Collection || $vo['work'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['work'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
            <div style="margin-bottom: 0.5rem;">
                <span class="txt one" style="width: 70%;"><?php echo $v['company']; ?>·<?php echo $v['job']; ?></span>
                <span class="date"><?php echo $v['start_time']; ?>-<?php echo $v['end_time']; ?></span>
                <div style="clear: both;"></div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>

            <div class="text"><?php echo $vo['intro']; ?>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <!--<div class="div1">-->
        <!--<div class="div1-title">-->
        <!--<a href="" class="fl">-->
        <!--<img src="__static__/img/E9939C51-E583-4F4E-B8E5-5A09E946B803@1x.png"/>-->
        <!--<span>孙悟空</span>-->
        <!--</a>-->
        <!--<a href="" class="fr csr">安保处处长</a>-->
        <!--<div style="clear: both;"></div>-->
        <!--</div>-->
        <!--<div style="margin-bottom: 0.5rem;">-->
        <!--<span class="txt">大唐王朝·外交部长</span>-->
        <!--<span class="date">664.01-2017.6</span>-->
        <!--<div style="clear: both;"></div>-->
        <!--</div>-->
        <!--<div style="margin-bottom: 0.5rem;">-->
        <!--<span class="txt">大慈恩学院·佛学系·博士</span>-->
        <!--<span class="date">664.01-2017.6</span>-->
        <!--<div style="clear: both;"></div>-->
        <!--</div>-->
        <!--<div class="text down">-->

        <!--孙悟空生性聪明、活泼、忠诚、嫉恶如仇，在民间文 化中代表了机智、勇敢。被中国人奉为神明，孙悟空 生性聪明、活泼、忠诚、嫉恶如仇，在民间文化中代 表了机智、勇敢。被中国人奉为神明。-->

        <!--<a href=""><img src="__static__/img/947B4F38-AEDC-47F0-9478-813D118BA842@1x.png"/></a>-->
        <!--</div>-->
        <!--</div>-->
        <div class="maim-information pic">
            <h6>项目图片</h6>
            <a href=""><span style="top: 10px;">查看全部</span> <img
                    src="__static__/img/6A0BE1FE-F28F-4D5E-ADFB-0B2130890F51@1x.png"/></a>
            <div style="clear: both;"></div>
            <ul class="">
                <?php if(is_array($data['img_list']) || $data['img_list'] instanceof \think\Collection || $data['img_list'] instanceof \think\Paginator): $i = 0;$__LIST__ = is_array($data['img_list']) ? array_slice($data['img_list'],0,3, true) : $data['img_list']->slice(0,3, true); if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <li><img src="<?php echo $vo; ?>"/></li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
            <div style="clear: both;"></div>
            <h6 style="margin-top: 0.5rem;">项目视频</h6>
            <div style="clear: both;"></div>
            <ul class="">
                <li class="video">
                    <img src="__static__/img/0E9B1E0A-F663-4023-A1FC-F453F5CFADFC@1x.png"/>
                    <span><img src="__static__/img/play@2x.png"/></span>
                </li>
            </ul>
            <div style="clear: both;"></div>
        </div>
    </div>
    <div class="number">
        <span class="fl">项目金额</span>
        <span class="fr" style="color:#ff4646;">￥<?php echo $data['money']; ?></span>
        <div style="clear: both;"></div>
    </div>
    <div class="project-text">项目已过平台认证</div>
</div>
<div class="footer">
    <!--<a href="" class="jhs">索要项目计划书</a>-->
    <!--<a href="" class="lxfs">索要联系方式</a>-->
    <a href="" class="lxfs">下载app</a>
</div>
</body>

</html>