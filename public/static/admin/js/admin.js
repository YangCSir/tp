/* 
* []
* @Author: Careless
* @Date:   2015-11-16 16:58:25
* @Email:  965994533@qq.com
* @Copyright:
*/
var loding;
var iframe_url = '';
$(function(){
    $('#right_content').css({'min-height':$(window).height() - 60});

    // 绑定tooltip
    $('.operation a').attr({
        'data-toggle':'tooltip',
        'data-placement':'top'
    })
    $('[data-toggle="tooltip"]').tooltip();

    // 左侧菜单点击效果
    $('#menu h2').click(function(){
        $(this).next('p').slideToggle().siblings('p').slideUp();
        $(this).toggleClass('this').siblings().removeClass('this');  
        $(this).find('span').animate({'left':'-35px'}, 300);
        $(this).siblings('h2').find('span').animate({'left':'-40px'}, 300);
    });

    // 左侧菜单链接点击样式
    $('#menu p a').click(function(){
        $(this).addClass('this').siblings('a').removeClass('this');
        $(this).parent('p').siblings('p').find('a').removeClass('this');
        $('#top .icon-menu a').removeClass('this');
    })

    $('.check_box input,.radio_box input').click(function(event){
        event.stopPropagation();
    })
    // 单选
    $('.radio_box').click(function(){
        $(this).find('input').click();
        // 增加选中样式
        $(this).find('span').addClass('this').siblings('input').prop({'checked':true}).attr({'checked':true});
        // 选中对应表单
        $(this).siblings('div').find('span').removeClass('this').siblings('input').prop({'checked':false}).attr({'checked':false});
    });

    // 多选
    // $(document).on('click', '.check_box', function(event) {
    $('.check_box').click(function(){
        $(this).find('input').click();
        var _attr = $(this).find('span').attr('class');
        if (_attr){
            // 去除样式 去除选中 
            $(this).find('span').removeClass('this').siblings('input').attr({'checked':false}).prop({'checked':false});
        } else {
            // 增加选中样式 选中对应表单
            $(this).find('span').addClass('this').siblings('input').prop({'checked':true}).attr({'checked':true});
        }

    })
    
    $('.table_show:gt(0)').hide();
    $('.tableshow').click(function(){
        $('.table_show').hide();
        $(this).next('.table_show').fadeTo(300, 1);
    })

    // 绑定移除图片
    $('.uploader-list').on('click', '.remove-img', function() {
        $(this).parent('.upimg-box').remove();
    }); 

    // 单选多选验证提示样式
    if ($('u.Validform_checktip').length > 0) {
        $.each($('u.Validform_checktip'), function(index, val) {
            var _width = 0;
            $.each($(this).parent('div').parent('td').find('div'), function(index, val) {
                _width += $(this).width() + 10;
            });
            $(this).css({'position':'absolute','top':'-10px','left':_width + 'px'});
        });
    }

    // 表单验证提交
    if ($(".verify-form").length > 0) {
        $(".verify-form").Validform({
            tiptype     : 3,
            label       : ".msg",
            showAllError: true,
            ajaxPost    : true,
            beforeSubmit:function(){
                $('#Validform_msg').remove();
                loding = layer.load(2, {
                    shade: [0.1,'#fff']
                });
            },  
            callback:function(ret){
                layer.close(loding);
                layer.msg(ret.msg,{'time':1000});

                setTimeout(function(){
                    // 成功处理
                    if (ret.status == 1) {
                        var go = $(".verify-form").attr('go');
                        if (is_var(go)) {
                            if (go.indexOf('(') && go.indexOf(')')) {
                                eval(go);
                                return;
                            }
                        }

                        // 是否定义了回调函数
                        if (is_function(go)) {
                            window[go](ret);
                            return;
                        }

                        // 是否定义了跳转页面
                        if (is_var(go) && go != '') {
                            // 跳转到指定页面
                            setTimeout(function(){
                                location.href = window.parent.location = go;
                            }, 500);
                            return;
                        }
                        parent.window.close_frame(function(){
                            // 刷新父级框架
                            window.parent.location.reload();
                        });
                    }
                }, 500);
            }
        });
    }

    // 顶部菜单获取更多
    $('.menu-morer').click(function(){
        $('#top-menu ul').animate({
            'left':'-' + 800 * mr
        }, 500);
        $('.menu-morel').removeClass('vh');
        mr += 1;
    })

    $('.menu-morel').click(function(){
        $('#top-menu ul').animate({
            'left':0
        }, 500);
        $('.menu-morel').addClass('vh');
        mr = 1;
    })
})

/**
 * [goto_url 跳转页面]
 */
function goto_url(url){
    setTimeout(function(){
        location.href = url;
    }, 500);
}

/**
 * [is_function 函数是否存在]
 */
function is_function(funcName) {
    try {
        if (typeof(eval(funcName)) == "function") {
            return true;
        }
    } catch(e) {}
    return false;
}

/**
 * [is_var 变量是否存在]
 */
function is_var(vname) {
    try {
        return (vname == "" || vname == undefined || vname == null || !vname) ? false : true;
    } catch(e) {}
    return false;
}

/**
 * [show_frame 显示frame操作框]
 */
