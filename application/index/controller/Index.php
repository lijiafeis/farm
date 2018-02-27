<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27 0027
 * Time: 17:06
 */
namespace app\index\controller;
use think\Controller;
use think\Exception;

class Index extends Controller{
    public function _initialize()
    {
        header('Access-Control-Allow-Origin:*');
        parent::_initialize();
    }

    public function index(){
        $id = input('get.id',0);
        if(!$id){
            $url = "https://farm.wechatchat.cn/app/download/index.html";
        }else{
            $url='index/Index/jumpRegister?id=' . $id;
            header("location:{$url}");exit;
        }

        $this -> redirect($url);
    }

    public function jumpRegister(){
        header("Content-type: text/html; charset=utf-8");
        $id = input('get.id',0);
        $url = '';
        if(!$id){
            echo '二维码已失效';exit;
        }else{
            $url = 'https://farm.wechatchat.cn/login/Login/register?id=' . $id;
        }
        $type = $this -> is_weixin();
//        if($type == 1){
            //在微信中
  //          return $this -> fetch();
    //    }else{
            //在外部浏览器
            $this -> redirect($url);
      //  }
    }

    //判断打开方式 1 微信 2 其他
    function is_weixin(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return 1;
        }
        return 2;
    }

    public function test(){
        
    }
}
