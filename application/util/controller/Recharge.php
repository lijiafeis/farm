<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/7 0007
 * Time: 19:19
 */
namespace app\util\controller;
use think\Controller;
use think\Exception;
use think\Request;

class Recharge extends Controller{
    

    public function recharge(){
        $gold = input('get.gold',0)*1;
        $user_id = input('get.user_id',0)*1;
        if(!$gold || !$user_id || $gold < 10){
            return 0;
        }
        $time = time();
        $data['user_id'] = $user_id;
        $data['gold'] = $gold;
        $data['create_time'] = $time;
        $data['order_sn'] = $user_id . $time . mt_rand(100,999);
        $res = db('order_rubbish') -> insertGetId($data);
        $html_url = action('home/Alipay/pay',['sn' => $res]);
        if ($html_url){
            $this -> redirect($html_url);
        }
    }

    public function pay(){
        $gold = input('get.gold',0)*1;
        $user_id = input('get.user_id',0)*1;
        if(!$gold || !$user_id){
            echo '请走正道';exit;
        }
        $this -> assign('gold',$gold);
        $this -> assign('user_id',$user_id);
        return $this -> fetch();
    }

    public function wechat_pay(){
        $gold = input('get.gold',0)*1;
        $user_id = input('get.user_id',0)*1;
        if(!$gold || !$user_id || $gold < 10){
            return 0;
        }
        //调起微信统一下单
        $time = time();
        $data['user_id'] = $user_id;
        $data['gold'] = $gold;
        $data['create_time'] = $time;
        $data['order_sn'] = $user_id . $time . mt_rand(100,999);
        $data['pay_type'] = 1;
        $res = db('order_rubbish') -> insertGetId($data);
        if($res){
            $domain = cache('config');
            if(!$domain){
                $domain = db('config') ->  find();
                cache('config',$domain);

            }
//            $notify_url = 'https://'.$_SERVER['HTTP_HOST'].url('util/Recharge/weixin_notify');
            $notify_url = $domain['domain'] . "/util/Recharge/weixinli_notify";
            $payInfo['appid'] = 'wx84d39894c5bddde9';
            $payInfo['mch_id'] = 1387709702;
            $payInfo['mch_key'] = 'askldfklsajfjsas4d6f54sa65d4f56s';
            $weixin = new \app\util\controller\Weixin();
            $code_url = $weixin -> get_qr_prepay_id($gold * 100,$data['order_sn'],$res,'farm',$payInfo,$notify_url);
            $this -> assign('url',$code_url);
            $this -> assign('gold',$gold);
            return $this -> fetch();
        }else{
            echo '请稍后重试';exit;
        }
    }

    public function createQr(){
        $code_url = input('post.code_url');
        if(!$code_url){
            $return['code'] = -1;
            $return['info'] = '缺少参数';
            return json($return);
        }
        import('phpqrcode.phpqrcode',EXTEND_PATH,'.php');
        $data =$code_url;
        $level = 'L';
        $size =4;
        $QRcode = new \QRcode();
        ob_start();
        $QRcode->png($data,false,$level,$size,2);
        $imageString = base64_encode(ob_get_contents());
        ob_end_clean();
        $imageString = "data:image/jpg;base64,".$imageString;
        $return['code'] = 1;
        $return['info'] = $imageString;
        return json($return);

    }

    public function weixinli_notify(){
        $postStr = file_get_contents("php://input");;
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $attach = trim($postObj->attach); //当前的id号；
        $order_sn = trim($postObj->out_trade_no); //订单号
        $total_fee = trim($postObj->total_fee)/100; //金额
        $orderInfo = db('order_rubbish') -> where(['id' => $attach]) -> find();
        if(!$orderInfo || $orderInfo['state'] == 1 || $order_sn != $orderInfo['order_sn']){
            file_put_contents('errorLog/alipay_order_error1.txt','order_sn' . $attach,FILE_APPEND);
            die('SUCCESS');
        }
        $model = db();
        $model -> startTrans();
        try{
            db('order_rubbish') -> where(['id' => $attach]) -> setField('state',1);
            unset($orderInfo['id']);
            $orderInfo['state'] = 1;
            $orderInfo['pay_time'] = time();
            $flag = db('order') -> field('id') -> where(['order_sn' => $orderInfo['order_sn']]) -> find();
            if($flag){
                die('SUCCESS');
            }
            $res = db('order') -> insert($orderInfo);
            if($res){
                setUserCache($orderInfo['user_id'],$total_fee,2,1);
                db('user') -> where(['user_id' => $orderInfo['user_id']]) -> setInc('gold',$total_fee);
                finance_log($orderInfo['user_id'],2,22,$orderInfo['gold']);
                $model -> commit();
                die('SUCCESS');
            }
        }catch (Exception $e){
            $model -> rollback();
            file_put_contents('errorLog/alipay_order_error.txt',json_encode($orderInfo) . $e,FILE_APPEND);
            cache("user".$orderInfo['user_id'],null);
            return 0;
        }

    }

    public function wechat_pay_h5(){
        $gold = input('get.gold',0)*1;
        $user_id = input('get.user_id',0)*1;
        if(!$gold || !$user_id || $gold < 10){
            return 0;
        }
       
        //调起微信统一下单
        $time = time();
        $data['user_id'] = $user_id;
        $data['gold'] = $gold;
        $data['create_time'] = $time;
        $data['order_sn'] = $user_id . $time . mt_rand(100,999);
        $data['pay_type'] = 2;
        $res = db('order_rubbish') -> insertGetId($data);
        if($res){
            $domain = cache('config');
            if(!$domain){
                $domain = db('config') ->  find();
                cache('config',$domain);

            }
            $notify_url = $domain['domain'] . "/util/Recharge/weixinli_notify";
            $payInfo['appid'] = 'wxff6da5d2859c1906';
            $payInfo['mch_id'] = 1384804202;
            $payInfo['mch_key'] = 'lkasdklfjslkdjfslafsa1df5sd4fsad';
            $weixin = new \app\util\controller\Weixin();
            $code_url = $weixin -> get_qr_prepay_id_h5($gold * 100,$data['order_sn'],$res,'芒果',$payInfo,$notify_url);
            if($code_url){
                header("location:{$code_url}");
            }
        }else{
            echo '请稍后重试';exit;
        }
    }

}