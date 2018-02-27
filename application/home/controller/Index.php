<?php
/**
 * Created by PhpStorm.
 * User: 李佳飞
 * Date: 2017/12/26 0026
 * Time: 14:48
 * 前端的接口信息
 */
namespace app\home\controller;
use think\Controller;
use think\Exception;

class Index extends Action {


    /**
     * 获取用户的基本信息
     */
    public function getUserInfo(){
        //获取用户的最新信息
//        $userInfo = db('user')
//            -> alias('a')
//            -> field('a.gold,a.accelerator_number as accelerator_remain')
//            -> where(['a.user_id' => $this -> user_id])
//            -> find();
        debug('begin');
        $userCache = cache('user'.$this->user_id);
        if(!$userCache){
            //生成一个
            $userCache = setUserCache($this->user_id,0,1,1);
        }
        $userInfo['gold'] = $userCache['gold'];
        $userInfo['accelerator_remain'] = $userCache['acc_number'];
        $userInfo['user_id'] = 'ID:' . $this -> user['user_id'];
        $userInfo['nickname'] = $this -> user['nickname'];
        $userInfo['p_name'] = $this -> user['p_id'];
//        $userInfo['user_id'] = '';
        //得到配置信息，充值最低提现多少
        $config = cache("config");
        if(!$config){
            $config = db('config') -> find();
            cache('config',$config);
        }
        $userInfo['recharge_gold'] = $config['recharge_gold'];
        //$userInfo['accelerator_time'] = $config['accelerator_time'];
        //返回加速器的配置 4个豆四个小时
        $userInfo['acc_number'] = 4;
        $userInfo['acc_time'] = 4*3600;
        $userInfo['kefu'] = $config['kefu_url'];;
        debug('end');
        $time = debug('begin','end');
        if($time >= 1){
            if(isset($this->user_id)){
                file_put_contents('log_getUserinfo.php','time:' . date('Y-m-d H:i:s',time()) . '执行时间' .$time . ' 用户id:' . $this -> user_id  .'..'.PHP_EOL,FILE_APPEND);
            }
        }
        $return['code'] = '10000';
        $return['msg'] = $userInfo;
        return json($return);
    }

    /**
     * 得到用户的金币数和加速器的个数
     */
    public function getUserGold(){
        $userCache = cache('user'.$this->user_id);
        if(!$userCache){
            //生成一个
            $userCache = setUserCache($this->user_id,0,1,1);
        }
        $info['gold'] = $userCache['gold'];
        $info['accelerator_number'] = $userCache['acc_number'];
        $return['code'] = '10000';
        $return['msg'] = $info;
        return json($return);
    }

    /**
     * 返回用户的土地信息，返回用户开的地和用户在地上种植的植物的类型，并判断是否可以收获
     */
    public function getUserLand(){
        $userLand = cache('land' . $this->user_id);
        if($userLand){
            $return['code'] = '10000';
            $return['msg'] = $userLand;
            return json($return);
        }
        $userLand = db('user_land')
            -> alias('a')
            -> field('a.state,a.land_plant_id,a.land_id,b.id as land_plant_id,b.cate_id,b.plant_code,b.price,b.cycle,b.income,b.count,b.number,b.end_time')
            -> join('xg_land_plant b','a.land_plant_id = b.id','left')
            -> where(['a.user_id' => $this -> user_id])
            -> order('a.land_id asc')
            -> select();
        //定义land_id
        $kaidi_gold = cache('config');
        if(!$kaidi_gold){
            $kaidi_gold = db('config') -> find();
            cache('config',$kaidi_gold);
        }

        $kaidi_gold = $kaidi_gold['kaiken_gold'];
        $arr = [1,2,3,4,5,6,7,8,9];
        foreach ($userLand as $k => $v){
            $userLand[$k]['is_kaiken'] = 1;
            $userLand[$k]['land_gold'] = $kaidi_gold;
            if($v['state'] == 1){
                $landPlant['land_plant_id'] = $v['land_plant_id'];
                $landPlant['cate_id'] = $v['cate_id'];
                $landPlant['plant_code'] = $v['plant_code'];
                $landPlant['price'] = $v['price'];
                $landPlant['cycle'] = $v['cycle'];
                $landPlant['income'] = $v['income'];
                $landPlant['count'] = $v['count'];
                $landPlant['number'] = $v['number'];
                $landPlant['end_time'] = $v['end_time'];
                if($v['end_time'] <= time()){
                    $landPlant['type'] = 1;
                }else{
                    $landPlant['type'] = 0;
                }
                $userLand[$k]['land_plant'] = $landPlant;

            }
            unset($arr[array_search($v['land_id'],$arr)]);
            unset($userLand[$k]['land_plant_id']);
            unset($userLand[$k]['cate_id']);
            unset($userLand[$k]['plant_code']);
            unset($userLand[$k]['price']);
            unset($userLand[$k]['cycle']);
            unset($userLand[$k]['income']);
            unset($userLand[$k]['count']);
            unset($userLand[$k]['number']);
            unset($userLand[$k]['end_time']);
        }
        if($arr){
            //虚构没有开垦的土地
            foreach ($arr as $k => $v){
                $insertLnad['state'] = 0;
                $insertLnad['land_id'] = $v;
                $insertLnad['is_kaiken'] = 0;
                $insertLnad['land_gold'] = $kaidi_gold;;
                $userLand[] = $insertLnad;
            }
        }
//        dump($userLand);
        $userLand = array_sort($userLand,'land_id','asc');
//        halt($userLand);
        cache('land' . $this -> user_id,$userLand);
        $return['code'] = '10000';
        $return['msg'] = $userLand;
        return json($return);
    }



