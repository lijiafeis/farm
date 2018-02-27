<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/29 0029
 * Time: 14:45
 */
namespace app\admin\controller;
use think\Request;

class Main extends Action{

    public function index(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $type = input('post.type',1);
            $data = array();
            switch ($type){
                case 1:
                    //总用户量
                    $userNumber = db('user') -> count();
                    //有效用户量
                    $userLandNumber = db('user') -> where(['land_number' => ['gt',0]]) -> count();
                    //用户的金币总数
                    $gold = db('user') -> sum('gold');

                    $data['userNumber'] = $userNumber;
                    $data['userLandNumber'] = $userLandNumber;
                    $data['gold'] = $gold;
                    break;
                case 2:
                    //订单总金额
                    $orderMoney = db('order') -> where(['state' => 1]) -> sum('gold');

                    //当天订单金额
                    $currOrderMoney = db('order')
                        -> where(['state' => 1])
                        -> whereTime('pay_time','today')
                        -> sum('gold');

                    //提现申请
                    $withdrawSq = db('user_withdraw_log')
                        -> where(['state' => 0])
                        -> sum('gold');

                    //提现申请成功
                    $withdrawSuccess = db('user_withdraw_log')
                        -> where(['state' => 1])
                        -> sum('gold');
                    $data['orderMoney'] = $orderMoney;
                    $data['currOrderMoney'] = $currOrderMoney;
                    $data['withdrawSq'] = $withdrawSq;
                    $data['withdrawSuccess'] = $withdrawSuccess;
                    break;
                case 3:
//提现申请驳回
                    $withdrawFail = db('user_withdraw_log')
                        -> where(['state' => 2])
                        -> sum('gold');

                    //后台管理员充值
                    $adminCz = db('finance_log') -> where(['type' => 23]) -> sum('gold');

                    //开垦土地金币
                    $kaiKenLand = db('finance_log') -> where(['type' => 1]) -> sum('gold');

                    //种植植物发给金币
                    $plant = db('finance_log') -> where(['type' => 2]) -> sum('gold');

                    $data['withdrawFail'] = $withdrawFail;
                    $data['adminCz'] = $adminCz;
                    $data['kaiKenLand'] = $kaiKenLand;
                    $data['plant'] = $plant;
                    break;
                case 4:
                    //收获植物金币
                    $shPlant = db('finance_log') -> where(['type' => 21]) -> sum('gold');

                    //下级购买土地上级收获金币
                    $xjGold = db('finance_log') -> where(['type' => 20]) -> sum('gold');

                    //下级种植植物上级收获金币
                    $xjPlantGold = db('finance_log') -> where(['type' => 24]) -> sum('gold');
//提现申请成功
                    $setwithdrow = db('user_withdraw_log')
                        -> where(['state' => 3])
                        -> sum('gold');
                    $withdrowId = db('user_withdraw_log')
                        -> field('id')
                        -> where(['state' => 3])
                        -> order('id asc')
                        -> find();
                    $data['shPlant'] = $shPlant;
                    $data['xjGold'] = $xjGold;
                    $data['xjPlantGold'] = $xjPlantGold;
                    $data['setwithdrow'] = $setwithdrow;
                    $data['withdrowId'] = isset($withdrowId['id'])?$withdrowId['id']:'无';
                    break;
            }
            return json($data);
        }


    }

}