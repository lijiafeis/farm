<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//加密方法
function xgmd5($pwd){
    $res = 'xiguakeji.com'.$pwd;
    return md5($res);
}

/*
       生成随机码
        */
function mkCode($_len){
    //通过类的参数获取需要的随机数的个数。这个值可以自由的指定
    $len = $_len;
    //我们生成的随机数的字母喝数字就是在这里面进行随机生成。
    $str = 'ABCDEFGHIGKLMNOPQRST1234567890';
    $code = '';
    //通过循环的生成随机数进行获取
    for($i = 0; $i < $len; $i++){
        //生成随机数
        $j = mt_rand(0,strlen($str)-1);
        //把随机生成的随机数拼接起来。
        $code .= $str[$j];
    }
    //把生成的随机数，保存在session中，便于当我们输入验证码是验证是否正确。
    session('imgCode',$code);
    return $code;
}

/**
 * @param int $_len 生成的字符的长度
 * @param int $_pixel 干扰点的个数
 * @param int $_width
 * @param int $_height
 * @return resource
 */
function makeImage($_len = 4,$_pixel = 100,$_width = 100,$_height = 20){
    //获取随机生成的随机码
    $code = mkCode($_len);
    //通过类的属性指定图形的大小,默认是100,20
    $canvas = imagecreatetruecolor($_width, $_height);
    //随机生成一个颜色的画笔
    $paint = imagecolorallocate($canvas,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
    //把背景的颜色进行改变，默认是黑色的。
    imagefill($canvas, 10, 10, $paint);
    //创建一个画随机码的笔，颜色也是随机生成的。
    $paint_str = imagecolorallocate($canvas,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
    //把随机码打印在画布上。
    imagestring($canvas, 4, 20, 2, $code, $paint_str);
    //绘制干扰点的颜色
    $paint_pixel = imagecolorallocate($canvas,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
    //通过类的属性指定需要多少个干扰点。
    for($i = 0; $i < $_pixel; $i++){
        //绘制不同的干扰点，而绘制的位置也是随机生成的。
        imagesetpixel($canvas, mt_rand(0,imagesx($canvas)),  mt_rand(0,imagesy($canvas)), $paint_pixel);
    }
    ob_start ();
    imagepng ($canvas);
    $image_data = ob_get_contents ();
    ob_end_clean ();
    $image_data = "data:image/png;base64,". base64_encode ($image_data);
    return $image_data;
}

function verifyTel($tel){
    if(!$tel){return false;}
    preg_match_all("/^1[345789]\d{9}$/",$tel,$array);
    if($array[0]){
        return true;
    }else{
        return false;
    }
}


function verifyPass($password){
    if(!$password){return false;}
    preg_match_all("/^[a-zA-Z]\w{5,17}$/",$password,$array);
    if($array[0]){
        return true;
    }else{
        return false;
    }
}

/*发送短信接口*/
function msg_everify($code,$tel,$appname){
    $msg = db('msg') -> find();
    $key = $msg['key'];
    $tpl_id = $msg['tel_id'];
    $tpl_value = '#code#='.$code.'&#app#='.$appname;//您设置的模板变量，根据实际情况修改
    $tpl_value = urlencode($tpl_value);
    $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
    $smsConf = array(
        'key'   => $key,
        'mobile'    => $tel, //接受短信的用户手机号码
        'tpl_id'    => $tpl_id,
        'tpl_value' => $tpl_value,
    );

    $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信
    if($content){
        $result = json_decode($content,true);
        $error_code = $result['error_code'];
        if($error_code == 0){
            //状态为0，说明短信发送成功
            return true;
        }else{
            file_put_contents('errorLog/sendMsg.txt',$content);
            //状态非0，说明失败
            return false;
        }
    }else{
        //返回内容异常，以下可根据业务逻辑自行修改
        return false;
    }
}
/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}

/**
 * @param $user_id 用户id
 * @param $state 1 表示当前用户支出 2 表示当前用户收入
 * @param $type 1 开垦土地支出 2 购买植物支出 3 提现扣除金币
 * 20 下级开垦土地收入 21 自己收获植物收入 22 充值收入 23 后台管理员充值 24 下级开垦土地上级金币
 * @param $gold 金币数
 * @param 额外的参数比如 下级名字  或植物名
 */
function finance_log($user_id,$state,$type,$gold,$xj_id = 0,$param = '',$plant_name='',$cate_name=''){
    if(!$user_id || !$type || !$gold || !$state){
        return false;
    }
    $finance['user_id'] = $user_id*1;
    $finance['state'] = $state*1;
    $finance['type'] = $type*1;
    $finance['gold'] = $gold*1;
    $finance['xj_userid'] = $xj_id*1;
    $finance['create_time'] = time();
    $finance['param'] = $param;
    $finance['plant_name'] = $plant_name;
    $finance['cate_name'] = $cate_name;
    $res = db('finance_log') -> insertGetId($finance);
    if($res){
        return $res;
    }else{
        return false;
    }
}

/**
 *  玩家购买普通类目植物，他的上3级每人获得1枚种子。
    玩家购买白银类目植物，他的上3级每人获得2枚种子。
    玩家购买黄金类目植物，他的上3级每人获得3枚种子。
 */
function setUserAccelerator($user_id,$land_plant_id,$price,$nickname = '',$plant_name = '',$cate_name = ''){
    if(!$user_id || !$land_plant_id || !$price){
        return 0;
    }
    $accelerator = cache('config');
    if(!$accelerator){
        $accelerator = db('config')  -> find();
        cache('config',$accelerator);
    }

    $number = $accelerator['accelerator_number'];
    if(!$number){
        $number = 1;
    }
    //得到上级
    $pInfo = db('user_contact') -> field('user_id,level') -> where(['children_id' => $user_id,'level' => ['elt',9]]) -> select();
    if(!$pInfo){
        return 0;
    }
    $acceleratorInfoAll = array();
    foreach ($pInfo as $k => $v){
        setUserCache($v['user_id'],$number,1,1);
        db('user') -> where(['user_id' => $v['user_id']]) -> setInc('accelerator_number',$number);
        //保存记录，谁种植植物，给谁返的加速器
        $acceleratorInfo['user_id'] = $v['user_id'];
        $acceleratorInfo['number'] = $number;
        $acceleratorInfo['land_plant_id'] = $land_plant_id;
        $acceleratorInfo['xj_userid'] = $user_id;
        $acceleratorInfo['create_time'] = time();
        $acceleratorInfoAll[] = $acceleratorInfo;
//        db('accelerator_log') -> insert($acceleratorInfo);
        //根据上级的直推人数确定要返还多少金币
        $xj_number = db('user') -> field('xj_number') -> where(['user_id' => $v['user_id']]) -> find();
        $xj_number = $xj_number['xj_number'];
        $gold = 0;
        if($v['level'] > 0 && $v['level'] <= 3){
            $gold = $price * 0.03;
        }else if($v['level'] > 3 && $v['level'] <= 6){
            if($xj_number >= 5){
                //可以拿到
                $gold = $price * 0.03;
            }else{
                continue;
            }
        }else if($v['level'] >6 && $v['level'] <= 9){
            if($xj_number >= 10){
                //可以拿到
                $gold = $price * 0.03;
            }else{
                continue;
            }
        }else{
            continue;
        }
        if(!$gold){
            continue;
        }
        $gold = floor($gold);
        setUserCache($v['user_id'],$gold,2,1);
        db('user') -> where(['user_id' => $v['user_id']]) -> setInc('gold',$gold);
        finance_log($v['user_id'],2,24,$gold,$user_id,$nickname,$plant_name,$cate_name);
    }
    model('accelerator_log') -> saveAll($acceleratorInfoAll);
}
function array_sort($array,$row,$type){
    $array_temp = array();
    foreach($array as $v){
        $array_temp[$v[$row]] = $v;
    }
    if($type == 'asc'){
        ksort($array_temp);
    }elseif($type='desc'){
        krsort($array_temp);
    }else{
    }
    $array = array();
    foreach ($array_temp as $value){
        $array[] = $value;
    }
//    halt($array);
    return $array;
}

//获取访客ip
function getIp()
{
    $ip=false;
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
        for ($i = 0; $i < count($ips); $i++) {
            if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/**
 * @param $user_id
 * @param $number
 * @param $type 1 表示加速器 2 表示金币
 * @param $oper 1 加数据 2 表示减数据
 * 修改个人的加速器个数
 */
function setUserCache($user_id,$number,$type,$oper){
    if(!$user_id || !$type || !$oper){return;}
    $cacheInfo = cache('user'.$user_id);
    $data = array();
    if(!$cacheInfo){
        //获取用户的最新信息
        $userInfo = db('user')-> field('gold,accelerator_number')-> where(['user_id' => $user_id])-> find();
        $data['acc_number'] = $userInfo['accelerator_number'];
        $data['gold'] = $userInfo['gold'];
//        cache('user'.$user_id,$data);
    }else{
        $data['acc_number'] = $cacheInfo['acc_number'];
        $data['gold'] = $cacheInfo['gold'];
    }
    if($type == 1){
        if($oper == 1){
            $data['acc_number'] += $number;
        }else if($oper == 2){
            $data['acc_number'] -= $number;
            if($data['acc_number'] < 0){
                $data['acc_number'] = 0;
            }
        }
    }else if($type == 2){
        if($oper == 1){
            $data['gold'] += $number;
        }else if($oper == 2){
            $data['gold'] -= $number;
            if($data['gold'] < 0){
                $data['gold'] = 0;
            }
        }
    }

    cache('user'.$user_id,$data);
    return $data;
}

function pay($sn,$money,$aliay_number,$name){
    if(!$sn || !$money || !$aliay_number || !$name){
        return;
    }
    //header('content-type:text/html;charset=utf8');
    //$ip = $_SERVER["REMOTE_ADDR"];
    //return json(1);
    \Think\loader::import('aop.AopClient');
    $aop = new \AopClient ();
    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    $aop->appId = '2018012602084894';
    $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEA4lZX7RjoMNyhVhRrrBZR+OqsSdget/OKDZVFqohrpi5Vz6YSsucpe0FJm2D3IwCHilDWCCULWS1JMy7XsOVxzErtgj0iRzytUJRNR7Bgmr/YGRv0WcT70+hxBJAJA0xqoJstHiKBd7augLcvKRskVrqll4y0g+OdmFLrsn2Do7uBb/WeqY4G19aQSW3RnSiaa5EWLPpUHuWaQgNcf2RLlEmWnvkV0hJIgvK78GeROwbURjD+F++Sw9Bo3TwuCBcObq4M7/HR7AmaXSYQuhi6mvDren/fkH0wUdJDhAUDWAXtxEMMYbaKX/7VLZrwJK36HqnQOVynee0RJ4lBzLpwbQIDAQABAoIBAEtnzL9XDvRIbQ/KmdypSwIM3P11HTbX0mSYGK+p54Nj6H7Xq18jGHTR2X4EnhFxObbhG413GgLJzZtZvc5XgsQ3Kk27pFHraypvXhfGMUkdJReoco39zJBa3lxQyE/rA5MiX7Osd0m0+Qo0/WdKfZ7PbB/DZtiR2o1HAvNiUZsYWncHGaJ4Jvggc3uzWjGT4seUJocVGt1YZWXG/FJo27INiAe0F76/V7Dcx3U3mH8jVTNaKS8gJprEyfQeJSHPwhElHXTwV8hYy/9jf6TMm7pE2B25aDbvzQOYm6ChCQQ7el/4TKK8K1Y1sOKJL++gaoVM94FY6Yl+yxCTa0qTlFkCgYEA/dk/l+qErYwgiWSeNKpc2H6+j1ohDS5PhLJ279klut5EaHONWsR8UHtR+hI7SNh2vGZ1egOxwWcyZbI0TMumpAxHBK3Yi0UNOyAZ3sQq+HOZ0oFytY4xr5kMCKgVvReJGiAAwAw3nwWMr3BrKy6bHXAOTsw4HZca8oCblOso9DcCgYEA5EFoAGV8GDg7Lb+EJz5fjnuYstL+ZgddU8tLNbDCa+hmMM4Q9qb7FCS/NvO7/FRCtJR9cDm+y00Nw9fmiw8fughFhABfJcSMj1rhqzcjqbt3Dg/loPEPKtst7vYkxGtxDEFvJnldRKsryRw0LCFVjRjjKIZko78IYC2GGYYCtnsCgYEApE/cRwRJT2C1qtlTQnnH0WbxCC9p13NTi2xNamEfd/7pPscVB1zJrvq0DG+CqltbOAYGIq2DgNHAoG0iR1dHDUbZLWEuGq/eqZfUxwopWlrRhZ2+12AsLyKc1HmgYJ58Y0m10pnV4vwfnWviIrhvNTXUPRMZe6XUjoXKrzEseC8CgYEAq/SKQSIzJpvWGVTaXiYjHtgF5VIGzR5nNKVGd6A+F8Twl3vmU6rgJAC6/M8Jo8JmrlvfVBhsoAPghtWznLc8E43/sL4G8BDuQ2EX+UCE4W2U90cKmwB/iK2uIQPWFxNKCw2Qis+LcBvz1IIm28gRB0bkerckQie8S5iAGeJXUNkCgYEAhgJTRXxc2zoyXKS/d4a6xDenozJtvfNoed+3dGxO0/MWasvURQSXfbqkNcQJ9PLUnsoggyoLbuaR+3yfrVtgBxSuPrPfhtzxNjWrKchSv8h03mxQuhE+Clo7xECtDfzgJt4PIAHMbh1Yw8Qamy5tMB0CuM0iQNCwwrUmsKeTqwE=';
    $aop->alipayrsaPublicKey='';
    $aop->apiVersion = '1.0';
    $aop->signType = 'RSA2';
    $aop->postCharset='utf-8';
    $aop->format='json';
    $out_sn =$sn;
    //$out_sn ='167115045237252653';
    //查询转账是否已完成
    \Think\loader::import('aop.request.AlipayFundTransOrderQueryRequest');
    $record_request = new \AlipayFundTransOrderQueryRequest ();
    $record_data = '{
			 "out_biz_no":"'.$out_sn.'"
		 }';
    $record_request->setBizContent($record_data);
    $record = $aop->execute ( $record_request);
    $record_responseNode = str_replace(".", "_", $record_request->getApiMethodName()) . "_response";
    $record_resultCode = $record->$record_responseNode->code;
    if($record_resultCode == 10000){return $record_resultCode;exit;}

    \Think\loader::import('aop.request.AlipayFundTransToaccountTransferRequest');
    $request = new \AlipayFundTransToaccountTransferRequest ();

    $amount = $money;

    $payee_account = $aliay_number;
    $payee_real_name = $name;
    // $amount = '1';
    // $payee_account = '529157244@qq.com';
    // $payee_real_name = '于春峰';
    $data = '{
            "out_biz_no":"'.$out_sn.'",
            "payee_type":"ALIPAY_LOGONID",
            "payee_account":"'.$payee_account.'",
            "amount":"'.$amount.'",
            "payer_show_name":"欢乐农庄",
            "payee_real_name":"'.$payee_real_name.'",
            "remark":"欢乐农庄"
            }';
    $request->setBizContent($data);
    $result = $aop->execute ( $request);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
//return $resultCode;
    if(!empty($resultCode)&&$resultCode == 10000){
        return $resultCode;
    } else {
        return $result->$responseNode->sub_code;

    }
}