    /**
     * 用户开垦土地，使用土地开垦，前端传递过来是哪个土地
     */
    public function buyLand(){
        $land_id = input('post.land_id','');
        if(!$land_id){
            $return['code'] = '10001';
            $return['msg_test'] = '缺少土地id';
            return json($return);
        }
        //land_id是1 - 9 getUserLand设定
        $array = array(1,2,3,4,5,6,7,8,9);
        if(!in_array($land_id,$array)){
            $return['code'] = '10001';
            $return['msg_test'] = '缺少土地id';
            return json($return);
        }
        //判断当前的土地是否在user_land表中存在
        $info = db('user_land') -> where(['user_id' => $this -> user_id,'land_id' => $land_id]) -> find();
        if($info){
            $return['code'] = '10002';
            $return['msg'] = '当前土地已经开垦完毕,请勿重复开垦';
            return json($return);
        }
        //得到配置信息
        $landInfo = cache('config');
        if(!$landInfo){
            $landInfo = db('config') ->  find();
            cache('config',$landInfo);
        }
        if(!$landInfo || !$landInfo['kaiken_gold'] || !$landInfo['kaiken_p_gold']){
            $return['code'] = '10003';
            $return['msg_test'] = '参数缺失';
            return json($return);
        }
        //开垦土地判断金币是否足够
        $userInfo = db('user') -> field('nickname,gold,land_number,expense,p_id') ->  where(['user_id' => $this -> user_id]) -> find();
        if($userInfo['gold'] < $landInfo['kaiken_gold']){
            $return['code'] = '10004';
            $return['msg'] = '你的金币不足,请充值';
            return json($return);
        }
        //用户的余额充足,可以开垦
        $model = db();
        $model -> startTrans();
        try{
            setUserCache($this->user_id,$landInfo['kaiken_gold'],2,2);
            $updateUser['gold'] = $userInfo['gold'] - $landInfo['kaiken_gold'];
            $updateUser['land_number'] = $userInfo['land_number'] + 1;
            $updateUser['expense'] = $userInfo['expense'] + $landInfo['kaiken_gold'];
            $res = db('user') -> where(['user_id' => $this -> user_id]) -> update($updateUser);
            if($res){

                //当前用户开垦土地支出 type = 1
                finance_log($this -> user_id,1,1,$landInfo['kaiken_gold']);
                //添加数据到user_land表中
                $userLand['user_id'] = $this -> user_id;
                $userLand['land_id'] = $land_id;
                $userLand['create_time'] = time();
                $res = db('user_land') -> insert($userLand);
                if($res){
                    //加入公派
                    if($userInfo['land_number'] == 0){
                        action('util/Sanwei/contact_deal',['user_id' => $this -> user_id]);
                    }
                    //上级分佣，并记录金币的流逝
                    if($userInfo['p_id']){
                        setUserCache($userInfo['p_id'],$landInfo['kaiken_p_gold'],2,1);
                        db('user') -> where(['user_id' => $userInfo['p_id']]) -> setInc('gold',$landInfo['kaiken_p_gold']);
                        //下级开垦土地收入 type = 20
                        finance_log($userInfo['p_id'],2,20,$landInfo['kaiken_p_gold'],$this -> user_id,$userInfo['nickname']);
                    }
                    $model -> commit();
                    cache('land' . $this->user_id,null);
                    $return['code'] = '10000';
                    $return['msg'] = '开垦土地成功';
                    return json($return);
                }
            }

        }catch (Exception $e){
            $model -> rollback();
            cache('user'.$this->user_id,null);
            file_put_contents('errorLog/home_index_buyLand.php',$e,FILE_APPEND);
            $return['code'] = '10005';
            $return['msg'] = '网络错误,请稍后重试';
            return json($return);
        }
    }


