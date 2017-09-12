/**
 * Created by 1 on 2017/8/16.
 */
$(function () {
    $('.carousel').carousel();
    var flag = true;
    $(".changeImg").click(function () {

        if (flag) {
            $(this).parent().parent().find(".intro").hide();
            $(this).attr("src", "/static/img/947B4F38-AEDC-47F0-9478-813D118BA842@1x.png");
            // console.log($(this));
            flag = false;
        } else {
            $(this).parent().parent().find(".intro").show();
            $(this).attr("src", "/static/img/b6下箭头@2x.png");
            flag = true;
        }
    })
});