<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"D:\phpStudy\WWW\farm\public/../application/admin\view\index\top.html";i:1514443574;}*/ ?>
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="__STATIC__/admin/js/bootstrap.min.js"></script>
<style>
.modal-dialog{margin-top:5px;}
.top_image{
    margin-left: 20px;
    margin-top:4px;
}
</style>
<link rel="stylesheet" href="__STATIC__/admin/css/base.css">
    <header class="header top"  oncontextmenu=self.event.returnValue=false onselectstart="return false">
    <img class="top_image" src="__STATIC__/admin/image/logo.png"/>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#myModal">
		<div class="top_close"><i class="glyphicon glyphicon-off"></i> 退出</div>
	</a>
    </header>
<!-- Button trigger modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-footer">
		<span style="float:left;font-size:16px;">确定要退出登录吗？</span>
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-success" onclick="out()">退出</button>
      </div>
    </div>
  </div>
</div>
<div type="button" id="notice" data-toggle="modal" data-target=".bs-example-modal-lg" style="width:0;height:0;"></div>

<script>
function out(){
	location.href="<?php echo url('out'); ?>";
}
</script>