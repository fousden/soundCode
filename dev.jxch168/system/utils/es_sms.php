<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class sms_sender
{
	var $sms;

	public function __construct()
    {
		$sms_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms where is_effect = 1");

		if($sms_info)
		{
			$sms_info['config'] = unserialize($sms_info['config']);

			require_once APP_ROOT_PATH."system/sms/".$sms_info['class_name']."_sms.php";

			$sms_class = $sms_info['class_name']."_sms";

			$this->sms = new $sms_class($sms_info);
		}
    }


	public function sendSms($mobiles,$content,$handleType = 5,$sendType = '',$sendTime='')
	{
                //选择短信接口发送短信 ,$sendTime YY 一信通 EN 泉龙达
                if($sendType){
                    $sms_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms where class_name = '".$sendType."'");
                    if($sms_info)
                    {
                            $sms_info['config'] = unserialize($sms_info['config']);
                            require_once APP_ROOT_PATH."system/sms/".$sms_info['class_name']."_sms.php";
                            $sms_class = $sms_info['class_name']."_sms";
                            $this->sms = new $sms_class($sms_info);
                    }
                }

		if(!is_array($mobiles)){
			$mobiles = preg_split("/[ ,]/i",$mobiles);
                }
		if(count($mobiles) > 0 )
		{
			if(!$this->sms)
			{
				$result['status'] = 0;
			}
			else
			{
				$result = $this->sms->sendSms($mobiles,$content,$handleType,$sendTime);
			}
		}
		else
		{
			$result['status'] = 0;
			$result['msg'] = "没有发送的手机号";
		}

		return $result;
	}

        //查询剩余短信条数
        public function get_remain_msg(){
            $result = $this->sms->get_count_msg();
            return $result;
        }
}
?>