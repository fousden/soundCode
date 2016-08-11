<?php
function E($param)
{
	if (is_array($param) || gettype($param) == 'object')
		print_r($param);
	else
		print("{$param}<br>\n");
	exit;
}

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

function GetStanderXmlKey($strkey, $str, $start = 0)
{
	$strbegin = "<".$strkey;
	$strend = "</$strkey>";
	$pos = strpos($str, $strbegin, $start);
	if ($pos == "")
	{
		return "";
	}
	$strRet = substr($str, $pos + 1);
	$pos = strpos($strRet, ">");
	if ($pos == "")
	{
		return "";
	}
	$strRet = substr($strRet, $pos + 1);
	$pos = strpos($strRet, $strend);
	$strRet = substr($strRet, 0, $pos);
	return $strRet;
}

function GetArrXmlKey($strkey, $str)
{
	$strbegin = "<".$strkey;
	$arr = array();
	$pos = strpos($str, $strbegin);
	while ($pos != "")
	{
		$arr[] = GetStanderXmlKey($strkey, $str, $pos);
		$pos = strpos($str, $strbegin, $pos + strlen($strbegin));
	}
	return $arr;
}

function GetCodeMsg($code, $arrcode)
{
	$err = '';
	if (is_array($code))
		$err = $code[0];
	else
		$err = $code;
	if (isset($arrcode[$err]))
	{
		return $arrcode[$err];
	}
	return '';
}

function FillLens($str, $lens, $isfront = true, $ch = '0')
{
	$count = strlen($str);
	if ($count >= $lens)
		return $str;
	$count = $lens - $count;
	$addstr = '';
	while ($count-- > 0)
	{
		$addstr.=$ch;
	}
	if ($isfront)
		$strret = $addstr.strval($str);
	else
		$strret = strval($str).$addstr;
	return $strret;
}
//可能出错  原函数名cut_strs
//function cut_strs($string, $sublen, $start = 0, $code = 'utf-8')
//{
//	if($code == 'utf-8')
//	{
//		$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
//		preg_match_all($pa, $string, $t_string);
//		if(count($t_string[0]) - $start > $sublen)
//			return join('', array_slice($t_string[0], $start, $sublen));
//		return join('', array_slice($t_string[0], $start, $sublen));
//	}
//	else
//	{
//		$start = $start*2;
//		$sublen = $sublen*2;
//		$strlen = strlen($string);
//		$tmpstr = '';
//		for($i=0; $i< $strlen; $i++)
//		{
//			if($i>=$start && $i< ($start+$sublen))
//			{
//				if(ord(substr($string, $i, 1))>129)
//				{
//					$tmpstr.= substr($string, $i, 2);
//				}
//				else
//				{
//					$tmpstr.= substr($string, $i, 1);
//				}
//			}
//			if(ord(substr($string, $i, 1))>129)
//				$i++;
//		}
//		return $tmpstr;
//	}
//}

//function strLength($str, $charset='utf-8')
//{
//	if($charset=='utf-8')
//		$str = iconv('utf-8','gb2312',$str);
//	$num = strlen($str);
//	$cnNum = 0;
//	for($i=0;$i<$num;$i++)
//	{
//		if(ord(substr($str,$i+1,1))>127)
//		{
//			$cnNum++;
//			$i++;
//		}
//	}
//	$enNum = $num-($cnNum*2);
//	$number = ($enNum)+$cnNum;
//	return ceil($number);
//}

function singleMsgId($msgid, $arrmobile, $splitxt = ',', $clens = 4)
{
	$negative = '+';
	if (strpos($msgid, "-") == 0)
	{
		$negative = '-';
	}
	$fixid = substr($msgid, 0, strlen($msgid) - $clens);
	$chid = substr($msgid, strlen($msgid) - $clens);
	$ch = $chid;

	$strMsgId = "";
	$count = count($arrmobile);
	for ($i = 0; $i < $count; ++$i)
	{
		eval('$ch = '.'$chid' ."{$negative} {$i};");
		$strMsgId .= $arrmobile[$i].":{$fixid}".FillLens($ch, $clens)."{$splitxt}";
	}
	if ('' != $strMsgId)
		$strMsgId = substr($strMsgId, 0, -strlen($splitxt));
	return $strMsgId;
}

function longMsgId($msgid, $arrmobile, $lens, $splitxt = ',', $clens = 12)
{
	$negative = '+';
	if (strpos($msgid, "-") == 0)
	{
		$negative = '-';
	}
	$fixid = substr($msgid, 0, strlen($msgid) - $clens);
	$chid = substr($msgid, strlen($msgid) - $clens);
	$ch = $chid;

	$strMsgId = "";
	$count = count($arrmobile);
	for ($i = 0; $i < $count; ++$i)
	{
		eval('$ch = '.'$chid' ."{$negative} {$i};");
		for ($j = 0; $j < $lens; ++$j)
		{
			eval('$ch'."$negative=".($j * 17179869184).";");
			$strMsgId .= $arrmobile[$i].":{$fixid}".FillLens($ch, $clens)."{$splitxt}";
		}
	}
	if ('' != $strMsgId)
		$strMsgId = substr($strMsgId, 0, -strlen($splitxt));
	return $strMsgId;
}

?>