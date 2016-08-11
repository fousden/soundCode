//根据idx获取商品数据
function getProdByidx(idx) {
	var p;
	$.each(parms, function(k, v) {
		if (v.idx === idx) {
			p = v;
			return false;
		}
	});
	return p;
}
//获取URL参数
function getQueryString(name) { 
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null; 
}

//数字加千分位
function formatThousands(num) {
    return (num.toFixed(2) + '').replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,');
}