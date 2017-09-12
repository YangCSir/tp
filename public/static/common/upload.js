/* 
* []
* @Author: Careless
* @Date:   2015-11-16 16:58:25
* @Email:  965994533@qq.com
* @Copyright:
*/
/**
 * [file_upload 文件上传]
 * @param  {[type]} conf [配置]
 */
function file_upload(conf){
    var oldConf = {
        // 选完文件后，是否自动上传。
        auto: true,
        // swf文件路径
        swf: '/static/common/webuploader/Uploader.swf',
        // 文件接收服务端。
        server: '',
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '',
        duplicate:true,
        mulit:false,
        // 只允许选择图片文件。
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/jpg,image/jpeg,image/png'
        }
    };

    var newConf = $.extend(oldConf, conf)
    var uploader = WebUploader.create(newConf);

    var loding;
    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {
        loding = layer.load(1, {
            shade: [0.2,'#000']
        });
    });

    // 文件上传成功
    uploader.on( 'uploadSuccess', function( file,ret ) {
        layer.close(loding);
        layer.msg(ret.msg,{'time':1000});
        if (ret.status == 1) {
            // 是否自定义了回调函数
            if (is_function(newConf.callback)) {
                newConf.callback(ret);
            } else {
                var info = '<div class="upimg-box">\
                                <span class="glyphicon glyphicon-remove-sign remove-img"></span>\
                                <input type="hidden" name="'+newConf.inputname+'" value="'+ret.data.path+'">\
                                <img src="'+ret.data.path+'" alt="" style="max-width:200px;">\
                            </div>';

                if (newConf.mulit == true) {
                    $(newConf.container).find('.allimg-verify-del').remove();
                    $(newConf.container).find('.upimg-ts').remove();
                    $(newConf.container).find('.Validform_checktip').remove();
                    $(newConf.container).append(info);
                } else {
                    $(newConf.container).html(info);
                }
            }
        }
    });
}