/*此文件是自定义的js函数*/
//isIE();
function handle_act(datatype,data){
    if(datatype=='url' && data){
    	window.open(data); 
    }else if(datatype=='urlLink' && data){
		location.href=data;
    }
}

function isIE() { //ie? 
      if (!!window.ActiveXObject || "ActiveXObject" in window){
      	if(getIeVersions()<=8){
      		alert("请使用IE8以上版本的浏览器访问！");
      		 location.href='/Public/html/404/404.html';
      	}
      }
    }

function getIeVersions(){
	var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
	var isOpera = userAgent.indexOf("Opera") > -1; //判断是否Opera浏览器
	var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera; //判断是否IE浏览器
	if(isIE){
		var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
        reIE.test(userAgent);
        return parseFloat(RegExp["$1"]);
	}
}

