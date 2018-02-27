<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26 0026
 * Time: 18:01
 */
namespace app\home\controller;
use think\Exception;
use think\Request;
class User extends Action {
    
    /**
     * 获取用户的银行卡和支付宝等信息
     */
    public function getUserWithdrawinfo(){
        $userInfo = cache('withdraw_info'.$this->user_id);
        if(!$userInfo){
            $userInfo = db('user_withdraw_info') -> field('name,tel,alipay_number,bank_name,bank_number') -> where(['user_id' => $this -> user_id]) -> find();
            cache('withdraw_info'.$this->user_id,$userInfo);
        }
        //判断后台是否设置了最大提现的金额
        $withdraw_gold = cache('config');
        if(!$withdraw_gold){
            $withdraw_gold = db('config') -> find();
            cache('config',$withdraw_gold);
        }
        $userInfo['withdraw_gold'] = $withdraw_gold['withdraw_gold'];
        $return['code'] = '10000';
        $return['msg'] = $userInfo;
        return json($return);
    }

    /**
     * 设置账号
     */
    public  function setWithdrawInfo(){
        $data['name'] = input('post.name');
        $data['tel'] = $this -> user['username'];
        $data['alipay_number'] = input('post.alipay_number');
        $data['user_id'] = $this -> user_id;
        if(!$data['name'] || !$data['tel'] || !$data['alipay_number']){
            $return['code'] = '10001';
            $return['msg'] = '参数值缺失';
            return json($return);
        }
        //保存到user_withdraw_info里面
        $is_true = db('user_withdraw_info') -> field('id') -> where(['user_id' => $this -> user_id]) -> find();
        if($is_true){
            $res = db('user_withdraw_info') -> where(['user_id' => $this -> user_id]) -> update($data);
        }else{
            $res = db('user_withdraw_info') -> insert($data);
        }
        if($res){
            cache('withdraw_info'.$this->user_id,null);
            $return['code'] = '10000';
            $return['msg'] = '保存成功';
            return json($return);
        }else{
            $return['code'] = '10002';
            $return['msg'] = '保存成功,信息未改变';
            return json($return);
        }
    }