function open_frame(title, w, h, is){
    loding = layer.load(2, {
        shade: [0.1,'#fff']
    });
    // if (!is_var(w) && !is_var(h)) {
    //     return false;
    // }
    // is = is ? is : false;
    // if (is == true) {
    //     w = $(window).width() - w;
    //     h = $(window).height() - h;
    // } else {
    //     w = w ? w : 800;
    //     h = h ? h : 500;
    // }

    var iframe = document.getElementById("frame_disposeid");
    // console.log(window.frames["frame_disposeid"]);
    // return;

    // layer.open({
    //     type: 2,
    //     title: title,
    //     // skin: 'layui-mydiy',
    //     shadeClose: true,
    //     shade: 0.1,
    //     area: [w + 'px', h + 'px'],
    //     content: iframe //iframe的url
    // });
    
    if (iframe.attachEvent) {
        iframe.attachEvent("onload", function() {      
            my_show_frame(title, w, h, is); 
        });
    } else {
        iframe.onload = function() {      
            my_show_frame(title, w, h, is); 
        }; 
    }
}

function my_show_frame(title, w, h, is){
    if (!is_var(w) && !is_var(h)) {
        return false;
    }
    
    is = is ? is : false;
    if (is == true) {
        w = $(window).width() - w;
        h = $(window).height() - h;
    } else {
        w = w ? w : 800;
        h = h ? h : 500;
    }
    $('#show_frame iframe').height(h - 50);
    $('#show_frame .title').text(title);
    $('#loding-bg').stop().fadeTo(300,0.2);

    $('#show_frame').css({'opacity':'0'}).show();
    $('#show_frame').stop().animate({
        width: w,
        height: h,
        opacity: 1
    },500);
    w = 0; h = 0;
    layer.close(loding);
}

/**
 * [close_frame 关闭frame操作框]
 */
function close_frame(fn){
    $('#loding-bg').fadeTo(200, 0, function(){
        $('#loding-bg').hide();
    });
    var w = $('#show_frame').width();
    var h = $('#show_frame').height();
    $('#show_frame').stop().animate({
        'width': '100%',
        'height': '100%',
        'opacity':0,
    },200,function(){
        $('#show_frame').hide();
        $('.rform').css({'top':'40px'});
        if (is_function(fn)) {
            fn();
        }
    })
}

/**
 * [ajax_post 异步POST请求]
 */
function ajax_post(url, data, fn){
    layer.confirm('确定操作吗？', {
        btn: ['确定', '取消']
    }, function(){
        loding = layer.load(2, {
            shade: [0.1,'#fff']
        });
        $.ajax({
            url : url,
            type: 'post',
            dataType: 'json',
            data: data,
            success:function(ret){
                layer.close(loding);
                // 是否自定义了回调函数
                if (is_function(fn)) {
                    fn(ret);
                    return;
                }
                
                layer.msg(ret.msg,{'time':1000});
                if (ret.status == 1) {
                    setTimeout(function(){
                        location.reload();
                    }, 500);
                }
            }
        }) 
    }, function(){

    });  
}

/**
 * [ajax_noaffirm 异步POST请求，不需要确认]
 */
function ajax_noaffirm($url, $data, fn){
    $.ajax({
        url : url,
        type: 'post',
        dataType: 'json',
        data: data,
        success:function(ret){
            layer.close(loding);
            // 是否自定义了回调函数
            if (is_function(fn)) {
                fn(ret);
                return;
            }

            layer.msg(ret.msg,{'time':1000});
            if (ret.status == 1) {
                setTimeout(function(){
                    location.reload();
                }, 500);
            }
        }
    })
}

/**
 * [ajax_post 异步GET请求]
 */
function ajax_get(url, fn){
    loding = layer.load(2, {
        shade: [0.1,'#fff']
    });
    $.ajax({
        url : url,
        type: 'get',
        dataType: 'json',
        data: '',
        success:function(ret){
            layer.close(loding);
            layer.msg(ret.msg,{'time':1000});
            // 是否自定义了回调函数
            if (is_function(fn)) {
                fn(ret);
                return;
            }
            
            
            if (ret.status == 1) {
                setTimeout(function(){
                    location.reload();
                }, 500);
            }
        }
    })    
}

/**
 * [check_all 全选]
 */
function check_all(name){
    var a = $(this).attr('bz');
    if (!a) {
        $(this).attr({'bz':1});
        $('input[name="'+name+'"]').attr({'checked':'checked'}).siblings('span').addClass('this');
    } else {
        $(this).attr({'bz':0});
        $('input[name="'+name+'"]').removeAttr('checked').siblings('span').removeClass('this');
    }
}

/**
 * [all_operation 批量操作]
 */
function all_operation(url, inputName, data, fn){
    if (!is_var(data)) {
        data = {};
    }
    // 组合批量操作的ID
    var inputObj = $('input[name="'+inputName+'"]');
    var id = '';
    var d  = '';
    $.each(inputObj, function(index, val) {
        // console.log($(this).attr('checked'));
        if ($(this).attr('checked') == 'checked'){
            id += d + $(this).val();
            d = ',';
        }
    });
    data.id = id;
    if (data.id == '') {
        layer.msg('请选择要操作的内容', {time:1000});
        return;
    }
    // post 请求
    ajax_post(url, data, fn);
}

/**
 * [loding 加载动画]
 */
function loding(){
    loding = layer.load(2, {
        shade: [0.1,'#fff']
    });
}

/**
 * [close_loding 关闭加载]
 */
function close_loding(){
    layer.close(loding);
}

/**
 * [msg 提示信息]
 */
function msg(msg){
    layer.msg(msg,{'time':1000});
}

/**
 * [msg 打印信息]
 */
function log(data){
    console.log(data);
}