    /**
     * 通过land_id 获取这块地的情况
     */
    public function getUserLandByLandId(){
        $land_id = input('post.land_id');
        if(!$land_id){
            $return['code'] = '10001';
            $return['msg_test'] = '参数不存在';
            return json($return);
        }
        $userLand = db('user_land')
            -> field('user_id,state,land_plant_id,land_id')
            -> where(['land_id' => $land_id,'user_id' => $this -> user_id])
            -> find();
        if(!$userLand || $userLand['user_id'] != $this -> user_id){
            $return['code'] = '10001';
            $return['msg_test'] = '参数错误';
            return json($return);
        }
        if($userLand['state'] == 1){
            //这块地上有植物，查出来植物并判断是否可以收获，
            $landPlant = db('land_plant')
                -> alias('a')
                -> field('a.id as land_plant_id,a.cate_id,a.name,a.plant_code,a.price,a.cycle,a.income,a.count,a.number,a.end_time,b.name as cate_name')
                -> join('xg_category b','a.cate_id = b.id','left')
                -> where(['a.id' => $userLand['land_plant_id']])
                -> find();
            //判断这颗植物是否可以收获
            if($landPlant){
                if($landPlant['end_time'] <= time()){
                    $landPlant['type'] = 1;
                }else{
                    $landPlant['type'] = 0;
                }
                $userLand['land_plant'] = $landPlant;
            }
        }
        unset($userLand['land_plant_id']);
        $return['code'] = '10000';
        $return['msg'] = $userLand;
        return json($return);
    }


    /**
     * 获取所有的类目信息
     */
    public function getAllCategory(){
        $cateInfo = db('category') -> field('id,name,price,cycle,income,count') -> select();
        $return['code'] = '10000';
        $return['msg'] = $cateInfo;
        return json($return);
    }

    /**
     * 得到后台创建的所有植物
     * 可以传递页数和每页显示的条数 同时可以通过类目的id进行分别
     */
    public function getAllPlant(){
        $is_true = cache('allplant');
        if($is_true){
            $array = $is_true;
        }else{
            $cateInfo = db('category')
                -> alias('a')
                -> field('a.id as c_id,b.id,b.name,b.plant_code,b.cate_id,a.name as cate_name,a.price,a.cycle,a.income,a.count')
                -> join('xg_plant b','a.id = b.cate_id','left')
                -> order('b.id asc')
                -> select();
            $array = array();
            $array[0] = ['cate_id' => 1,'plant' => array()];
            $array[1] = ['cate_id' => 2,'plant' => array()];
            $array[2] = ['cate_id' => 3,'plant' => array()];
            foreach ($cateInfo as $v){
                if($v['c_id'] == 1){
                    $array[0]['plant'][] = $v;
                }else if($v['c_id'] == 2){
                    $array[1]['plant'][] = $v;
                }else if($v['c_id'] == 3){
                    $array[2]['plant'][] = $v;
                }

            }
            cache('allplant',$array);
        }
        $return['code'] = '10000';
        $return['msg'] = $array;
        return json($return);
    }

    /**
     * 用户购买植物  传递用户想要购买的是哪种植物，传递过来plant_id
     */


