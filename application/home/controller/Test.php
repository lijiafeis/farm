<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/7 0007
 * Time: 16:12
 */
namespace app\home\controller;
use think\Controller;
use think\Request;

class Test extends Controller{

    public function _initialize()
    {
//        header('Access-Control-Allow-Origin:*');
//        header('Access-Control-Allow-Origin:http://127.0.0.1:5925');
        parent::_initialize();
//
//        $ip = getIp();
//        $url = $_SERVER['REQUEST_URI'];
//        if(strpos($url,'return_url') || strpos($url,'notify_url')){
//
//        }else if($ip != '1.194.22.114'){
//            exit;
//        }


        debug('begin');
dump(session(''));die();
        //获取用户的登录信息,在登录的时候，保存的
        $token = input('post.token',0);
        if(!$token){
            $return['code'] = 10100;
            $return['msg'] = '用户未登录';
            echo json_encode($return);exit;
        }
        unset($_POST['token']);
        session_id($token);
        $this -> user = session('user_id');dump($this->user);die();
//        $this -> user = db('user') -> find(1);
        if(!$this -> user){
            $return['code'] = 10100;
            $return['msg'] = '用户未登录';
            echo json_encode($return);exit;
        }else if($this -> user['is_forbidden']){
            $return['code'] = 10111;
            $return['msg'] = '当前用户被禁用';
            echo json_encode($return);exit;
        }else{
            //判断session_id是否一样
            $session_info = cache('session'.$this->user['user_id']);
            if(!$session_info){
                $session_info = db('session_info') -> field('session_id') -> where(['user_id' => $this -> user['user_id']]) -> find();
                cache('session'.$this->user['user_id']);
            }
            $session_id = $token;
            if($session_info['session_id'] != $session_id){
                $return['code'] = 99999;
                $return['msg'] = '账号在别处登录';
                echo json_encode($return);exit;
            }
        }
        $this -> user_id = $this -> user['user_id'];
    }
function index(){
    echo 1;
}
    public function __destruct(){
        if(isset($this -> user_id)){
            cache($this->user_id,null);
        }
        debug('end');
        $url =  $_SERVER['REQUEST_URI'];
        $time = debug('begin','end');
        if($time > 1){
            if(isset($this->user_id)){
                file_put_contents('log.php','time:' . date('Y-m-d H:i:s',time()) . '执行时间' .$time . ' 用户id:' . $this -> user_id . ' 接口:' . $url .'..'.PHP_EOL,FILE_APPEND);
            }
        }
    }
}