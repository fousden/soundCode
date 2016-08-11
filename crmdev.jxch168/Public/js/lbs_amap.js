function geocoder() {
    var MGeocoder;
    var address = $(".address").val();
    if(address == ''){
        alert("请输入具体地址");
        return;
    }
    //加载地理编码插件
    AMap.service(["AMap.Geocoder"], function () {
        MGeocoder = new AMap.Geocoder({
            radius: 1000 //范围，默认：500
        });
        //返回地理编码结果
        //地理编码

        MGeocoder.getLocation(address, function (status, result) {
            if (status === 'complete' && result.info === 'OK') {
                geocoder_CallBack(result);
            }
        });
    });
}
function addmarker(i, d) {
    var lngX = d.location.getLng();
    var latY = d.location.getLat();
    var markerOption = {
        'map': map,
        'icon': "http://webapi.amap.com/theme/v1.3/markers/n/mark_b" + (i + 1) + ".png",
        'position': [lngX, latY]
    };
    var mar = new AMap.Marker(markerOption);
    marker.push(markerOption.position);

    var infoWindow = new AMap.InfoWindow({
        content: d.formattedAddress,
        autoMove: true,
        size: new AMap.Size(150, 0),
        offset: {x: 0, y: -30}
    });
    windowsArr.push(infoWindow);

    var aa = function (e) {
        infoWindow.open(map, mar.getPosition());
    };
    mar.on("mouseover", aa);
}
    //地理编码返回结果展示
function geocoder_CallBack(data) {
    var resultStr = "";
    //地理编码结果数组
    var geocode = new Array();
    geocode = data.geocodes;
    var address = geocode[0].formattedAddress;//地址：
    var x = geocode[0].location.getLat();//x坐标
    var y = geocode[0].location.getLng();//y坐标
    $(".address").val(address);
    $(".x").val(x);
    $(".y").val(y);
    addmarker(0, geocode[0]);
    map.setFitView();
}