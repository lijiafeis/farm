<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/29 0029
 * Time: 09:43
 */
namespace app\admin\controller;
use think\Controller;
use think\Request;

class Zijin extends Action {

    /**
     * 提现申请
     */
    public function withdrawApply(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $tel = input('post.tel',0);
            $name = input('post.name',0);
            $type = input('post.type',0);
            $number = 20;
            $where = array();
            if($user_id){
                $where['user_id']= $user_id;
            }
            if($tel){
                $where['tel']= $tel;
            }
            if($name){
                $where['name']= $name;
            }
            if($type){
                $where['type'] = $type;
            }
            //只查询没有处理的请求记录
            $where['state'] = 0;
            $count=db('user_withdraw_log')->where($where) -> count();
            $data = db('user_withdraw_log')
                -> field('id,user_id,gold,type,name,tel,alipay_number,bank_name,bank_number,create_time')
                -> where($where)
                -> order('gold desc')
                -> page($page,$number)
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }

    /**
     * 成功或驳回
     * type 1 成功， 2 驳回
     */
    public function setUserWithdrawLog(){
        $is_true = session('is_true');
        if($is_true){
            $return['code'] = 0;
            $return['info'] = '正在处理';
            return json($return);
        }else{
            session('is_true',1);
        }
        $id = input('post.id',0)*1;
        $type = input('post.type',0)*1;
        if(!$id || ($type != 1 && $type != 2)){
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
        $logInfo = db('user_withdraw_log') -> field('state,gold,user_id,type,name,alipay_number,create_time') -> where(['id' => $id]) -> find();
        if($logInfo['state'] != 0){
            $return['code'] = 0;
            $return['info'] = '已处理';
            return json($return);
        }
        if($type == 1){
            //调用方法打款
            if($logInfo['type'] == 1){
                $order_sn = $logInfo['create_time'] . $logInfo['user_id'];
                $flag = pay($order_sn,$logInfo['gold'],$logInfo['alipay_number'],$logInfo['name']);
            }else{
                $flag = 10000;
                $order_sn = 1;
            }
            if($flag == 10000){
                $data['state'] = 1;
                $data['order_sn'] = $order_sn;
                $data['success_time'] = time();
                $res = db('user_withdraw_log') -> where(['id' => $id]) -> update($data);
            }else{
                $return['code'] = 0;
                $return['info'] = '打款失败';
                file_put_contents('errorLog/admin_Money_setUserWithdrawLog.php',$flag,FILE_APPEND);
                return json($return);
            }
        }else if($type == 2){
            //驳回，返回到用户账户
            $data['state'] = 2;
            $data['success_time'] = time();
            $res = db('user_withdraw_log') -> where(['id' => $id]) -> update($data);
            if($res){
                //给用户价钱
                setUserCache($logInfo['user_id'],$logInfo['gold'],2,1);
                $res = db('user') -> where(['user_id' => $logInfo['user_id']]) -> setInc('gold',$logInfo['gold']);
            }
        }
        if($res){
            $return['code'] = 1;
            $return['info'] = '成功';
            return json($return);
        }
        $return['code'] = 0;
        $return['info'] = '网络错误';
        return json($return);
    }


    /**
     * 成功或驳回
     * type 1 成功， 2 驳回
     */
    private function setUserWithdrawAll(){
        ini_set('max_execution_time','0');
        $is_true = session('is_true');
        if($is_true){
            $return['code'] = 0;
            $return['info'] = '正在处理';
            return json($return);
        }else{
            session('is_true',1);
        }
        //查询所有的没有打款的记录信息
        $logInfo = db('user_withdraw_log') -> field('id,state,gold,user_id,type,name,alipay_number,create_time') -> where(['state' => 0]) -> find();
        if(!$logInfo){
            $return['code'] = 0;
            $return['info'] = '没有数据要处理';
            return json($return);
        }
        $success_number = 0;
        $error_number = 0;
        foreach($logInfo as $k => $v){
            $order_sn = $logInfo['create_time'] . $logInfo['user_id'];
            $flag = pay($order_sn,$v['gold'],$v['alipay_number'],$v['name']);
            if($flag == 10000){
                //打款成功
                $data['state'] = 1;
                $data['order_sn'] = $order_sn;
                $data['success_time'] = time();
                $res = db('user_withdraw_log') -> where(['id' => $v['id']]) -> update($data);
                $success_number ++;
            }else{
                //打款失败
                file_put_contents('errorLog/admin_Money_setUserWithdrawLog.php',$flag.PHP_EOL,FILE_APPEND);
                //驳回，返回到用户账户
                $data['state'] = 2;
                $data['success_time'] = time();
                $res = db('user_withdraw_log') -> where(['id' => $v['id']]) -> update($data);
                if($res){
                    //给用户价钱
                    $res = db('user') -> where(['user_id' => $v['user_id']]) -> setInc('gold',$v['gold']);
                }
                $error_number ++;
            }
        }
        $return['code'] = 1;
        $return['info'] = '成功';
        $return['success'] = $success_number;
        $return['error'] = $error_number;
        return json($return);
    }

    /**
     * 对整个页面进行打款
     */
    public function setUserWithdrawLogAll(){
        ini_set('max_execution_time','0');
        $is_true = session('is_true');
        if($is_true){
            $return['code'] = 0;
            $return['info'] = '正在处理';
            return json($return);
        }else{
            session('is_true',1);
        }
        $ids = input('post.ids');
        $idLog = explode(',',$ids);
        if(!$idLog){
            $return['code'] = 0;
            $return['info'] = '网络异常';
            return json($return);
        }
        $ids = substr($ids,0,strlen($ids)-1);
        db('user_withdraw_log') -> where("id in ({$ids})") -> update(['state' => 3]);
        $success_number = 0;
        $error_number = 0;
//        foreach($idLog as $k => $v){
//            if(!$v){
//                continue;
//            }
//            //判断是否是待打款状态
//            //得到数组
//            $logInfo = db('user_withdraw_log') -> field('id,state,gold,user_id,type,name,alipay_number,create_time') -> where(['id' => $v]) -> find();
//            if(!$logInfo || $logInfo['state'] != 0){
//                $error_number ++;
//                continue;
//            }
//
//            $order_sn = $logInfo['create_time'] . $logInfo['user_id'];
//            $flag = pay($order_sn,$logInfo['gold'],$logInfo['alipay_number'],$logInfo['name']);
//            if($flag == 10000){
//                //打款成功
//                $data['state'] = 1;
//                $data['order_sn'] = $order_sn;
//                $data['success_time'] = time();
//                $res = db('user_withdraw_log') -> where(['id' => $v]) -> update($data);
//                $success_number ++;
//            }else if($flag==40004){
//                //驳回，返回到用户账户
//                $data['state'] = 2;
//                $data['success_time'] = time();
//                $res = db('user_withdraw_log') -> where(['id' => $v]) -> update($data);
//                if($res){
//                    //给用户价钱
//                    setUserCache($logInfo['user_id'],$logInfo['gold'],2,1);
//                    $res = db('user') -> where(['user_id' => $logInfo['user_id']]) -> setInc('gold',$logInfo['gold']);
//                }
//                $error_number ++;
//            }
//        }
        $return['code'] = 1;
        $return['info'] = '成功';
        $return['success'] = $success_number;
        $return['error'] = $error_number;
        return json($return);

    }


    /**
     * 后台提现的记录
     */
    public function withdrawLog(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $tel = input('post.tel',0);
            $name = input('post.name',0);
            $type = input('post.type',0);
            $number = 10;
            $where = array();
            if($user_id){
                $where['user_id']= $user_id;
            }
            if($tel){
                $where['tel']= $tel;
            }
            if($name){
                $where['name']= $name;
            }
            if($type){
                $where['type'] = $type;
            }
            //只查询没有处理的请求记录
            $where['state'] = ['gt',0];
            $count=db('user_withdraw_log')->where($where) -> count();
            $data = db('user_withdraw_log')
                -> field('id,user_id,gold,type,name,tel,alipay_number,bank_name,bank_number,create_time,success_time,state')
                -> where($where)
                -> page($page,$number)
                -> order('id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }

    /**
     * 订单 order
     */
    public function orderList(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $tel = input('post.tel',0);
            $name = input('post.name',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }
            if($tel){
                $where['b.username']= $tel;
            }
            if($name){
                $where['b.nickname']= $name;
            }
            $count = db('order') -> where($where)-> count();
            $data = db('order')
                -> alias('a')
                -> field('a.gold,a.user_id,a.order_sn,a.pay_time,a.state,b.nickname,b.username')
                -> join('xg_user b','a.user_id = b.user_id','left')
                -> where($where1)
                -> page($page,$number)
                -> order('id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }


    /**
     * 采摘记录 pick_log
     */
    public function pickLog(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }

            $count = db('pick_log') -> where($where)-> count();
            $data = db('pick_log')
                -> alias('a')
                -> field('a.gold,a.user_id,a.create_time,a.land_plant_id,b.nickname,c.name as plant_name')
                -> join('xg_user b','a.user_id = b.user_id','left')
                -> join('xg_land_plant c','a.land_plant_id = c.id','left')
                -> where($where1)
                -> page($page,$number)
                -> order('a.id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }

    /**
     * 加速器记录 accelerator_log
     */
    public function acceleratorLog(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $type = input('post.type',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }
            if($type == 1){
                $where['type'] = 0;
                $where1['a.type'] = 0;
            }else if($type == 2){
                $where['type'] = 1;
                $where1['a.type'] = 1;
            }
            $count = db('accelerator_log') -> where($where)-> count();
            $data = db('accelerator_log')
                -> alias('a')
                -> field('a.create_time,a.type,a.user_id,a.number,c.name as plant_name,b.nickname as xj_name,d.nickname')
                -> join('xg_user b','a.xj_userid = b.user_id','left')
                -> join('xg_user d','a.user_id = d.user_id','left')
                -> join('xg_land_plant c','a.land_plant_id = c.id','left')
                -> where($where1)
                -> page($page,$number)
                -> order('a.id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }


    /**
     * 用户的土地情况 user_land
     */
    public function landLog(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }
            $count = db('land_plant') -> where($where)-> count();
            $data = db('land_plant')
                -> alias('a')
                -> field('a.user_id,d.nickname,a.name as plant_name,a.price,a.cycle,a.income,a.number,a.count')
                -> join('xg_user d','a.user_id = d.user_id','left')
                -> where($where1)
                -> page($page,$number)
                -> order('a.id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }

    /**
     * 账户总流水 finance_log
     */
    public function financeLog(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $state = input('post.state',0);
            $type = input('post.type',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }
            if($state){
                $where['state']= $state;
                $where1['a.state']= $state;
            }
            if($type){
                $where['type']= $type;
                $where1['a.type']= $type;
            }
            $count = db('finance_log') -> where($where)-> count();
            $data = db('finance_log')
                -> alias('a')
                -> field('a.user_id,a.gold,a.state,a.type,a.create_time,a.param,b.nickname,c.nickname as xj_name')
                -> join('xg_user b','a.user_id = b.user_id','left')
                -> join('xg_user c','a.xj_userid = c.user_id','left')
                -> where($where1)
                -> page($page,$number)
                -> order('a.id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);
        }
    }





}