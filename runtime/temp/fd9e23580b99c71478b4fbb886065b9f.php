<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:70:"D:\phpStudy\WWW\farm\public/../application/util\view\recharge\pay.html";i:1516966822;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
<title>金币充值</title>
<link rel="stylesheet" type="text/css" href="__STATIC__/util/css/pay-common.css"/>
<link rel="stylesheet" type="text/css" href="__STATIC__/util/css/wx-pay.css"/>
</head>
<body>
<div class="app_container chongzhi">	
	<div id="top">金币充值</div>
	<div id="chongzhi">
		<div>充值金币<?php echo $gold; ?>个</div>
		<div>应付金额 <em> <?php echo $gold; ?> </em> 元</div>
	</div>
	<div id="text" style="">选择支付方式</div>
	<a class="weui-panel__ft" onclick="pay(1,<?php echo $user_id; ?>,<?php echo $gold; ?>)">
		<div class="img-icon"><img src="__STATIC__/util/img/pay-icons/icon-ali.png"/></div>
		<div class="weui-cell weui-cell_access  weui-cell_link">
			<div class="weui-cell__bd">支付宝支付</div> 				    				    
			<span class="weui-cell__ft"></span>
		</div>    
	</a>
	<a class="weui-panel__ft" onclick="pay(2,<?php echo $user_id; ?>,<?php echo $gold; ?>)">
		<div class="img-icon"><img src="__STATIC__/util/img/pay-icons/icon-wx.png"/></div>
		<div class="weui-cell weui-cell_access  weui-cell_link" style="border-bottom: 0">
			<div class="weui-cell__bd">微信支付</div> 				    				    
			<span class="weui-cell__ft"></span>
		</div>    
	</a>
</div>
</body>
<script src="__STATIC__/util/js/font.js" type="text/javascript" charset="utf-8"></script>
<script>
	function pay(type,user_id,gold){
	    if(type != 1 && type != 2){
	        alert('请重新生成');return;
		}
		if(user_id == '' || gold == ''){
	        alert('请重新生成');return;
		}
	    if(type == 1){
	        //支付宝支付
			location.href="<?php echo url('recharge'); ?>?gold=" + gold + "&user_id=" + user_id;
		}else if(type == 2){
	        location.href="<?php echo url('wechat_pay'); ?>?gold=" + gold + "&user_id=" + user_id;
		}
	}
</script>
</html>
