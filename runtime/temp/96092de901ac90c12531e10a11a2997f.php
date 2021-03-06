<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:71:"D:\phpStudy\WWW\farm\public/../application/admin\view\member\users.html";i:1516182252;}*/ ?>
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
	<div class="main-top"><span  aria-hidden="true"></span>会员列表</div>
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
							<button type="button"  class="btn btn-default">会员昵称</button>
							<div class="btn-group">
								<input type="text" id="nickname"  class="form-control">
							</div>
							<button type="button" class="btn btn-warning" onclick="getPage(1)">查询</button>
						</div>
					</div>

				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">列表</a></li>
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
        var nickname = $("#nickname").val();
        var index = layer.load(2,{
            shade:[0.6,'#000']
		})
        $.ajax({
            url:"<?php echo url('users'); ?>",
            type:"post",
            dataType:"json",
            data:{
                'page':a,
                'user_id':user_id,
                'tel':tel,
                'nickname':nickname
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
                ,limit:10
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
	    str = '';
	    str += '<table class="table table-striped"  style="font-size:14px;margin-top: 20px;"><th>user_id</th><th>昵称</th><th>电话</th><th>开地数量</th><th>金币</th><th>上级姓名</th><th>创建时间</th><th>加速器数量</th><th>是否禁用</th><th>操作</th>';
		for(i = 0; i < data.length; i++){
		    data[i]['create_time'] = new Date(parseInt(data[i]['create_time']) * 1000).toLocaleString().substr(0,17)
			if(data[i]['is_forbidden'] == 0){
		        data[i]['is_for'] = '未禁用';
		        data[i]['type'] = '禁用';
			}else{
                data[i]['is_for'] = '已禁用';
                data[i]['type'] = '解禁';
			}
			if(data[i]['p_name'] == null){
                data[i]['p_name'] = '无'
			}
        	str += '<tr style="font-size:13px;" class="data"><td>'+data[i]['user_id']+'</td>';
			str += '<td>'+data[i]['nickname']+'</td>';
			str += '<td>'+data[i]['username']+'</td>';
			str += '<td>'+data[i]['land_number']+'</td>';
			str += '<td>'+data[i]['gold']+'</td>';
			str += '<td>'+data[i]['p_name']+'</td>';
			str += '<td>'+data[i]['create_time']+'</td>';
			str += '<td>'+data[i]['accelerator_number']+'</td>';
			str += '<td>'+data[i]['is_for']+'</td>';
			str += '<td><button type="button" class="btn btn-warning" onclick="jinzhi('+data[i]['user_id']+','+data[i]['is_forbidden']+')">'+data[i]['type']+'</button><button type="button" class="btn btn-warning" onclick="chongzhi('+data[i]['user_id']+')">充值</button><button type="button" class="btn btn-warning" onclick="lookXj('+data[i]['user_id']+')">下级</button></td></tr>';
		}
		str += '</table>';
		$('#list').empty();
		$('#list').append(str);
	}
</script>
<script>
	//禁用用户
	function jinzhi(user_id,type){
	    if(type == 0){
	        str = '禁用'
		}else{
	        str = '解禁'
		}
        layer.confirm('确定'+str+'？', {
            btn: ['是，确认','否，再看看'] //按钮
        }, function(){
            var index = layer.load(2,{
                shade:[0.6,"#000"]
            });
            $.ajax({
                type: "POST",
                url: "<?php echo url('forbiddenUser'); ?>",
                dataType: "json",
                data: {"user_id":user_id,type:type},
                success: function(json){
                    layer.close(index)
                    if(json.code==1){
                       layer.msg(str+'成功', {icon: 1});
                    }else{
                        layer.msg("处理失败，请重新尝试");
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

	//给用户充值
	function chongzhi(user_id) {
        //prompt层
        layer.prompt({title: '请输入充值金币数', formType: 4}, function(number, index){
			//判断number是否是数字
			if(isNaN(number)){
			    layer.msg('请输入数字');
				return;
			}
            layer.close(index);
			//是数字
			var index = layer.load(2,{
			    shade:[0.6,'#000']
			})
			$.ajax({
			    url:"<?php echo url('setUserGold'); ?>",
				type:"post",
				dataType:"json",
				data:{
			        'user_id':user_id,
					'gold':number
				},
				success:function(data){
			        layer.close(index);
					if(data.code == 1){
					    layer.msg(data.info);
					}else{
                        layer.msg(data.info);
					}
				},
				error:function(data){
				    layer.close(index);
				    layer.msg('网络错误,请稍后重试');
				}
			});
        });
    }

    //查看下级用户
    function lookXj(user_id){
        var index = layer.load(2,{
            shade:[0.6,'#000']
        })
        $.ajax({
            url:"<?php echo url('getUserXj'); ?>",
            type:"post",
            dataType:"json",
            data:{
                'user_id':user_id
            },
            success:function(data){
                layer.close(index);
                if(data.code == 1){
                    setXjUserInfo(data.info);
                }else{
                    layer.msg(data.info);
                }
            },
            error:function(data){
                layer.close(index);
                layer.msg('网络错误,请稍后重试');
            }
        });

	}

	function setXjUserInfo(userInfo){
        str = '';
        str += '<table style="text-align: center" class="table"  border="1" cellspacing="0" width="500px"><th>user_id</th><th>昵称</th><th>电话</th>';
        for(i = 0; i < userInfo.length; i++){
            str += '<tr><td>'+userInfo[i]['user_id']+'</td><td>'+userInfo[i]['nickname']+'</td><td>'+userInfo[i]['username']+'</td></tr>';
		}
		str += '</table>';
        //页面层-自定义
        //自定页
//        layer.open({
//            type: 1,
//            skin: 'layui-layer-demo', //样式类名
//            closeBtn: 0, //不显示关闭按钮
//            anim: 3,
//            shadeClose: true, //开启遮罩关闭
//            content: str
//        });
        //页面层
        layer.open({
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['420px', '240px'], //宽高
            content: str
        });
	}
</script>
