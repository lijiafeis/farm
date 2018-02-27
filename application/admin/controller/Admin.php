<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:59
 * 账号的管理  修改密码
 */
namespace app\admin\controller;
use think\Request;

class Admin extends Action{
    /**
     * 修改账户的密码
     */
    public function updatePassword(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $password = input('post.password');
            $password1 = input('post.password1');
            $password = xgmd5($password);
            $password1 = xgmd5($password1);
            if($password != $password1){
                $this -> error('两次输入的密码不一样','updatePassword');exit;
            }else{
                $res = db('admin') -> where(['id' => $this -> admin_id]) -> setField('password',$password);
                if($res){
                    //修改成功，然后重新登录
                    session('admin_info',null);
                    $this -> success('修改成功',url('User/index'));
                }
            }
        }

    }

    /**
     * 设置游戏的一些参数信息
     */
    public function setGameCs(){
        if(Request::instance() -> isGet()){
            $config = db('config') -> find();
            if($config['kefu_url']){
                preg_match_all("/\d{7,12}/",$config['kefu_url'],$array);
                $config['kefu_url'] = $array[0][0];
            }
            $this -> assign('config',$config);
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $data = input('post.','');
            if(!$data){
                $this -> error('错误','setGameCs');
            }
            $data['kefu_url'] = "http://wpa.qq.com/msgrd?v=3&uin=".$data['kefu_url']."&site=qq&menu=yes";
            $res = model('config') -> allowField(true) -> save($data,['id' => $data['id']]);
            if($res){
                cache('config',null);
                $this -> success('成功','setGameCs');
            }else{
                $this -> error('错误','setGameCs');
            }
        }
    }

}