    /**
     * 用户点击某一块地进行种植 land_id user_plant_id
     */
    public function growPlant(){
        $is_true = cache($this->user_id);
        if($is_true){
            $return['code'] = '10999';
            $return['msg'] = '请稍等';
            return json($return);
        }else{
            cache($this->user_id,1,3600);
        }
        $land_id = input('post.land_id',0);
        $plant_id = input('post.plant_id',0)*1;
        if(!$land_id || !$plant_id){
            $return['code'] = '10001';
            $return['msg'] = '参数缺失land_id';
            return json($return);
        }
        //判断当前这个land_id用户是否开启
        $landInfo = db('user_land') -> field('id,state') ->  where(['user_id' => $this -> user_id,'land_id' => $land_id]) -> find();
        if(!$landInfo || $landInfo['state'] == 1){
            $return['code'] = '10006';
            $return['msg'] = '当前土地上有植物,无法种植';
            return json($return);
        }

        $plantInfo = db('plant')
            -> alias('a')
            -> field('a.id,a.cate_id,a.name,a.plant_code,b.name as cate_name,b.price,b.cycle,b.income,b.count')
            -> join('xg_category b','a.cate_id = b.id','left')
            -> where(['a.id' => $plant_id])
            -> find();
        if(!$plantInfo || !$plantInfo['price']){
            $return['code'] = '10002';
            $return['msg'] = '当前购买的植物不存在';
            return json($return);
        }
        //判断用户的钱是否可以购买种子
        //开垦土地判断金币是否足够
        $userInfo = db('user') -> field('gold,land_number,expense,p_id,nickname,is_true') ->  where(['user_id' => $this -> user_id]) -> find();
        if($userInfo['gold'] < $plantInfo['price']){
            $return['code'] = '10003';
            $return['msg'] = '你的金币数不足';
            return json($return);
        }
        //金币充足，保存植物信息
        $userPlant['user_id'] = $this -> user_id;
        $userPlant['plant_id'] = $plantInfo['id'];
        $userPlant['cate_id'] = $plantInfo['cate_id'];
        $userPlant['name'] = $plantInfo['name'];
        $userPlant['plant_code'] = $plantInfo['plant_code'];
        $userPlant['price'] = $plantInfo['price'];
        $userPlant['cycle'] = $plantInfo['cycle'];
        $userPlant['income'] = $plantInfo['income'];
        $userPlant['count'] = $plantInfo['count'];
        $userPlant['create_time'] = time();
        $model = db();
        $model -> startTrans();
        try{
            setUserCache($this->user_id,$plantInfo['price'],2,2);
            $update['gold'] = $userInfo['gold'] - $plantInfo['price'];
            $update['expense'] = $userInfo['expense'] + $plantInfo['price'];
            $res = db('user') -> where(['user_id' => $this -> user_id]) -> update($update);
            if($res){
                finance_log($this -> user_id,1,2,$plantInfo['price'],0,$plantInfo['name']);
                $userPlant['state'] = 1;
                $res = db('user_plant') -> insertGetId($userPlant);
            }else{
                $model -> rollback();return;
            }

            $userPlantInfo['user_plant_id'] = $res;
            $userPlantInfo['user_id'] = $userPlant['user_id'];
            $userPlantInfo['cate_id'] = $userPlant['cate_id'];
            $userPlantInfo['name'] = $userPlant['name'];
            $userPlantInfo['plant_code'] = $userPlant['plant_code'];
            $userPlantInfo['price'] = $userPlant['price'];
            $userPlantInfo['cycle'] = $userPlant['cycle'];
            $userPlantInfo['income'] = $userPlant['income'];
            $userPlantInfo['count'] = $userPlant['count'];

            //用户有这块地和有这颗植物，可以种植，把user_plant => land_plant
            $landPlant = $userPlantInfo;
            $landPlant['create_time'] = time();
            $landPlant['start_time'] = time();
            $landPlant['end_time'] = time() + $userPlantInfo['cycle'];
            $re = db('land_plant') -> insertGetId($landPlant);
            if($re){
                //修改user_land的state
                $userLand['state'] = 1;
                $userLand['land_plant_id'] = $re;
                $res = db('user_land') -> where(['user_id' => $this -> user_id,'land_id' => $land_id]) -> update($userLand);
                if($res){

                    //修改上级人数
                    $this -> updateUserNumber($this -> user_id,$userInfo);

                    //修改当前植物为已种植
//                    db('user_plant') -> where(['id' => $user_plant_id]) -> setField('state',1);
                    //玩家购买普通类目植物，他的上3级每人获得1枚种子。
                    //玩家购买白银类目植物，他的上3级每人获得2枚种子。
                    //玩家购买黄金类目植物，他的上3级每人获得3枚种子。
                    setUserAccelerator($userPlantInfo['user_id'],$re,$landPlant['price'],$this -> user['nickname'],$userPlant['name'],$plantInfo['cate_name']);
                    $model -> commit();
                    cache('land' . $this -> user_id,null);

                    $return['code'] = '10000';
                    $return['msg'] = '种植成功';
                    return json($return);
                }else{
                    $model -> rollback();return;
                }

            }else{
                $model -> rollback();return;
            }
        }catch (Exception $e){
            $model -> rollback();
            cache('user'.$this->user_id,null);
            file_put_contents('errorLog/home_index_growPlant.php',$e,FILE_APPEND);
            $return['code'] = '10004';
            $return['msg'] = '网络错误,请稍后重试';
            return json($return);
        }


    }

