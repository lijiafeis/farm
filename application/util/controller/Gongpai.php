<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/5 0005
 * Time: 14:25
 */
namespace app\util\controller;
use think\Controller;

/**
 * Class Gongpai
 * @package app\util\controller
 * 作废，使用Sanwei.php
 */
class Gongpai extends Controller{

    public function setData($user_id){
        if(!$user_id){
            return -1;
        }
        $this -> user = db('user') -> find($user_id);
        $this -> user_id = $this -> user['user_id'];
        if(!$this -> user){
            return -2;
        }
        //判断当前用户的是否在公派内
        $info = db('tree') -> field('id') -> where(['user_id' => $this -> user_id]) -> select();
        if($info){
            return -1;
        }
        //判断是否是最顶级的top_userid = 0，如果不是判断最顶级的
        if($this -> user['top_userid'] == 0){
            //最顶级，加入公派
            $array = array(
                'user_id' => $this -> user_id,
                'p_id' => 0,
                'tier' => 0,
                'code' => 1,
                'create_time' => time(),
                'top_userid' => $this -> user_id,
            );
            db('tree') -> insert($array);
            return 1;
        }
        //有顶级，得到这个顶级的所有数据，接下来要判断在那一层，在谁下面
        $info = db('tree')  -> field('tier,max(code) as code,top_userid') -> where(['top_userid' => $this -> user['top_userid']]) -> order('tier desc') -> group('tier') -> find();
        if(!$info){
            return -1;
        }
        //判断这一层是否排满
        $is_man = pow(3,$info['tier']);
        if($is_man <= $info['code']){
            //这个人要排到下一层的第一个位置
            $lastTier['user_id'] = $this -> user_id;
            $lastTier['tier'] = $info['tier'] + 1;
            $lastTier['code'] = 1;
            $lastTier['top_userid'] = $info['top_userid'];
            $lastTier['create_time'] = time();
            //得到上级
            $p_id = db('tree') -> field('id,user_id') -> where(['top_userid' => $this -> user['top_userid'],'tier' => $info['tier']]) -> order('code asc') -> find();
            $lastTier['p_id'] = $p_id['user_id'];
            db('tree') -> insert($lastTier);
            return 1;
        }else{
            //这一排没有排满，排到code最大的后一个，并判断上级
            $data = db('tree') -> field('code,p_id') -> where(['top_userid' => $this -> user['top_userid'],'tier' => $info['tier']]) -> order('code desc') -> find();
            //判断code能否被3整除，如果能整除，上级id要换，不能上级id不用换
            //这个人要排到下一层的第一个位置
            $lastTier['user_id'] = $this -> user_id;
            $lastTier['tier'] = $info['tier'];
            $lastTier['code'] = $data['code']+1;
            $lastTier['top_userid'] = $info['top_userid'];
            $lastTier['create_time'] = time();
            if($data['code'] % 3 == 0){
                //要换上级
                $pInfo = db('tree') -> field('code') -> where(['user_id' => $data['p_id']]) -> find();
                $p_id = db('tree') -> field('id,user_id') -> where(['top_userid' => $this -> user['top_userid'],'tier' => $info['tier'] -1,'code' => $pInfo['code'] + 1]) -> find();
                $lastTier['p_id'] = $p_id['user_id'];
            }else{
                //不用换上级
                $lastTier['p_id'] = $data['p_id'];
            }
            db('tree') -> insert($lastTier);
            return 1;

        }
    }

}