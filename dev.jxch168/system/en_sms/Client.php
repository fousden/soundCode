<?php
define('SMS_PATH', str_replace('Client.php', '', str_replace('\\', '/', __FILE__)));
require_once(SMS_PATH.'Http.php');

$isMode = true;
if (!class_exists('soapclient'))
{
	require_once(SMS_PATH.'nusoap.php');
	$isMode = false;
}

/**
 短信平台
 */
class Client
{
	/**
	 * 用户名
	 */
	var $userName;

	/**
	 * 密码
	 */
	var $userPass;

	//子端口号码，不带请填星号{*} 长度由账号类型定4-6位，通道号总长度不能超过20位。如：10657****主通道号，3321绑定的扩展端口，主+扩展+子端口总长度不能超过20位。
	var $pszSubPort;

	var $url;

	var $timeout;

	var $response_timeout;

	//发送信息0 获取上行1 获取状态报告2 获取余额3
	var $webinterface = 0;

	var $namespace = 'http://tempuri.org';

	/**
	 * 往外发送的内容的编码,默认为 UTF-8
	 */
	var $outgoingEncoding = "UTF-8";

	/**
	 * 往外发送的内容的编码,默认为 UTF-8
	 */
	var $incomingEncoding = 'UTF-8';

	var $interfaceType = 0;

	var $clt;


	/**
	 * @param string $url 			接口地址
	 * @param string $serialNumber 	用户名
	 * @param string $password		密码
	 * @param string $timeout		连接超时时间，默认0，为不超时
	 * @param string $response_timeout		信息返回超时时间，默认30
	 *
	 *
	 */
	function Client($url, $type = 0, $timeout = 0, $response_timeout = 30)
	{
		$this->url = $url;
		$this->interfaceType = $type;
		$this->timeout = $timeout;
		$this->response_timeout = $response_timeout;

		$this->setclient();
	}

	/**
	 * 设置发送内容的字符编码
	 * @param string $outgoingEncoding 发送内容字符集编码
	 */
	function setOutgoingEncoding($outgoingEncoding)
	{
		$this->outgoingEncoding =  $outgoingEncoding;
		if ($this->interfaceType != 0)
			$this->clt->setOutgoingEncoding($outgoingEncoding);
	}

	/**
	 * 设置接收内容 的字符编码
	 * @param string $incomingEncoding 接收内容字符集编码
	 */
	function setIncomingEncoding($incomingEncoding)
	{
		$this->incomingEncoding =  $incomingEncoding;
		if ($this->interfaceType != 0)
			$this->clt->setIncomingEncoding($incomingEncoding);
	}

	function getError()
	{
		if	($this->clt)
			return $this->clt->getError();
		return "没有匹配的业务类型";
	}

	function setclient()
	{
		global $isMode;
		switch ($this->interfaceType)
		{
			case 0:
				if ($isMode)
				{
					$this->clt = new SoapClient(null,array(
						"location" => $this->url,
						"uri"      => $this->namespace,  //资源描述符服务器和客户端必须对应
						"connection_timeout" => $this->timeout
			   			));
				}
				else
				{
					$this->clt = new soapclient($this->url, false, false, false, false, false, $this->timeout, $this->response_timeout);

					$this->clt->soap_defencoding = $this->outgoingEncoding;
					if (strval(stripos($this->outgoingEncoding, "utf")) != '')
						$this->clt->decode_utf8 = false;
					$this->clt->xml_encoding = $this->incomingEncoding;
				}
			break;
			case 1:
				$this->clt = new Post($this->url, $this->timeout);
			break;
			case 2:
				$this->clt = new Get($this->url, $this->timeout);
			break;
		}
	}

	//SOAP方式
	function SoapSendSms($arrParam)
	{
		global $soapinface,$defhandle,$isMode;

		if ($isMode)
		{
			$response = $this->clt->__soapcall($soapinface[$defhandle], $this->GetSoapParam($arrParam));
		}
		else
		{
			$response = $this->clt->call($soapinface[$defhandle], $arrParam, $this->namespace);
		}
		return $response;
	}

	//POST 方式
	function PostSendSms($arrParam)
	{
		global $defhandle,$arrret;
		$statusCode = $this->clt->call($arrret[$defhandle], $arrParam);
		return $statusCode;
	}

	//GET方式
	function GetSendSms($arrParam)
	{
		return $this->PostSendSms($arrParam);
	}

