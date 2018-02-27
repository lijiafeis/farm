<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/2 0002
 * Time: 15:22
 */
namespace app\automate\controller;
use think\Controller;

class Index1 extends Controller{


    public function index(){
        $userWithInfo  = db('user_withdraw_log') -> field('id,state,gold,user_id,type,name,alipay_number,create_time') -> where(['state' => 3]) -> order('id asc') -> limit(60,90) ->  select();
        if(!$userWithInfo){return;}
//        file_put_contents('/home/c.txt',2,FILE_APPEND);
        foreach ($userWithInfo as $k => $v){
            $order_sn = $v['create_time'] . $v['user_id'];
            $flag = pay($order_sn,$v['gold'],$v['alipay_number'],$v['name']);

            if($flag == 10000){
                //打款成功
                $data['state'] = 1;
                $data['order_sn'] = $order_sn;
                $data['success_time'] = time();
                db('user_withdraw_log') -> where(['id' => $v['id']]) -> update($data);
            }else if($flag == 'PAYEE_NOT_EXIST' || $flag == 'PAYEE_USER_INFO_ERROR'){
    //驳回，返回到用户账户
                $data['state'] = 2;
                $data['success_time'] = time();
                $res = db('user_withdraw_log') -> where(['id' => $v['id']]) -> update($data);
                if($res){
                    //给用户价钱
                    setUserCache($v['user_id'],$v['gold'],2,1);
                    $res = db('user') -> where(['user_id' => $v['user_id']]) -> setInc('gold',$v['gold']);
                }
            }else{
                file_put_contents('/home/b.txt',$flag . '...',FILE_APPEND);
                exit;
            }

        }
    }



}