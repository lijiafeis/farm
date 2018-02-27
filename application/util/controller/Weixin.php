<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/26 0026
 * Time: 18:28
 */
namespace app\util\controller;
use think\Controller;

class Weixin extends Controller{
    //微信扫码支付的调用
    function get_qr_prepay_id($total_fee,$out_trade_no,$attach,$good_name,$payInfo,$notify_url){
        $nonce_str = $this -> createNonceStr();
        $ip = $_SERVER['SERVER_ADDR'];
        $sign = $this->signjiami1($nonce_str,$total_fee,$out_trade_no,$notify_url,$attach,$good_name,$ip,$payInfo);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = "<xml>
		   <appid>".$payInfo['appid']."</appid>
		   <attach>".$attach."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$payInfo['mch_id']."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <sign>".$sign."</sign>
		   <spbill_create_ip>".$ip."</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>NATIVE</trade_type>
		</xml>";
        $result = $this -> http_request($url,$data);
        $postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        $code_url = trim($postObj->code_url);
        return $code_url;
    }

     //生成长度16的随机字符串
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "z".$str;
    }

    private function signjiami1($nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name,$ip,$payInfo){
        //$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
        $string1 = "appid=".$payInfo['appid']."&attach=".$pay_id."&body=".$good_name."&mch_id=".$payInfo['mch_id']."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&spbill_create_ip=".$ip."&total_fee=".$total_fee."&trade_type=NATIVE";
        $result = md5($string1."&key=".$payInfo['mch_key']);
        return strtoupper($result);
    }

    private function signjiami1_h5($nonce_str,$total_fee,$out_trade_no,$notify_url,$pay_id,$good_name,$ip,$payInfo){
        //$key = "qazwsxedcrfvtgbyhnujmikolpqazwsx";
        $string1 = "appid=".$payInfo['appid']."&attach=".$pay_id."&body=".$good_name."&mch_id=".$payInfo['mch_id']."&nonce_str=".$nonce_str."&notify_url=".$notify_url."&out_trade_no=".$out_trade_no."&spbill_create_ip=".$ip."&total_fee=".$total_fee."&trade_type=MWEB";
        $result = md5($string1."&key=".$payInfo['mch_key']);
        return strtoupper($result);
    }


    //微信扫码h5支付的调用
    function get_qr_prepay_id_h5($total_fee,$out_trade_no,$attach,$good_name,$payInfo,$notify_url){
        $nonce_str = $this -> createNonceStr();
        $ip = $this -> get_client_ip();
        $sign = $this->signjiami1_h5($nonce_str,$total_fee,$out_trade_no,$notify_url,$attach,$good_name,$ip,$payInfo);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = "<xml>
		   <appid>".$payInfo['appid']."</appid>
		   <attach>".$attach."</attach>
		   <body>".$good_name."</body>
		   <mch_id>".$payInfo['mch_id']."</mch_id>
		   <nonce_str>".$nonce_str."</nonce_str>
		   <notify_url>".$notify_url."</notify_url>
		   <out_trade_no>".$out_trade_no."</out_trade_no>
		   <sign>".$sign."</sign>
		   <spbill_create_ip>".$ip."</spbill_create_ip>
		   <total_fee>".$total_fee."</total_fee>
		   <trade_type>MWEB</trade_type>
		</xml>";
        $result = $this -> http_request($url,$data);
        $postObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        $code_url = trim($postObj->mweb_url);
        return $code_url;
    }

    private function get_client_ip(){
        $cip = 'unknown';
        if($_SERVER['REMOTE_ADDR']){
            $cip = $_SERVER['REMOTE_ADDR'];
        }else if(getenv("REMOTE_ADDR")){
            $cip = getenv("REMOTE_ADDR");
        }
        return $cip;
    }

    //https请求(支持GET和POST)
    function http_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        //var_dump(curl_error($curl));
        curl_close($curl);
        return $output;
    }

}