    /**
     * 用户的提现,两种提现方式
     * gold 有用户提现的金币数
     * type 1 支付宝 2 银行卡
     * 1 alipay_number
     * 2 bank_name bank_number
     */
    public function setUserWithdrawInfo(){
        $is_true = cache($this->user_id);
        if($is_true){
            $return['code'] = '10999';
            $return['msg'] = '请稍等';
            return json($return);
        }else{
            cache($this->user_id,1,3600);
        }
        $type = input('post.type',1);
        $gold = input('post.gold')*1;
        $gold = floor($gold);
        if(!$type || !$gold || $gold <= 0){
            $return['code'] = '10001';
            $return['msg'] = '参数值缺失';
            return json($return);
        }
        //判断用户提现的钱是否够
        $withdraw_info = cache('config');
        if(!$withdraw_info){
            $withdraw_info = db('config') -> find();
            cache('config',$withdraw_info);
        }

        $withdraw_gold = $withdraw_info['withdraw_gold'];
        if($gold < $withdraw_gold){
            $return['code'] = '10002';
            $return['msg'] = '最低提现' . $withdraw_gold . '金币';
            return json($return);
        }
//        dump($withdraw_info);exit;
        //如果后台设置了最大提现次数和最大提现金额，那么就要判断了,没有排除驳回的
        if($withdraw_info['withdraw_number'] || $withdraw_info['withdraw_max_gold']){
            $userWithdraw = db('user_withdraw_log') -> field('count(*) as number,sum(gold) as gold') -> where(['user_id' => $this -> user_id])
                -> where("state in (0,1)")
                -> whereTime('create_time','today') -> select();
            $userWithdraw = $userWithdraw[0];
            if($withdraw_info['withdraw_number']){
                if($userWithdraw['number'] >= $withdraw_info['withdraw_number']){
                    $return['code'] = '10002';
                    $return['msg'] = '当天最多提现' . $withdraw_info['withdraw_number'] . '次';
                    return json($return);
                }
            }
            if($withdraw_info['withdraw_max_gold']){

                if($userWithdraw['gold'] >= $withdraw_info['withdraw_max_gold']){
                    $return['code'] = '10002';
                    $return['msg'] = '当天最多提现' . $withdraw_info['withdraw_max_gold'] . '金币';
                    return json($return);
                }
            }
        }

        //判断用户是否有这么多金币提现
        $userInfo = db('user') -> field('gold') -> where(['user_id' => $this -> user_id]) -> find();
        if($gold > $userInfo['gold']){
            $return['code'] = '10003';
            $return['msg'] = '余额不足';
            return json($return);
        }
        //可以扣用户的钱了，保存在提现记录表中，同时记录流水
        $model = db();
        $model -> startTrans();
        try{
            //保存账户信息
            $data['user_id'] = $this -> user_id;
//            $is_true = db('user_withdraw_info')  -> where(['user_id' => $this -> user_id]) -> find();//直接用缓存的信息，更改的时候更新
            $is_true = cache('withdraw_info'.$this->user_id);
            if(!$is_true){
                $is_true = db('user_withdraw_info') -> field('name,tel,alipay_number,bank_name,bank_number') -> where(['user_id' => $this -> user_id]) -> find();
                cache('withdraw_info'.$this->user_id,$is_true);
            }
            $data['name'] = $is_true['name'];
            $data['tel'] = $is_true['tel'];
            //根据类型不同，获取不同的参数
            if($type == 1){
                $data['alipay_number'] = $is_true['alipay_number'];
                if(!$data['alipay_number']){
                    $return['code'] = '10005';
                    $return['msg'] = '请填写账号信息';
                    return json($return);
                }
            }else if($type == 2){
                $data['bank_name'] = $is_true['bank_name'];
                $data['bank_number'] =  $is_true['bank_number'];
                if(!$data['bank_number'] || !$data['bank_name']){
                    $return['code'] = '10001';
                    $return['msg'] = '参数值缺失';
                    return json($return);
                }
            }
            setUserCache($this->user_id,$gold,2,2);
            $res = db('user') -> where(['user_id' => $this -> user_id]) -> setDec('gold',$gold);
            if($res){
                //保存到提现记录表中
                $data['gold'] = $gold;
                $data['type'] = $type;
                $data['create_time'] = time();
                //用户支出金币，记流水账
                $finance_log_id = finance_log($this -> user_id,1,3,$gold);
                $data['finance_log_id'] = $finance_log_id;
                $log_id = db('user_withdraw_log') -> insertGetId($data);
                $data['id'] = $log_id;
                $model -> commit();
                // $h=date('G');
                // if($h<22 && $h > 7){
                //     if($gold <= 20){
                //         $this -> setUserWithdrawLog($data);
                //     }
                // }
                $return['code'] = '10000';
                $return['msg'] = '等待审核，9点~22点提现2小时内到账；22点后提现第二天9点前到帐。请及时获取最新二维码。';
                return json($return);
            }


        }catch (Exception $e){
            $model -> rollback();
            file_put_contents('errorLog/home_User_setUserWithdrawInfo.php',$e,FILE_APPEND);
            cache("user".$this->user_id,null);
            $return['code'] = '10004';
            $return['msg'] = '网络错误';
            return json($return);
        }


    }

    /**
     * 成功或驳回
     * type 1 成功， 2 驳回
     */
    private function setUserWithdrawLog($logInfo){
        //调用方法打款
        if($logInfo['type'] == 1){
            $order_sn = $logInfo['create_time'] . $logInfo['user_id'];
            $flag = pay($order_sn,$logInfo['gold'],$logInfo['alipay_number'],$logInfo['name']);
        }
        if($flag == 10000){
            $data['state'] = 1;
            $data['order_sn'] = $order_sn;
            $data['success_time'] = time();
            $res = db('user_withdraw_log') -> where(['id' => $logInfo['id']]) -> update($data);
        }else if($flag == 'PAYEE_NOT_EXIST' || $flag == 'PAYEE_USER_INFO_ERROR'){
            $data['state'] = 2;
            $data['success_time'] = time();
            $res = db('user_withdraw_log') -> where(['id' => $logInfo['id']]) -> update($data);
            if($res){
                //给用户价钱
                setUserCache($logInfo['user_id'],$logInfo['gold'],2,1);
                $res = db('user') -> where(['user_id' => $logInfo['user_id']]) -> setInc('gold',$logInfo['gold']);
            }
        }
    }