    /**
     * 判断用户是否第一次开地，如果是，修改user表的直推人数，并修改上九级的user_number人数
     */
    public function updateUserNumber($user_id,$userInfo){
        if($userInfo['is_true']){
            return 0;
        }
        //第一次种植,修改user表的is_true和上级的xj_number
        db('user') -> where(['user_id' => $user_id]) -> setField('is_true',1);
        if($userInfo['p_id']){
            db('user') -> where(['user_id' => $userInfo['p_id']]) -> setInc('xj_number',1);
        }
        $info = db('user_contact') -> field('user_id,level') -> where(['children_id' => $user_id,'level' => ['elt',9]]) -> select();
        if(!$info){
            return 0;
        }

        foreach ($info as $k => $v){
            //找到上级根据$i把人数加一，这个人数user_number数据是在注册时候创建的
            db('user_number') -> where(['user_id' => $v['user_id']]) -> setInc('number'. $v['level'],1);
        }
    }



    /**
     * 点击某一块地进行收获 传递过来land_id
     */
    public function gainPlant(){
        $is_true = cache($this->user_id);
        if($is_true){
            $return['code'] = '10999';
            $return['msg'] = '请稍等';
            return json($return);
        }else{
            cache($this->user_id,1,60);
        }
        $land_id = input('post.land_id',0);
        if(!$land_id){
            $return['code'] = '10001';
            $return['msg_test'] = '缺少参数';
            return json($return);
        }
        $landPlantInfo = db('user_land')
            -> alias('a')
            -> field('a.id as user_land_id,a.state,a.land_plant_id,a.user_id,b.*')
            -> join('xg_land_plant b','a.land_plant_id = b.id','left')
            -> where(['a.land_id' => $land_id,'a.user_id' => $this -> user_id])
            -> find();
        if($landPlantInfo['user_id'] != $this -> user_id || !$landPlantInfo['state'] || !$landPlantInfo['land_plant_id'] || !$landPlantInfo['id']){
            $return['code'] = '10002';
            $return['msg'] = '当前植物已经收割完了';
            return json($return);
        }
        //判断植物是否成熟
        if($landPlantInfo['count'] <= $landPlantInfo['number']){
            //这个植物已经到期了，植物消失，用户可以在这块土地上种植
            $updateUserLand['state'] = 0;
            $updateUserLand['land_plant_id'] = 0;
            $res = db('user_land') -> where(['id' => $landPlantInfo['user_land_id']]) -> update($updateUserLand);
            if($res){
                $return['code'] = '10004';
                $return['msg'] = '当前植物已经过期';
                return json($return);
            }
        }else{
            //当前时间小于植物成熟时间，时间还不够
            if(time() < $landPlantInfo['end_time']){
                $return['code'] = '10005';
                $return['msg'] = '请勿操作太快';
                return json($return);
            }else{
                //植物成熟，用户可以采摘
                $model = db();
                $model -> startTrans();
                try{
                    //先判断是否领完这次这个植物就过期了然后修改land_plant表的number,并计算start_time和end_time,领的金币保存到user表中，并记录到采摘表pick_log表中中，同时保存到账户流水表中finance_log表中，最后判断是否采摘次数达标
                    if($landPlantInfo['number'] + 1 >= $landPlantInfo['count']){
                        $updateUserLand['state'] = 0;
                        $updateUserLand['land_plant_id'] = 0;
                        db('user_land') -> where(['id' => $landPlantInfo['user_land_id']]) -> update($updateUserLand);
                    }
                    $updateLandPlant['number'] = $landPlantInfo['number'] + 1;
                    $updateLandPlant['start_time'] = time();
                    $updateLandPlant['end_time'] = time() + $landPlantInfo['cycle'];
                    $res = db('land_plant') -> where(['id' => $landPlantInfo['land_plant_id']]) -> update($updateLandPlant);
                    if($res){
                        setUserCache($this->user_id,$landPlantInfo['income'],2,1);
                        $res = db('user') -> where(['user_id' => $landPlantInfo['user_id']]) -> setInc('gold',$landPlantInfo['income']);
                        if($res){
                            $pickLog['user_id'] = $landPlantInfo['user_id'];
                            $pickLog['gold'] = $landPlantInfo['income'];
                            $pickLog['land_plant_id'] = $landPlantInfo['id'];
                            $pickLog['user_land_id'] = $landPlantInfo['user_land_id'];
                            $pickLog['create_time'] = time();
                            $pickLog['finance_log_id'] = finance_log($landPlantInfo['user_id'],2,21,$landPlantInfo['income'],0,$landPlantInfo['name']);
                            db('pick_log') -> insert($pickLog);
                            $model -> commit();
                            cache('land' . $this -> user_id,null);
                            $return['code'] = '10000';
                            $return['msg'] = '收获成功';
                            return json($return);
                        }else{
                            $model -> rollback();return;
                        }
                    }
                }catch(Exception $e){
                    $model -> rollback();
                    file_put_contents('errorLog/home_index_gainPlant.php',$e,FILE_APPEND);
                    cache('user'.$this->user_id,null);
                    $return['code'] = '10006';
                    $return['msg'] = '网络错误,请稍后重试';
                    return json($return);
                }
            }



        }

    }

