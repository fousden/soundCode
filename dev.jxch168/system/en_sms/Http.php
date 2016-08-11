<?php

class Http_Base
{
	var $postUrl;

	/**
	 * 往外发送的内容的编码,默认为 UTF-8
	 */
	var $outgoingEncoding = "UTF-8";

	/**
	 * 往内送的内容的编码,默认为 UTF-8
	 */
	var $incomingEncoding = 'UTF-8';

	var $timeout = 60;

	function Http_Base($url, $timeout = 60)
	{
		$this->postUrl = $url;
		$this->timeout = 60;
	}

	function setOutgoingEncoding($outgoingEncoding)
	{
		$this->outgoingEncoding =  $outgoingEncoding;
	}

	/**
	 * 设置接收内容 的字符编码
	 * @param string $incomingEncoding 接收内容字符集编码
	 */
	function setIncomingEncoding($incomingEncoding)
	{
		$this->incomingEncoding =  $incomingEncoding;
	}

	function setInBaseCode(&$Param)
	{
		if ($Param)
			return;
		foreach($Param as $k=>$v)
		{
			$Param[$k] = iconv("GB2312", "UTF-8", $v);
		}
	}

	function getError()
	{
		return "连接异常";
	}

	function ConvertBaseCode($str)
	{
		if (strval(stripos($this->outgoingEncoding, "UTF-8")) == '')
			return iconv('UTF-8', $this->outgoingEncoding, $str);
		return $str;
	}

}

/**
 短信平台
 */
class Post extends Http_Base
{
	function Post($url, $timeout = 60)
	{
		Http_Base::__construct($url, $timeout);
	}

	/**
	 * 发送短信
	 * @return int 操作结果状态码
	*/
	function call($strkey, $arrSmsInfo)
	{
		if (stripos($this->incomingEncoding, "UTF-8") === false)
		{
			$this->setInBaseCode($arrSmsInfo);
		}
		if (isset($arr['multixmt']))
			$data_string = "userId=".$arrSmsInfo['userId']."&password=".$arrSmsInfo['password']."&multixmt=".$arrSmsInfo['multixmt'];
		else
			$data_string = UrlUnion($arrSmsInfo);

		$info=parse_url($this->postUrl);
		$referrer=@$_SERVER["SCRIPT_URI"];
		if(!isset($info["port"]))
		{
			$info["port"]=80;
		}
		$fp = fsockopen($info["host"], $info["port"], $errno, $errstr, $this->timeout);
		$head = "POST ".$info['path']." HTTP/1.1\r\n";
		$head .= "Host: ".$info['host']."\r\n";
		$head .= "Content-type: application/x-www-form-urlencoded\r\n";
		$head .= "Content-Length: ".strlen($data_string)."\r\n";
		$head .= "\r\n";
		$head .= trim($data_string);

		$write = fputs($fp, $head);
		while (!feof($fp))
		{
			@$result.= fread($fp, 4096);
		}

		$result = $this->ConvertBaseCode($result);
		$arr = GetArrXmlKey($strkey, $result);
		return $arr[0];
	}
}

class Get extends Http_Base
{
	function Get($url, $timeout = 60)
	{
		Http_Base::__construct($url, $timeout);
	}

	function call($strkey, $arrSmsInfo)
	{
		if (stripos($this->incomingEncoding, "UTF-8") === false)
		{
			$this->setInBaseCode($arrSmsInfo);
		}
		$data_string=UrlUnion($arrSmsInfo);
		$info=parse_url($this->postUrl);
		$referrer=$_SERVER["SCRIPT_URI"];

		if(!isset($info["port"]))
		{
			$info["port"]=80;
		}
                $strm = stream_context_create(array(
                        'http' => array(
                            'timeout' => 5
                            )
                        )
                    );
		$result = file_get_contents($this->postUrl."?".$data_string,0,$strm);
		$result = $this->ConvertBaseCode($result);
		$arr = GetArrXmlKey($strkey, $result);
		return $arr;
	}
}
?>