    /**
     * 前台用户充值，创建订单信息
     */
    public function createOrder(){
        $gold = input('post.gold')*1;
        $gold = intval($gold);
        $gold = floor($gold);
        if(!$gold || $gold <= 0){
            $return['code'] = '10001';
            $return['msg'] = '请输入充值金额';
            return json($return);
        }
        //判断是否有最低标准
        $recharge = cache('config');
        if(!$recharge){
            $recharge = db('config')  -> find();
            cache('config',$recharge);
        }

        if($gold < $recharge['recharge_gold']){
            echo '最低充值' . $recharge['recharge_gold'] . '金币';
            exit;
        }
        //判断当天最多充值2000
        $rechargeMoney = db('order') -> where(['user_id' => $this -> user_id,'state' => 1]) -> whereTime('pay_time','today') -> sum('gold');
        if($rechargeMoney >= 2000){
            $return['code'] = '10001';
            $return['msg'] = '当天最多充值金额不能超过2000';
            return json($return);
        }
        $domain = cache('config');
        if(!$domain){
            $domain = db('config') -> find();
            cache('config',$domain);
        }
        $url = $domain['domain'] . "/util/Recharge/pay?user_id=" . $this -> user_id . '&gold=' . $gold;
        $return['code'] = '10000';
        $return['msg'] = $url;
        return json($return);

    }

    /**
     * 得到用户的金币收支情况
     */
    public function getUserGoldLog(){
        $page = input('post.page',1);
        $number = input('post.number',20);
        $info = db('finance_log')
            -> alias('a')
            -> field("a.gold,a.state,a.type,a.create_time,FROM_UNIXTIME(a.create_time,'%m月%d日 %H:%i') as date,b.state as withdraw_state,a.param,a.plant_name,a.cate_name")
            -> join('xg_user_withdraw_log b','a.id = b.finance_log_id','left')
            -> where(['a.user_id' => $this -> user_id])
            -> page($page,$number)
            -> order('a.id desc')
            -> select();
//        foreach ($info as $k => $v){
//            $info[$k]['date'] = date('m月d日 H:i',$v['create_time']);
//        }
        $return['code'] = '10000';
        $return['msg'] = $info;
        return json($return);

    }

    /**
     * 获取发放加速器的记录
     */
    public function getAcceleratorLog(){
        $page = input('post.page',1);
        $number = input('post.number',20);
        $info = db('accelerator_log')
            -> alias('a')
            -> field("a.number,a.type,b.nickname,a.create_time,FROM_UNIXTIME(a.create_time,'%m月%d日 %H:%i') as date")
            -> join('xg_user b','a.xj_userid = b.user_id','left')
            -> where(['a.user_id' => $this -> user_id])
            -> order('a.id desc')
            -> page($page,$number)
            -> select();
        $return['code'] = '10000';
        $return['msg'] = $info;
        return json($return);
    }


    /**
     * 生成二维码
     */
    public function getUserQr(){
        //判断当前用户是否开启
        if(!$this -> user['land_number']){
            $is_true = db('user_land') -> where(['user_id' => $this -> user_id]) -> select();
            if(!$is_true){
                $return['code'] = '10001';
                $return['msg'] = '你还没开垦土地,不能生成二维码';
                return json($return);
            }
        }
//        $domain = db('config') -> field('domain') -> find();
        $url = 'http://gee.xtwjc.com/login/Login/register?id=' . $this -> user_id;
	    $url1 = "您的推荐码:" . $this -> user_id;
        $flag = action('util/CreateShare/createShare',['user_id' => $this -> user_id,'url' => $url]);
        if($flag){
            $array['qr'] = $flag;
            $array['url'] = $url1;
            $return['code'] = 10000;
            $return['msg'] = $array;
            return json($return);
        }

    }

    /**
     * 获取直推人数和下九级人数
     * state
     */
    public function getXjUser(){
        $number = db('user')
            -> alias('a')
            -> field('a.xj_number,b.*')
            -> join('xg_user_number b','a.user_id = b.user_id','left')
            -> where(['a.user_id' => $this -> user_id])
            -> find();
        $number1 = $number['number1'] + $number['number2'] + $number['number3'] + $number['number4'] + $number['number5'] + $number['number6'] + $number['number7'] + $number['number8'] + $number['number9'];
        $info['xj_number'] = isset($number['xj_number'])?$number['xj_number']:0;
        $info['total_number'] = isset($number1)?$number1:0;
        $return['code'] = 10000;
        $return['msg'] = $info;
        return json($return);
    }




}
