<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 13:41
 * 登录界面管理登录
 */
namespace app\admin\controller;
use think\Controller;

class User extends Controller{
    public function index(){
        return $this -> fetch();
    }

    /**
     * ajax传递过来账号密码，保存
     */
    public function check(){
        $username = addslashes(input('username',''));
        $password = addslashes(input('password',''));
        if(!$username || !$password){
            $arr['success'] = 0;
            return json($arr);
        }
        $ress = db('admin') -> field('id,password,login_time') ->  where(['username' => 'admin']) -> find();
        if($ress){
            //判断密码是否正确
            if($ress['password'] === xgmd5($password)){
                $ip = getIp();
                $ip = ip2long($ip);
                $data['ip'] = $ip;
                $data['last_time'] = $ress['login_time'];
                $data['login_time'] = time();
                $res = db('admin') -> where(['id' => $ress['id']]) -> update($data);
                if($res){
                    session('admin_info',$ress['id']);
                    //修改admin的ip 时间等信息
                    $arr['success'] = 1;
                    return json($arr);
                }else{
                    $arr['success'] = 0;
                    return json($arr);
                }

            }else{
                $arr['success'] = 0;
                return json($arr);
            }
        }else{
            $arr['success'] = 0;
            return json($arr);
        }

    }

}