<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"D:\phpStudy\WWW\farm\public/../application/admin\view\user\index.html";i:1515843895;}*/ ?>
﻿ <!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="renderer" content="webkit">
  <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
  <title>农场管理后台</title>

   <link rel="stylesheet" href="__STATIC__/admin/css/font-awesome.min.css">
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="__STATIC__/admin/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="__STATIC__/admin/css/base.css">

<link rel="stylesheet" type="text/css" href="__STATIC__/admin/css/style.css" tppabs="css/style.css" />
<style>
body{height:100%;background:#16a085;overflow:hidden;}
canvas{z-index:-1;position:absolute;}
</style>
 <!--粒子特效-->
<script src="__STATIC__/admin/js/Particleground.js" tppabs="js/Particleground.js"></script>
<script src="__STATIC__/admin/layer/layer.js"></script>
<script>
if(window !=top){
    top.location.href=location.href;  
}
$(document).ready(function() {
  //粒子背景特效
  $('body').particleground({
    dotColor: '#5cbdaa',
    lineColor: '#5cbdaa'
  });
	$(".submit_btn").click(function(){
        login();
	});
});
</script>
</head>
<body oncontextmenu=self.event.returnValue=false onselectstart="return false">
<dl class="admin_login">
 <dt>
  <strong>农场管理系统</strong>
  <em>Farm</em>
 </dt>
 <dd class="user_icon" style="margin-top:30px;">
  <input type="text" name="username" id="input1" placeholder="账号" class="login_txtbx"/>
 </dd>
 <dd class="pwd_icon">
  <input type="password" name="password" id="input2" placeholder="密码" class="login_txtbx"/>
 </dd>

 <dd style="margin-top:30px;">
  <input type="button" value="立即登陆"  data-loading-text="请稍候..." class="submit_btn"/>
 </dd>
 <dd>
  <p>© 2016-2017 西瓜科技 版权所有</p>
  <p>微信第三方专业开发</p>
 </dd>
</dl>
</body>
<script>
    document.onkeydown=function(event){
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if(e && e.keyCode==13){ // enter 键
//要做的事情
            login();
        }
    };

    function login(){
        var $btn = $(".submit_btn").button('loading');
        var username = $('#input1').val();if(username == ''){layer.closeAll();layer.msg("请输入用户名");$btn.button('reset');return;}
        var password = $('#input2').val();if(password == ''){layer.closeAll();layer.msg("请输入用户密码");$btn.button('reset');return;}
        $.ajax({
            type: "POST",
            url: "<?php echo url('check'); ?>?time="+new Date(),
            dataType: "json",
            data: {
                "username":username,
                "password":password,
            },
            success: function(json){
                if(json.success == 1){
                    layer.msg("登录成功，正在跳转到管理台");
                    setTimeout(function(){
                        location.href="<?php echo url('Index/index'); ?>";
                    },2000);
                }else{
                    layer.msg("帐号密码有误！");$btn.button('reset');
                }
            },
            error:function(){
                layer.msg("帐号密码有误！");$btn.button('reset');
            }
        });
    }
</script>
</html>