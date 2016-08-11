//设置全局同步
$.ajaxSetup({
    async: false
});
//获取商品列表
//从session获取数据，如果没有则调取api
var parms = sessionStorage.getItem("Productlist") ? JSON.parse(sessionStorage.getItem("Productlist")) : [];
if (parms.length <= 0) {
    $.getJSON("../../index.php?m=Wxapi&a=Productlist", function(data) {
        $.each(data, function (k, v) {

            parms.push({
                "idx": v.product_id,//序号
                "name": v.name,//，名称
                "img": v.path.substr(1),//图片地址
                "rate": v.year_rate + "%",//利率
                "deadline": v.str_month,//月份
                "payMethod": v.payMethod,//还款方式
                "reserveNum": v.reserve_num//预约人数
            });
        });
        sessionStorage.setItem("Productlist", JSON.stringify(parms));
    });
}

//var parms = [{
//	"idx": "1",
//	"name": "年富通",
//	"img": "p_nft.png",
//	"rate": "12%",
//	"deadline":"12",
//	"payMethod": "分4期,每季返还本息"
//}, {
//	"idx": "2",
//	"name": "年富盈",
//	"img": "p_nfy.png",
//	"rate": "13%",
//	"deadline":"13",
//	"payMethod": "分5期,每季返还本息"
//}, {
//	"idx": "3",
//	"name": "季满盈",
//	"img": "p_jmy.png",
//	"rate": "14%",
//	"deadline":"14",
//	"payMethod": "分6期,每季返还本息"
//}, {
//	"idx": "4",
//	"name": "月满盈",
//	"img": "p_ymy.png",
//	"rate": "15%",
//	"deadline":"15",
//	"payMethod": "分7期,每季返还本息"
//}, {
//	"idx": "5",
//	"name": "双季通",
//	"img": "p_sjt.png",
//	"rate": "16%",
//	"deadline":"16",
//	"payMethod": "分8期,每季返还本息"
//}, {
//	"idx": "6",
//	"name": "双季盈",
//	"img": "p_nfy.png",
//	"rate": "17%",
//	"deadline":"17",
//	"payMethod": "分9期,每季返还本息"
//}];


//获取门店列表
//从session获取数据，如果没有则调取api
var palcearr = sessionStorage.getItem("PalceList") ? JSON.parse(sessionStorage.getItem("PalceList")) : [];
if (palcearr.length <= 0) {
    $.getJSON("../../index.php?m=Wxapi&a=getSubDepartment", function(data) {
        $.each(data, function(k, v) {
            palcearr.push(v.name);
            sessionStorage.setItem("PalceList", JSON.stringify(palcearr));
        });
    });
}

//var palcearr = [
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店',
//    '南京东路门店'
//];
