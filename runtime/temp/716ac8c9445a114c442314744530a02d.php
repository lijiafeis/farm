<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"D:\phpStudy\WWW\farm\public/../application/admin\view\main\index.html";i:1515551367;}*/ ?>
﻿<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit">
	<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
	<title>统计</title>
	<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="__STATIC__/admin/css/base.css">
	<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
	<script src="__STATIC__/admin/js/bootstrap.min.js"></script>
	<script src="__STATIC__/admin/js/Chart.min.js"></script>
</head>
<body>
<style>
	.view{padding:30px 0;background:#13cbae5;margin:10px 20px;color:#fff;text-align:center;}
	.view:hover{background:#133afd9;}
	.number{font-size:30px;}
</style>
<div class="well">
	<div class="col-sm-12 alert-success" style="font-size:16px;padding:10px 20px;margin-bottom:10px;">统计</div>
	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="orderMoney">#</em><div class="title">总订单金额</div>
			</div>
		</div>
	</div>
	<div class="col-sm-3">
		<div class="view" style="background:#5cb85c">
			<div class="inner">
				<em class="number" id="currOrderMoney">#</em><div class="title">当天订单金额</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#f0ad4e">
			<div class="inner">
				<em class="number" id="withdrawSq">#</em><div class="title">提现申请</div>
			</div>
		</div>
	</div>



	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="withdrawSuccess">#</em><div class="title">提现成功</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="withdrawFail">#</em><div class="title">提现驳回</div>
			</div>
		</div>
	</div>


	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="adminCz">#</em><div class="title">后台管理员充值</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="kaiKenLand">#</em><div class="title">开垦土地支出金币</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="plant">#</em><div class="title">种植植物支出金币</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="shPlant">#</em><div class="title">收获植物金币</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="xjGold">#</em><div class="title">下级开垦土地上级金币</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="xjPlantGold">#</em><div class="title">下级种植植物上级金币</div>
			</div>
		</div>
	</div>


	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="userNumber">#</em><div class="title">总用户量</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="userLandNumber">#</em><div class="title">开地用户量</div>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="view" style="background:#428bca">
			<div class="inner">
				<em class="number" id="gold">#</em><div class="title">用户金币总数</div>
			</div>
		</div>
	</div>

</div>
</body>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script>
	$.ajax({
		url:"<?php echo url('index'); ?>",
		type:"post",
		dataType:"json",
		data:{
			type:1
		},
		success:function (data) {
			$("#userNumber").text(data.userNumber);
			$("#userLandNumber").text(data.userLandNumber)
			$("#gold").text(data.gold);
			setData2();
		}
	});
	function setData2(){
        $.ajax({
            url:"<?php echo url('index'); ?>",
            type:"post",
            dataType:"json",
            data:{
                type:2
            },
            success:function (data) {
                $("#withdrawSuccess").text(data.withdrawSuccess);
                $("#orderMoney").text(data.orderMoney)
                $("#currOrderMoney").text(data.currOrderMoney);
                $("#withdrawSq").text(data.withdrawSq);
                setData3();
            }
        });
	}

	function setData3(){
        $.ajax({
            url:"<?php echo url('index'); ?>",
            type:"post",
            dataType:"json",
            data:{
                type:3
            },
            success:function (data) {
                $("#withdrawFail").text(data.withdrawFail);
                $("#adminCz").text(data.adminCz)
                $("#kaiKenLand").text(data.kaiKenLand);
                $("#plant").text(data.plant);
                setData4();
            }
        });
	}

    function setData4(){
        $.ajax({
            url:"<?php echo url('index'); ?>",
            type:"post",
            dataType:"json",
            data:{
                type:4
            },
            success:function (data) {
                $("#shPlant").text(data.shPlant);
                $("#xjGold").text(data.xjGold)
                $("#xjPlantGold").text(data.xjPlantGold);
                setData4();
            }
        });
    }

</script>


</html>