	//设置参数
	function setParam(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];
		if (isset($arr['multixmt']))
		{
			$mutilstr = '';
			$mos = explode(',', $arr['pszMobis']);
			$arr['pszMsg'] = iconv('UTF-8', 'GB2312', $arr['pszMsg']);
			for($i = 0; $i < $arr['iMobiCount']; ++$i)
			{
				$mutilstr.=",".$arr['flownum']."|".$arr['pszSubPort']."|".$mos[$i]."|".base64_encode($arr['pszMsg']);
			}
			$V['multixmt'] = substr($mutilstr, 1);
			return;
		}
		$V['pszMobis'] = $arr['pszMobis'];
		$V['pszMsg'] = $arr['pszMsg'];
		$V['iMobiCount'] = $arr['iMobiCount'];
		$V['pszSubPort'] = $arr['pszSubPort'];
		$V['MsgId'] = $arr['flownum'];
	}

	function GetSoapParam($V)
	{
		$arr = array();
		foreach( $V as $key=>$val)
		{
			$arr[] = new SoapParam($val, $key);
		}
		return $arr;
	}

	/**
	 * 发送短信
	 * @return int 操作结果状态码
	*/
	function sendSMS($arrInfo, $arrMobile)
	{
		$smsInfo = $arrInfo;
		if (empty($arrInfo["pszSubPort"]))
		{
			$smsInfo["pszSubPort"] = '*';
		}
		$smsInfo["pszMobis"] = implode(",", $arrMobile);
		$smsInfo["iMobiCount"] = count($arrMobile);
		echo '<pre>';var_dump(smsInfo);echo '</pre>';die;
		$this->setParam($smsInfo);

		switch ($this->interfaceType)
		{
			case 0:
				$result = $this->SoapSendSms($smsInfo);
			break;
			case 1:
				$result = $this->PostSendSms($smsInfo);
			break;
			case 2:
				$result = $this->GetSendSms($smsInfo);
			break;
		}

		return $result;
	}

	/**
	 * 不同内容群发
	 * @return int 操作结果状态码
	*/
	function SefsendSMS($arrInfo)
	{
		$smsInfo = $arrInfo;
		$this->setTwoParamEx($arrInfo);
		switch ($this->interfaceType)
		{
			case 0:
				$result = $this->SoapSendSms($smsInfo);
			break;
			case 1:
				$result = $this->PostSendSms($smsInfo);
			break;
			case 2:
				$result = $this->GetSendSms($smsInfo);
			break;
		}

		return $result;
	}

	function SoapComm($arrParam)
	{
		global $soapinface,$defhandle,$arrret,$isMode;
		if ($isMode)
			$statusCode = $this->clt->__soapcall($soapinface[$defhandle], $this->GetSoapParam($arrParam));
		else
			$statusCode = $this->clt->call($soapinface[$defhandle], $arrParam, $this->namespace);
		if (is_array($statusCode))
			return $statusCode[$arrret[$defhandle]];
		else if (gettype($statusCode) == 'object')
			return $statusCode->$arrret[$defhandle];
		return $statusCode;
	}

	function PostComm($arrParam)
	{
		global $defhandle,$arrret;
		$statusCode = $this->clt->call($arrret[$defhandle], $arrParam);
		return $statusCode;
	}

	function GetComm($arrParam)
	{
		return $this->PostComm($arrParam);
	}

	function setTwoParam(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];
		$V['iReqType'] = $arr['type'];
	}

	//设置账号密码两个参数
	function setTwoParamEx(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];
	}

	//获取
	function GetMoSMS($arrInfo)
	{
		if (!isset($arrInfo['type']))
		{
			$arrInfo['type'] = 1;
		}

		$this->setTwoParam($arrInfo);
		switch ($this->interfaceType)
		{
			case 0:
				$result = $this->SoapComm($arrInfo);
			break;
			case 1:
				$result = $this->PostComm($arrInfo);
			break;
			case 2:
				$result = $this->GetComm($arrInfo);
			break;
		}

		return $result;
	}

	function GetRpt($arrInfo)
	{
		$arrInfo['type'] = 2;
		return $this->GetMoSMS($arrInfo);
	}

	function GetMoAndRpt($arrInfo)
	{
		$arrInfo['type'] = 0;
		return $this->GetMoSMS($arrInfo);
	}

	///////////////////////////////////
	function GetMoney($arrInfo)
	{
		return $this->GetMoneySMS($arrInfo);
	}

	function setMoneyTwoParam(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];
	}

	//查询余额
	function GetMoneySMS($arrInfo)
	{
		if (!isset($arrInfo['type']))
		{
			$arrInfo['type'] = 1;
		}
		$this->setMoneyTwoParam($arrInfo);
		switch ($this->interfaceType)
		{
			case 0:
				$result = $this->SoapComm($arrInfo);
			break;
			case 1:
				$result = $this->PostComm($arrInfo);
			break;
			case 2:
				$result = $this->GetComm($arrInfo);
			break;
		}
		return $result;
	}
}
?>
