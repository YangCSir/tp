/* 
* [日期选择插件]
* @Author: Careless
* @Date:   2015-11-16 16:58:25
* @Email:  965994533@qq.com
* @Copyright:
*/
$(function(){
    // 日期选择器
    if ($('.dateplugin').length > 0) {
        $.each($('.dateplugin'), function(index, val) {
            var obj = $(this);
            var id = 'dateplugin-' + index;
            var dateContent = '<div id="'+id+'" class="jalendar">\
                                    <span class="close-date glyphicon glyphicon-remove-circle"></span>\
                                </div>';
            $(this).append(dateContent);
            // 选择类型
            var selectType = $(this).attr('stype');
            if (!is_var(selectType)) {
                selectType = 'selector';
            }

            $('#' + id).jalendar({
                color   : '#37C4A7',
                type    : selectType,
                lang    : 'EN',
                dateType:'yyyy-mm-dd',
                done: function() {
                    if (selectType == 'selector') {
                        // 单个选择
                        var data1 = $('#' + id + ' input.data1').val();
                        $(obj).find('.date-input').val(data1).blur();
                        // 隐藏选择器
                        $('#' + id).fadeTo(300, 0, function(){
                            $('#' + id).hide();
                        })
                    } else if (selectType == 'range') {
                        // 范围选择
                        var data1 = $('#' + id + ' input.data1').val();
                        var data2 = $('#' + id + ' input.data2').val();
                        $(obj).find('.date-more').val(data1 + '---' + data2).blur();
                        // 隐藏选择器
                        $('#' + id).fadeTo(300, 0, function(){
                            $('#' + id).hide();
                        })
                    }
                }
            });
        });
    }
    // 显示日期选择器
    $('.dateplugin input').click(function(event){
        event.stopPropagation();
        console.log(1);
        $('.jalendar-container').hide();
        $(this).siblings('.jalendar').fadeTo(300, 1);
        $(this).siblings('.jalendar').find('.jalendar-container').show();
        console.log(2);
    })

    $('.dateplugin').on('click', '.close-date', function() {
        $(this).parent('.jalendar').hide();
    });

    $('.jalendar .jalendar-container .header').click(function(event){
        event.stopPropagation();
    })

    $(document).click(function(){
        $('.jalendar-container').hide();
    })
})