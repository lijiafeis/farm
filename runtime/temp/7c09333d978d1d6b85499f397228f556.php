<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"D:\phpStudy\WWW\farm\public/../application/util\view\recharge\wechat_pay.html";i:1516967260;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
<title>微信支付</title>
<link rel="stylesheet" type="text/css" href="__STATIC__/util/css/pay-common.css"/>
<link rel="stylesheet" type="text/css" href="__STATIC__/util/css/wx-pay.css"/>
</head>
<body>
<div class="app_container wx-pay">
	<div id="top">微信支付</div>
	<div id="chongzhi">		
		<div>应付金额 <em> <?php echo $gold; ?> </em> 元</div>
	</div>
	<div id="qr-wrap">
		<img id="qr" src=""/>
		<div style="display: flex;">
			<img id="sys" src="__STATIC__/util/img/pay-icons/qr-box.png"/>
			<div>
				<p>请使用微信扫一扫</p>
				<p style="margin-top: 3px;">扫描二维码支付</p>
			</div>
		</div>
	</div>
	<div class="tips">
		<em>温馨提示</em>
		<p>用其他手机微信扫码支付,或者截图保存图片在微信中打开扫一扫,点击右上角选择相册,选择图片识别支付!</p>
	</div>
</div>
</body>
<script src="__STATIC__/util/js/font.js" type="text/javascript" charset="utf-8"></script>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="__STATIC__/login/layer/layer.js"></script>
<script>
    var index = layer.load(2,{
        shade:[0.6,"#000"]
    });
    $.ajax({
        type:"post",
		url:"<?php echo url('createQr'); ?>",
		dataType:"json",
		data:{
            'code_url':"<?php echo $url; ?>"
		},
		success:function (data) {
            layer.close(index);
			if(data.code == 1){
				$("#qr").attr('src',data.info);
			}else{
			    layer.msg(data.info);
			}
        },
		error:function (data) {
			layer.close(index);
			layer.msg('网络错误');
        }

	});
</script>
</html>
