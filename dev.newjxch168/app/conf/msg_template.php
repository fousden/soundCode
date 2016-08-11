<?php

     /*
     * 短信邮件模板
     *
     */
     return array(
         //短信
         '0'=>array(
                // 收款短信通知模板
                '1'  =>  '{$payment_notice.user_name}你好,你所下订单{$payment_notice.order_sn}的收款单{$payment_notice.notice_sn}金额{$payment_notice.money_format}于{$payment_notice.pay_time_format}支付成功',
             
                // 发送短信认证码模板 
                '2' => '手机号{$verify.mobile}金享财行注册手机验证码{$verify.code},请于{$verify.timeout}分钟内完成验证,如非本人操作,请忽略此短信.',
             
                //借款短信通知模板
                '3' => '{$notice.site_name}又有新借款啦!{$notice.deal_name},欢迎来投标{$notice.site_url}' ,

                //催款短信通知模板
                '4' => '尊敬的{$notice.site_name}用户 {$notice.user_name} ，您本期贷款的还款日是{$notice.repay_time_d}日，还款金额{$notice.repay_money}元，请按时还款。' ,

                //投标回款短信通知模板
                '5' => '尊敬的{$notice.user_name}，您投资的项目已完成还款，本息共计{$notice.repay_money}元，您可登录金享财行继续投资新的项目。回复TD拒收。' ,

                //提现成功通知短信
                '6' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的{$notice.carry_money}元提现已成功转入您的银行账户，请注意查收，感谢您的关注和支持。' ,

                //借入者流标短信模板
                '7' => '尊敬的用户{$notice.user_name}，遗憾的通知您，您于{$notice.deal_publish_time}发布的借款“{$notice.deal_name}”流标。' ,

                //借出者流标短信模板
                '8' => '尊敬的用户{$notice.user_name}，遗憾的通知您，您于{$notice.deal_load_time}所投的借款“{$notice.deal_name}”流标。' ,

                //借入者满标短信通知模板
                '9' => '尊敬的用户{$notice.user_name}，很高兴的通知您，您于{$notice.deal_publish_time}发布的借款“{$notice.deal_name}”满标。' ,

                //借出者满标短信通知模板
                '10' => '尊敬的（{$notice.user_name}），您投资的（{$notice.deal_name}）已募集成功，稍后将收到资金平台的出账通知短信，如有疑问请拨打 4000912828 回复TD拒收' ,

                //转让债权失败短信通知模板
                '11' => '尊敬的用户{$notice.user_name}，很遗憾的通知您，您于{$notice.transfer_time}转让的债权编号：“{$notice.transfer_id}”撤销了。' ,

                //转让债权成功短信通知模板
                '12' => '尊敬的用户{$notice.user_name}，很高兴的通知您，您于{$notice.transfer_time}转让的债权，编号：“{$notice.transfer_id}”已成功转让。' ,

                //借款审核失败短信通知模板
                '13' => '尊敬的用户{$notice.user_name}，遗憾的通知您，您于{$notice.deal_publish_time}发布的借款“{$notice.deal_name}”因“{$notice.delete_msg}”审核失败了。' ,

                //续约成功通知短信
                '14' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的“{$notice.deal_name}”续约已成功通过，感谢您的关注和支持。' ,

                //申请信用额度成功通知短信
                '15' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的{$notice.quota_money}元信用额度申请已成功，感谢您的关注和支持。' ,

                //申请信用额度失败通知短信
                '16' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的{$notice.quota_money}元信用额度申请 因 “{$notice.msg}” 审核失败了。' ,

                //借入者还款通知短信
                '17' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的借款“{$notice.deal_name}”在第{$notice.index}期{$notice.status}还款{$notice.all_money}元，感谢您的关注和支持。' ,

                //红包奖励短信通知模板
                '18' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得红包奖励，红包金额{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //收益率奖励短信通知模板
                '19' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得收益率奖励，收益率{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //积分奖励短信通知模板
                '20' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得积分奖励，积分值{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //礼品奖励短信通知模板
                '21' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得礼品奖励，礼品{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //支付密码修改验证码模板
                '22' => '手机号{$verify.mobile}金享财行支付密码修改验证码{$verify.code},请于{$verify.timeout}分钟内完成验证,如非本人操作,请忽略此短信.' ,

                //手机绑定修改验证码模板
                '23' => '手机号{$verify.mobile}金享财行手机绑定修改验证码{$verify.code},请于{$verify.timeout}分钟内完成验证,如非本人操作,请忽略此短信.' ,

                //红包放款通知模板
                '24' => '您好{$notice.user_name},您于{$notice.get_month}月{$notice.get_day}日领取的{$notice.money}元现金红包现已到账,您可登录金享财行进行使用。如有疑问请致电客服热线：4000912828。回复TD拒收。' ,

                //邀请好友投资通知模板
                '25' => '尊敬的{$notice.user_name},您邀请的{$notice.invite_name}已成功投资。小金特献上{$notice.money}元红包感谢您。登录金享财行即可查收。回复TD拒收！' ,

                //优惠券到期通知模板
                '26' => '尊敬的{$notice.mobile}您账户中有一张{$notice.name}券还有{$notice.date}天即将到期，请尽快使用。回复TD拒收！' ,

                //注册成功短信通知实名送红包模板
                '27' => '尊敬的%s，恭喜您成功注册金享财行，在实名认证后会送您%s元现金红包哦。回复TD拒收！' ,

                //绑卡成功模板
                '28' => '尊敬的%s，恭喜您绑卡成功，充值时注意上限问题及提现时注意第三方默认密码为您手机后6位，有任何问题请及时联系客服400-091-2828，回复TD拒收！' ,

                //找回登录密码验证码
                '29' => '尊敬的{$verify.mobile}金享财行找回登录密码验证码{$verify.code},请于{$verify.timeout}分钟内完成验证,如非本人操作,请忽略此短信。回复TD拒收！' ,

                //邀请活动奖励模板
                '30' => '您好{$notice.mobile}，您参与的老用户邀请活动，获得了{$notice.money}元提成奖励，现已到账您可登录金享财行查看。感谢您和您的朋友支持金享财行，如有疑问请致电客服热线：4000912828。回复TD拒收！' ,
         ),
         //邮件
         '1'=>array(
                //会员验证邮件
                '1' => '尊敬的用户您的验证码是【{$verify.code}】，此验证码只能用来注册。' ,

                //会员取回密码重新绑定邮件
                '2' => '尊敬的用户您的验证码是【{$verify.code}】。' ,

                //收款邮件通知模板
                '3' => '{$payment_notice.user_name}你好,你所下订单{$payment_notice.order_sn}的收款单{$payment_notice.notice_sn}金额{$payment_notice.money_format}于{$payment_notice.pay_time_format}支付成功' ,

                //借入者流标邮件模板
                '4' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>遗憾的通知您，您于{$notice.deal_publish_time}发布的借款“{$notice.deal_name}”流标，您的本次借款行为失败。&nbsp;</p>'."\r\n"
                    . '<p>您借款失败的可能原因为：&nbsp; </p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '1. 您没能按时提交四项必要信用认证的材料。'."\r\n"
                    . '<br><br>'."\r\n"
                    . '2. 您在招标期间没有筹集到足够的借款。&nbsp;&nbsp; '."\r\n"
                    . '<p>如果您属于认证未通过流标，为了您能够成功贷款，请凑齐申请贷款所需要的材料。您可以点击<a href="{$notice.help_url}" target="_blank">需要提供哪些材料？</a>来了解更多所需材料的详情。进行更多的信用认证将有助您获得更高的贷款额度。</p>'."\r\n"
                    . '<p>如果您属于招标到期流标，为了您能够成功贷款，请适度提高贷款利率，将有助您更快的获得贷款。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>点击 <a href="{$notice.send_deal_url}">这里</a>重新发布借款。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>',

                //借出者流标邮件模板
                '5' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>遗憾的通知您，您于{$notice.deal_load_time}所投的借款“{$notice.deal_name}”流标，您的本次投标行为失败。&nbsp;</p>'."\r\n"
                    . '<p>您所投的借款失败的可能原因为：&nbsp; </p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '1. 借款者没能按时提交四项必要信用认证的材料。<br><br>'."\r\n"
                    . '2. 借款者在招标期间没有筹集到足够的借款。&nbsp;&nbsp; '."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //标被留言邮件通知模板
                '6' => '<p>尊敬的用户{$notice.user_name}：</p>'."\r\n"
                    . '<p>您好，用户{$notice.msg_user_name}对您发布的借款列表“{$notice.deal_name}”进行了以下留言：</p>'."\r\n"
                    . '<p>“{$notice.message}”</p>'."\r\n"
                    . '<p>请您登录{$notice.site_name}借款详情页面查看答复。</p>'."\r\n"
                    . '<p>点击 <a href="{$notice.deal_url}" target="_blank">这里</a>进行答复。</p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注！</p>'."\r\n"
                    . '<p>{$notice.site_name}</p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！</p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="#" target="_blank">客服中心</a></p>' ,

                //标留言被回复邮件通知模板
                '7' => '<p>尊敬的用户{$notice.user_name}：</p>'."\r\n"
                    . '<p>您好，用户{$notice.msg_user_name}回复了您对借款列表“{$notice.deal_name}”的留言。具体回复如下：</p>'."\r\n"
                    . '<p>“{$notice.message}”</p>'."\r\n"
                    . '<p>点击 <a href="{$notice.deal_url}" target="_blank">这里</a>查看借款列表详情或进行投标。</p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注！</p>'."\r\n"
                    . '<p>{$notice.site_name}</p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！</p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="#" target="_blank">客服中心</a></p>' ,

                //催款邮件通知模板
                '8' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>您的借款“<a href="{$notice.deal_url}">{$notice.deal_name}</a>”本期还款日是{$notice.repay_time_d}日，还款金额{$notice.repay_money}元，请按时还款。【{$notice.site_name}】 </p>'."\r\n"
                    . '<p>点击 <a href="{$notice.repay_url}">这里</a>进行还款。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置rn&nbsp; </p>' ,

                //标完成度过半邮件通知模板
                '9' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>您的借款“<a href="{$notice.deal_url}">{$notice.deal_name}</a>”招标完成度超过50%【{$notice.site_name}】 </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //投标回款邮件通知模板
                '10' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>您好，您在{$notice.site_name}所投的的投标“<a href="{$notice.deal_url}">{$notice.deal_name}</a>”成功还款{$notice.repay_money}元 </p>'."\r\n"
                    . '{if $notice.need_next_repay}'."\r\n"
                    . '<p>本笔投标的下个还款日为{$notice.next_repay_time}，需还本息{$notice.next_repay_money}元。</p>'."\r\n"
                    . '{else}'."\r\n"
                    . '<p>本次投标共获得收益:{$notice.all_repay_money}元,{if $notice.impose_money}其中违约金为:{$notice.impose_money}元,{/if}本次投标已回款完毕！</p>'."\r\n"
                    . '{/if}'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //借出者满标邮件通知模板
                '11' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>很高兴的通知您，您于{$notice.deal_load_time}所投的借款“{$notice.deal_name}”满标，您的本次投标行为成功。&nbsp;</p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '<p>点击 <a href="{$notice.send_deal_url}">这里</a>查看您所发布借款。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //转让债权失败邮件通知模板
                '12' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>很遗憾的通知您，您于{$notice.transfer_time}转让的债权，编号：“{$notice.transfer_id}”因为“{$notice.bad_msg}”自动撤销了。&nbsp;</p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '<p>点击 <a href="{$notice.send_transfer_url}">这里</a>查看您所发布的转让信息。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //转让债权成功邮件通知模板
                '13' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>很高兴的通知您，您于{$notice.transfer_time}转让的债权，编号：“{$notice.transfer_id}”已成功转让。&nbsp;</p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '<p>点击 <a href="{$notice.send_deal_url}">这里</a>查看您所发布的转让信息。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //借入者满标邮件通知模板
                '14' => '<p>尊敬的用户{$notice.user_name}：&nbsp; </p>'."\r\n"
                    . '<p>很高兴的通知您，您于{$notice.deal_publish_time}发布的借款“{$notice.deal_name}”满标，您的本次借款行为成功。&nbsp;</p>'."\r\n"
                    . '<br><br>'."\r\n"
                    . '<p>点击 <a href="{$notice.send_deal_url}">这里</a>查看您所发布借款。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>感谢您对我们的支持与关注。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>{$notice.site_name}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>'."\r\n"
                    . '<p>注：此邮件由系统自动发送，请勿回复！&nbsp; </p>'."\r\n"
                    . '<p>如果您有任何疑问，请您查看 <a href="{$notice.help_url}" target="_blank">帮助</a>，或访问 <a href="{$notice.site_url}" target="_blank">客服中心</a></p>'."\r\n"
                    . '<p>如果您觉得收到过多邮件，可以点击 <a href="{$notice.msg_cof_setting_url}" target="_blank">这里</a>进行设置&nbsp; </p>' ,

                //红包奖励邮件通知模板
                '15' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得红包奖励，红包金额{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //收益率奖励邮件通知模板
                '16' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得收益率奖励，收益率{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //积分奖励邮件通知模板
                '17' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得积分奖励，积分值{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,

                //礼品奖励邮件通知模板
                '18' => '尊敬的{$notice.site_name}用户{$notice.user_name}，您的投标“{$notice.deal_name}”获得礼品奖励，礼品{$notice.gift_value}，奖励已于{$notice.release_date}发送。' ,
                         ),
         
          //其他
          '2'=>array(
                //流标通知
                '1' => '感谢您使用{$notice.shop_title}贷款融资，但有一些遗憾的通知您，您于{$notice.time}投标的借款列表{$notice.deal_name}流标，导致您本次所投的贷款列表流标的原因可能包括的原因：{$notice.bad_msg}' ,

                //满标通知
                '2' => '感谢您使用{$notice.shop_title}贷款融资，很高兴的通知您，您于{$notice.time}投标的借款列表{$notice.deal_name}满标' ,

                //债权转让撤销
                '3' => '您好，您在{$notice.shop_title}转让的债权 {$notice.url} {$notice.msg}',

                //等级提升
                '4' => '恭喜您，您已经成为{$notice.level_name}' ,

                //等级下降
                '5' => '很报歉，您已经降为{$notice.level_name}' ,

                //信用等级提升
                '6' => '恭喜您，您的信用等级升级到{$notice.level_name}.' ,

                //信用等级下降
                '7' => '很报歉，您的信用等级降为{$notice.level_name}.' ,

                //借款完成百分五十
                '8' => '您在{$notice.shop_title}的借款{$notice.url}完成度超过50%' ,

                //还款信息
                '9' => '您好，您在{$notice.shop_title}的借款{$notice.url}的借款第{$notice.key}期还款{$notice.money}元{$notice.content1}{$notice.content2}' ,

                //投标提前还款
                '10' => '您好，您在{$notice.shop_title}的投标{$notice.url}提前还款,本次投标共获得收益:{$notice.repay_money}元,其中违约金为:{$notice.impose_money}元,本次投标已回款完毕！' ,

                //借款提前还款
                '11' => '您好，您在{$notice.shop_title}的借款{$notice.url}成功提前还款{$notice.repay_money}元，其中违约金为:{$notice.impose_money}元,本笔借款已还款完毕！' ,

                //债权转让成功
                '12' => '您好，您在{$notice.shop_title}的债权{$notice.url}成功转让给：{$notice.url_name}' ,

                //提现
                '13' => '您于{$notice.time}提交的{$notice.money}提现申请我们正在处理，如您填写的账户信息正确无误，您的资金将会于3个工作日内到达您的银行账户.' ,

                //投标还款
                '14' => '您好，您在{$notice.shop_title}的投标{$notice.url}成功还款{$notice.money}元{$notice.content}' ,

                //借款回复
                '15' => '您好，用户{$notice.user_name}回复了您对借款列表 {$notice.url}的留言。具体回复如下：{$notice.msg}' ,

                //借款留言
                '16' => ' 您好，用户{$notice.user_name}对您发布的借款列表{$notice.url}进行了以下留言：“{$notice.msg}”' ,

                //认证审核通过
                '17' => '您好，您于{$sh_notice.time}在{$sh_notice.shop_title}提交的{$sh_notice.type_name}信息已经成功通过审核。您目前的信用分数为{$sh_notice.point}分({$sh_notice.dengji}级),信用额度为{$sh_notice.quota}' ,

                //借款还款提醒
                '18' => '您在{$sh_notice.time}的借款{$sh_notice.deal_name}，最近一期还款将于{$sh_notice.time}日到期，需还金额{$sh_notice.repay_money}元。' ,

                //认证审核失败
                '19' => '您好，您于 {$sh_notice.time}在{$sh_notice.shop_title}提交的{$sh_notice.type_name}信息未能通过审核。未能通过的原因是{$sh_notice.msg}.' ,

                //授信额度申请成功
                '20' => '您于{$sh_notice.time}提交的{$sh_notice.quota}授信额度申请成功，请查看您的申请记录。' ,

                //授信额度申请失败
                '21' => '您于{$sh_notice.time}提交的{$sh_notice.quota}授信额度申请申请被我们驳回，驳回原因{$sh_notice.msg};' ,

                //续约申请通过
                '22' => '您于{$sh_notice.time}提交的{$sh_notice.deal_name}续约申请通过。' ,

                //续约申请驳回
                '23' => '您于{$sh_notice.time}提交的{$sh_notice.deal_name}续约申请被我们驳回，驳回原因{$sh_notice.msg}.' ,

                //信用额度申请成功
                '24' => '您于{$sh_notice.time}提交的{$sh_notice.quota}信用额度申请成功，请查看您的申请记录。' ,

                //信用额度调整
                '25' => '您好，{$sh_notice.shop_title}审核部门经过综合评估您的信用资料及网站还款记录，将您的信用额度调整为：{$sh_notice.quota}元.' ,

                //信用额度申请失败
                '26' => '您于{$sh_notice.time}信用额度申请申请被我们驳回，驳回原因{$sh_notice.msg}.' ,

                //提现申请汇款成功
                '27' => '您于{$sh_notice.time}提交的{$sh_notice.money}提现申请汇款成功，请查看您的资金记录。' ,

                //提现申请驳回
                '28' => '您于{$sh_notice.time}提交的{$sh_notice.money}提现申请提现申请被我们驳回，驳回原因{$sh_notice.msg}.' ,
                             ), 
    );
