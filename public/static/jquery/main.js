$(function () {
    $(".table-nowrap th").attr('nowrap','nowrap');
    $(".table-nowrap td").attr('nowrap','nowrap');
    //美化所有分页
    $(".pager").attr('class','pagination');
    $(".pagination").find('span,a').attr('class','page-link');
    $(".pagination").find('li').attr('class','page-item');

})
//对立的弹出层
function x_admin_show(title,url,w,h){
    if (title == null || title == '') {
        title=false;
    };
    if (url == null || url == '') {
        url="/index/index/404";
    };
    if (w == null || w == '') {
        w=($(window).width()*0.9);
    };
    if (h == null || h == '') {
        h=($(window).height() - 50);
    };
    layer.open({
        type: 2,
        area: [w+'px', h +'px'],
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        shade:0.4,
        title: title,
        content: url
    });
}