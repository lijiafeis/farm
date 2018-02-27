<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:78:"D:\phpStudy\WWW\farm\public/../application/admin\view\zijin\withdrawapply.html";i:1516949492;}*/ ?>
<link rel="stylesheet" href="__STATIC__/admin/css/base.css">
<link rel="stylesheet" href="__STATIC__/admin/css/bootstrap.min.css">
<link rel="stylesheet" href="__STATIC__/admin/css/base.css">
<link rel="stylesheet" href="__STATIC__/admin/layui/css/layui.css"  media="all">
<style>
	table button{
		width: 50px;
		height: 34px;
		margin-left: 5px;
	}
</style>
<div class="container-fluid main">
	<div class="main-top"><span  aria-hidden="true"></span>提现申请列表</div>
		<div class="main-content">
			<div>
				<div>
					<div class="well">
						<div class="btn-group" style="">

						</div><br/>
						<div class="btn-group" style="margin-top:20px;">
							<button type="button" class="btn btn-default">user_id</button>
							<div class="btn-group">
								<input type="text" id="user_id" class="form-control">
							</div>
							<button type="button" class="btn btn-default">手机号</button>
							<div class="btn-group">
								<input type="text" id="tel" class="form-control">
							</div>
							<button type="button" class="btn btn-default">姓名</button>
							<div class="btn-group">
								<input type="text" id="name" class="form-control" placeholder="用户提现时输入的姓名，不是昵称">
							</div>
							<button type="button" class="btn btn-default">类型</button>
							<div class="btn-group">
								<select id="type" class="form-control">
									<option value="0">全部</option>
									<option value="1">支付宝</option>
									<option value="2">银行卡</option>
								</select>
							</div>
							<button type="button" class="btn btn-warning" onclick="getPage(1)">查询</button>
						</div>
					</div>

				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">列表</a></li>
					<li><button onclick="daquan()" style="width: 70px;height: 34px;" class="btn btn-warning">打款</button></li>
				</ul>

				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="home">
						<div id="list">

						</div>
						<div class="pagelist"><div id="demo3"></div></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="__STATIC__/admin/js/bootstrap.min.js"></script>
<script src="__STATIC__/admin/layer/layer.js"></script>
<script src="__STATIC__/admin/layui/layui.js" charset="utf-8"></script>
<script>
	getPage(1);
	function getPage(a) {
        var user_id = $("#user_id").val();
        var tel = $("#tel").val();
        var name = $("#name").val();
        var type = $('#type').val();
        var index = layer.load(2,{
            shade:[0.6,'#000']
		})
        $.ajax({
            url:"<?php echo url('withdrawApply'); ?>",
            type:"post",
            dataType:"json",
            data:{
                'page':a,
                'user_id':user_id,
                'tel':tel,
                'name':name,
				'type':type
            },
            success:function (data) {
                layer.close(index);
                setLayPage(data.number,a);
                setDivData(data.data);
            },
            error:function (data) {
                layer.close(index);
                layer.msg('网络错误,请稍后重试');
            }
        });

    }
</script>
<script>
	function setLayPage(number,a){
        layui.use(['laypage', 'layer'], function(){
            var laypage = layui.laypage
                ,layer = layui.layer;
            //自定义首页、尾页、上一页、下一页文本
            laypage.render({
                elem: 'demo3'
                ,count: number
                ,curr:a//显示第几页
                ,limit:20
                ,first: '首页'
                ,last: '尾页'
                ,prev: '<em>←</em>'
                ,next: '<em>→</em>'
                ,jump: function(obj,first){
                    if(!first){
                        getPage(obj.curr);
                    }
                }
            });
        });
	}

	function setDivData(data){
//	    console.log(data);return;
	    str = '';
	    str += '<table class="table table-striped"  style="font-size:14px;margin-top: 20px;"><th>id</th><th>user_id</th><th>姓名</th><th>电话</th><th>金币</th><th>支付宝账号</th><th>开户行</th><th>银行账号</th><th>类型</th><th>申请时间</th><th>操作</th>';
		for(i = 0; i < data.length; i++){
		    data[i]['create_time'] = new Date(parseInt(data[i]['create_time']) * 1000).toLocaleString().substr(0,17)
        	str += '<tr style="font-size:13px;" class="data"><td class="log_id">'+data[i]['id']+'</td>';
        	str += '<td>'+data[i]['user_id']+'</td>';
			str += '<td>'+data[i]['name']+'</td>';
			str += '<td>'+data[i]['tel']+'</td>';
			str += '<td>'+data[i]['gold']+'</td>';
			if(data[i]['type'] == 1){
			    //支付宝
                str += '<td>'+data[i]['alipay_number']+'</td>';
                str += '<td></td>';
                str += '<td></td>';
                str += '<td>支付宝</td>';
			}else if(data[i]['type'] == 2){
                str += '<td></td>';
                str += '<td>'+data[i]['bank_name']+'</td>';
                str += '<td>'+data[i]['bank_number']+'</td>';
                str += '<td>银行卡</td>';
			}
			str += '<td>'+data[i]['create_time']+'</td>';
			str += '<td><button type="button" class="btn btn-warning" onclick="manage('+data[i]['id']+',1,this)">成功</button><button type="button" class="btn btn-warning" onclick="manage('+data[i]['id']+',2,this)">驳回</button></td></tr>';
		}
		str += '</table>';
		$('#list').empty();
		$('#list').append(str);
	}
</script>
<script>
    /**
	 *
     * @param id 这条记录的id
     * @param type 1 成功操作 2 驳回
     */
	function manage(id,type,is){
		if(id == '' || type == ''){
		    return;
		}
		str = type == 1 ? '成功' : '驳回';
        layer.confirm('确定'+str+'？', {
            btn: ['是，确认','否，再看看'] //按钮
        }, function(){
            var index = layer.load(2,{
                shade:[0.6,"#000"]
            });
            $.ajax({
                type: "POST",
                url: "<?php echo url('setUserWithdrawLog'); ?>",
                dataType: "json",
                data: {"id":id,type:type},
                success: function(json){
                    layer.close(index)
                    if(json.code==1){
                        layer.msg(str+'成功', {icon: 1});
						$(is).parent().parent().remove();
                    }else{
                        layer.msg(json.info);
                    }
                },
                error:function(){
                    layer.close(index)
                    layer.msg("发生异常！");
                }
            });
        }, function(){

        });
	}

	//点击上面的全部打款
	function daquan(){
        layer.confirm('确定当前页打款？', {
            btn: ['是，确认','否，再看看'] //按钮
        }, function(){
            //获取当前页的所有user_withdrow_log的id好
			str = '';
			$(".log_id").each(function () {
				log_id = $(this).text();
				if(log_id != ''){
					str += log_id + ',';
				}
            })
			if(str == ''){
			    layer.msg('当前页没有记录');return;
			}
            var index = layer.load(2,{
                shade:[0.6,"#000"]
            });
            $.ajax({
                type: "POST",
                url: "<?php echo url('setUserWithdrawLogAll'); ?>",
                dataType: "json",
                data: {"ids":str},
                success: function(json){
                    layer.close(index)
                    if(json.code==1){
                        layer.msg(json.info + json.success + '个,打款失败' + json.error);
                        setTimeout(function () {
							history.go(0);
                        },1300);
                    }else{
                        layer.msg(json.info);
                    }
                },
                error:function(){
                    layer.close(index)
                    layer.msg("发生异常！");
                }
            });
        }, function(){

        });
	}


</script>
