$(function () {
    $(".table-nowrap th").attr('nowrap','nowrap');
    $(".table-nowrap td").attr('nowrap','nowrap');
    //美化所有分页
    $(".pagination").find('span,a').attr('class','page-link');
    $(".pagination").find('li').attr('class','page-item');
})