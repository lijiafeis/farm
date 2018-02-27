<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 15:50
 * 会员的展示和操作
 */
namespace app\admin\controller;
use think\Controller;
use think\Exception;
use think\Log;
use think\Request;

class Member extends Action {

    public function users(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else{
            $page = input('post.page',1);
            $user_id = input('post.user_id',0);
            $tel = input('post.tel',0);
            $nickname = input('post.nickname',0);
            $number = 10;
            $where = array();
            $where1 = array();
            if($user_id){
                $where['user_id']= $user_id;
                $where1['a.user_id']= $user_id;
            }
            if($tel){
                $where['username']= $tel;
                $where1['a.username']= $tel;
            }
            if($nickname){
                $where['nickname']= $nickname;
                $where1['a.nickname']= $nickname;
            }
            $count=db('user')->where($where) -> count();
            $info = db('user')
                -> alias('a')
                -> field('a.*,b.nickname as p_name')
                -> join('xg_user b','a.p_id = b.user_id','left')
                -> where($where1)
                -> order('a.user_id desc')
                -> page($page,$number)
                -> select();

            $return['number'] = $count;
            $return['data'] = $info;
            return json($return);
        }
    }

    /**
     * 禁用用户
     * type = 0 用户想要禁用，1 用户想要解禁
     */
    public function forbiddenUser(){
        $user_id = input('post.user_id',0);
        $type = input('post.type',-1);
        if(!$user_id || $type == -1){
            $return['code'] = 0;
            return json($return);
        }
        $userInfo = db('user') -> field('user_id,is_forbidden') -> where(['user_id' => $user_id]) -> find();
        if($type == 0){
            //禁用
            $res = db('user') -> where(['user_id' => $userInfo['user_id']]) -> setField('is_forbidden',1);
        }else if($type == 1){
            //解禁
            $res = db('user') -> where(['user_id' => $userInfo['user_id']]) -> setField('is_forbidden',0);
        }
        if($res){
            $return['code'] = 1;
            return json($return);
        }
        $return['code'] = 0;
        return json($return);
    }


    /**
     * 给用户充值
     */
    public function setUserGold(){
        $user_id = input('user_id',0);
        $gold = input('gold',0);
        if(!$user_id || !$gold){
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
        $userInfo = db('user') -> field('user_id') -> where("user_id = :user_id",['user_id' => $user_id]) -> find();
        if(!$userInfo){
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
        $model = db();
        $model -> startTrans();
        try{
            setUserCache($userInfo['user_id'],$gold,2,1);
            $res = db('user') -> where(['user_id' => $userInfo['user_id']]) -> setInc('gold',$gold);
            if($res){
                //记录到用户的流水表中
                finance_log($userInfo['user_id'],2,23,$gold);
                $model -> commit();
                $return['code'] = 1;
                $return['info'] = '充值成功';
                return json($return);
            }
            $return['code'] = 0;
            $return['info'] = '充值失败';
            return json($return);
        }catch (Exception $e){
            Log::write($e,'lijiafei');
            $model -> rollback();
            cache('user'.$userInfo['user_id'],null);
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
    }

    /**
     * 得到用户的下级人员昵称和user_id和电话号
     */
    public function getUserXj(){
        $user_id = input('post.user_id',0);
        if(!$user_id){
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
        
        $res = db('user') -> field('user_id,nickname,username') ->  where(['p_id' => $user_id]) ->  select();

        $return['code'] = 1;
        $return['info'] = $res;
        return json($return);
    }

}