    /**
     * 获取自己的加速器
     */
    public function getUserAccelerator(){
        $number = db('user') -> field('accelerator_number') -> where(['user_id' => $this -> user_id]) -> find();
        $return['code'] = '10000';
        $return['msg_test'] = $number['accelerator_number'];
        return json($return);
    }

    /**
     * 点击加速器给植物施肥
     * accelerator_number
     */
    public function setPlantByAccelerator(){
        $is_true = cache($this->user_id);
        if($is_true){
            $return['code'] = '10999';
            $return['msg'] = '请稍等';
            return json($return);
        }else{
            cache($this->user_id,1,3600);
        }
        $accelerator_type = input('post.type',0);
        if($accelerator_type == 0){
            $accelerator_number = 4;
        }else{
            $return['code'] = '10001';
            $return['msg_test'] = '参数错误';
            return json($return);
        }

         //获取当前用户的所有植物
        $user_number = cache('user'.$this->user_id);
        if(!$user_number){
            $user_number = db('user') -> where(['user_id' => $this -> user_id]) -> value('accelerator_number');
        }else{
            $user_number = $user_number['acc_number'];
        }
        if($user_number < $accelerator_number){
            $return['code'] = '10002';
            $return['msg'] = '当前能量豆不足';
            return json($return);
        }

        //判断当天用户是否加速十次，够了的话要提示
        $jiasu_number = db('accelerator_log') -> where(['user_id' => $this -> user_id,'type' => 1])
            -> whereTime('create_time','today')
            -> count();
        if($jiasu_number >= 10){
            $return['code'] = '10002';
            $return['msg'] = '每天最多加速10次';
            return json($return);
        }

       
        //获取加速时效
        $accelerator_time = cache('config');
        if(!$accelerator_time){
            $accelerator_time = db('config')  -> find();
            cache('config',$accelerator_time);
        }

        $accelerator_time = $accelerator_time['accelerator_time'];
        if(!$accelerator_time){
            $accelerator_time = 3600;
        }
        $time = $accelerator_number * $accelerator_time;
        setUserCache($this -> user_id,$accelerator_number,1,2);
        $res = db('user') -> where(['user_id' => $this -> user_id]) -> setDec('accelerator_number',$accelerator_number);
        if($res){
            //给当前用户的所有植物加速
            $res = db('user_land')
                -> alias('a')
                -> field('a.land_plant_id,group_concat(b.id) as id')
                -> join('xg_land_plant b','a.land_plant_id = b.id','right')
                -> where(['a.user_id' => $this -> user_id,'a.state' => 1])
                -> find();
            if(!$res){
                $return['code'] = '10002';
                $return['msg'] = '请先种植';
                return json($return);
            }
            //加速记录
            $accelerator_log = array(
                'user_id' => $this -> user_id,
                'number' => $accelerator_number,
                'land_plant_id' => $res['land_plant_id'],
                'create_time' => time(),
                'type' => 1,
            );
            $res = db('land_plant') -> where("id in ({$res['id']})") -> update([
                'end_time' => ['exp','end_time-' . $time],
                'accelerator_number' => ['exp','accelerator_number+'.$accelerator_number]
            ]);
            db('accelerator_log') -> insert($accelerator_log);
            if($res){
                cache('land' . $this -> user_id,null);
                $return['code'] = '10000';
                $return['msg'] = '加速成功';
                return json($return);
            }else{
                $return['code'] = '10003';
                $return['msg'] = '加速失败';
                return json($return);
            }
        }


    }

}





























