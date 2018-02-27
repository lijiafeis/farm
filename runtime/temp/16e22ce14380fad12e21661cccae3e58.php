<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"D:\phpStudy\WWW\farm\public/../application/admin\view\index\left.html";i:1515848444;}*/ ?>
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="__STATIC__/admin/css/iconfont/iconfont.css">
<link rel="stylesheet" href="__STATIC__/admin/css/base.css">
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="__STATIC__/admin/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
	$('.menu_title').click(function(){
		$(this).attr('data','1');
		$('.menu_title').each(function(){
			if($(this).attr('data') == 1){
				$(this).css('color','#fff');$(this).css('background','#44b549');$(this).find('i').css('color','#fff');
			}else{
				$(this).css('color','#555');$(this).css('background','#fff');$(this).find('i').css('color','#555');
			}
		});
		var obj = $(this).next('dd');
		if(obj.css("display") == 'block'){
			obj.css("display","none");$(this).attr('data','0');
		}else{
			$('dd').each(function(){
				$(this).css("display","none");
			});
			obj.css("display","block");
			$(this).attr('data','0');
		}
	});
	$('.menu dl dd ul li').click(function(){
		$('.menu dl dd ul li').each(function(){
			$(this).css('background','');
			$(this).css('color','');
		});
		$(this).css('background','#44b549');
		$(this).css('color','#fff');
	});
	
});
</script>
<style>
.menu_title:hover{background:#44b549;color:#fff;font-size:15px;}
.menu_title:hover i{color:#fff;}
.iconfont{font-weight:normal;}
</style>
<div class="left" oncontextmenu=self.event.returnValue=false onselectstart="return false">
  <div class="menu">
    <dl>
      <dt class="menu_title" data='0'><i class="icon iconfont add">&#xe668;</i>　账号设置</dt>
      <dd style="display:none;">
        <ul>
            <a href="<?php echo url('Admin/updatePassword'); ?>" target="main-frame"><li>修改密码</li></a>
            <a href="<?php echo url('Admin/setGameCs'); ?>" target="main-frame"><li>参数设置</li></a>
        </ul>
      </dd>
    </dl>
	<dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe617;</i>　会员管理</dt>
      <dd  style="DISPLAY: none">
        <ul>
          <a href="<?php echo url('Member/users'); ?>" target="main-frame"><li>会员列表</li></a>
        </ul>
      </dd>
    </dl>
    <dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe618;</i>　植物管理</dt>
      <dd  style="DISPLAY: none">
          <ul>
              <a href="<?php echo url('Plant/plantList'); ?>" target="main-frame"><li>植物列表</li></a>

          </ul>
      </dd>
    </dl>

	<dl>
      <dt class="menu_title"><i class="icon iconfont">&#xe618;</i>　资金管理</dt>
      <dd  style="DISPLAY: none">
        <ul>
          <a href="<?php echo url('Zijin/withdrawApply'); ?>" target="main-frame"><li>提现申请</li></a>
          <a href="<?php echo url('Zijin/withdrawLog'); ?>" target="main-frame"><li>提现记录</li></a>
          <a href="<?php echo url('Zijin/orderList'); ?>" target="main-frame"><li>订单记录</li></a>
          <a href="<?php echo url('Zijin/pickLog'); ?>" target="main-frame"><li>收获记录</li></a>
          <a href="<?php echo url('Zijin/landLog'); ?>" target="main-frame"><li>种植记录</li></a>
          <a href="<?php echo url('Zijin/acceleratorLog'); ?>" target="main-frame"><li>加速器记录</li></a>
          <a href="<?php echo url('Zijin/financeLog'); ?>" target="main-frame"><li>资金流水</li></a>
        </ul>
      </dd>
    </dl>

  <dl>
      <dt class="menu_title" data='0'><i class="icon iconfont">&#xe65a;</i>　商城统计</dt>
      <dd  style="DISPLAY: none">
          <ul>

              <a href="<?php echo url('Main/Index'); ?>" target="main-frame"><li>商城统计</li></a>

          </ul>
      </dd>
  </dl>
  </div>
  <div style="font-size:12px;color:#999;text-align:center;">Created By 郑州西瓜科技</div>
</div>
