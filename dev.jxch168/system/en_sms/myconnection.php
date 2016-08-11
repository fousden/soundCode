<?php
//长连接
// 建立客户端的socet连接  
set_time_limit(0);

function UrlUnion($V)
{	
	$strParam="";
	if (!$V)
		return "";	
	foreach ($V as $k=>$v)
	{
		$strParam.= $k."=".urlencode($v)."&";
	}
	$strParam = substr($strParam, 0, -1);
	return $strParam;
}

function xmlUnion($V)
{
	$strdata = "";
	if (!$V)
		return "";
	foreach ($V as $k=>$v)
	{
		$strdata.="<$k>$v</$k>";
	}
	return $strdata;
}
//获取包
$arrGetData = array('userId'=>'xlc001', 'password'=>'123456');
//发送包
$arrSendData = array('userId'=>'xlc001', 
				'password'=>'123456',
				'pszMobis'=>'15012478785',
				'pszMsg'=>'utf8编码'.strval(rand()),
				'iMobiCount'=>'1',
				'pszSubPort'=>'*');
				

//SOAP包
$postaddr = "MongateCsSpSendSmsNew";
$postdata = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><'.$postaddr.' xmlns="http://tempuri.org/">'.xmlUnion($arrSendData).'</'.$postaddr.'></soap:Body></soap:Envelope>';
$senddata = "POST /MWGate/wmgw.asmx HTTP/1.1\r\nHost: 192.168.1.202\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nSOAPAction: \"\"\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";

$times = 0;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);  
$connection = socket_connect($socket, '192.168.1.202', 8088);    //连接服务器端socket  

if (!$connection)
{
	echo "connection fail.";
	socket_close($socket);
	exit;
}

while(true)
{	
	$recvdata = '';
	if (!socket_write($socket, $senddata))
	{
		echo "send data fail.";
		socket_close($socket);
		exit;
	}	
	$recvdata = socket_read($socket, 8 * 1024, PHP_BINARY_READ);
	/*while ($buffer = socket_read($socket, 4096, PHP_BINARY_READ)) 
	{  
		$recvdata.=$buffer; 					
	}*/
	//echo htmlentities($recvdata)."<br/><br/>";
}
socket_close($socket);	
print_r($arrSendData);
exit;

//发送
//POST包
$postaddr = "MongateCsSpSendSmsNew";
$postdata = UrlUnion($arrSendData);
$senddata = "POST /MWGate/wmgw.asmx/{$postaddr} HTTP/1.1\r\nHost: 192.168.1.202\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";
 
/*//GET包
$postaddr = "MongateCsGetStatusReportExEx";
$postdata = UrlUnion($arrSendData);
$senddata = "GET /MWGate/wmgw.asmx/{$postaddr}?{$postdata} HTTP/1.0\r\n\r\n";*/

//SOAP包
$postaddr = "MongateCsSpSendSmsNew";
$postdata = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><'.$postaddr.' xmlns="http://tempuri.org/">'.xmlUnion($arrSendData).'</'.$postaddr.'></soap:Body></soap:Envelope>';
$senddata = "POST /MWGate/wmgw.asmx HTTP/1.1\r\nHost: 192.168.1.202\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nSOAPAction: \"\"\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";
	
				
//获取				
//POST包
$postaddr = "MongateCsGetStatusReportExEx";
$postdata = UrlUnion($arrGetData);
$senddata = "POST /MWGate/wmgw.asmx/{$postaddr} HTTP/1.1\r\nHost: 192.168.1.202\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";
 
/*//GET包
$postaddr = "MongateCsGetStatusReportExEx";
$postdata = UrlUnion($arrGetData);
$senddata = "GET /MWGate/wmgw.asmx/{$postaddr}?{$postdata} HTTP/1.0\r\n\r\n";*/

//Soap包
$postaddr = "MongateCsGetStatusReportExEx";
$postdata = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><'.$postaddr.' xmlns="http://tempuri.org/">'.xmlUnion($arrGetData).'</'.$postaddr.'></soap:Body></soap:Envelope>';
$senddata = "POST /MWGate/wmgw.asmx HTTP/1.1\r\nHost: 192.168.1.202\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nSOAPAction: \"\"\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";

?>