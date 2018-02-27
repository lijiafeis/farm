<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/29 0029
 * Time: 10:44
 */
namespace app\admin\controller;
use think\Request;

class Plant extends Action{

    /**
     * 显示植物列表
     */
    public function plantList(){
        if(Request::instance() -> isGet()){
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $page = input('post.page',1);
            $number = 10;
            $count=db('plant')-> count();
            $data = db('plant')
                -> alias('a')
                -> field('a.id,a.name,a.plant_code,b.name as cate_name,b.price,b.cycle,b.income,b.count')
                -> join('xg_category b','a.cate_id = b.id','left')
                -> page($page,$number)
                -> order('id desc')
                -> select();
            $return['number'] = $count;
            $return['data'] = $data;
            return json($return);

        }
    }

    /**
     * 删除植物
     */
    public function delPlant(){
        $id = input('post.id',0);
        if(!$id){
            $return['code'] = 0;
            $return['info'] = '网络错误';
            return json($return);
        }
        $plantInfo = db('plant') -> field('id') -> where(['id' => $id]) -> find();
        if(!$plantInfo){
            $return['code'] = 0;
            $return['info'] = '删除植物不存在';
            return json($return);
        }
        $res = db('plant') -> delete($id);
        if($res){
            cache('allplant',null);
            $return['code'] = 1;
            $return['info'] = '删除成功';
            return json($return);
        }
        $return['code'] = 0;
        $return['info'] = '删除失败';
        return json($return);
    }


    /**
     * 添加植物
     */
    public function plantAdd(){
        if(Request::instance() -> isGet()){
            $cateInfo = db('category') -> field('id,name') -> select();
            $this -> assign('cateInfo',$cateInfo);
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $data['name'] = input('post.name','');
            $data['cate_id'] = input('post.cate_id',0);
            $data['plant_code'] = input('post.plant_code','');
            if(!$data['name'] || !$data['cate_id'] || !$data['plant_code']){
                $this -> error('参数缺失','plantAdd');
                exit;
            }
            $res = db('plant') -> insert($data);
            if($res){
                cache('allplant',null);
                $this -> success('添加成功','plantList');
            }else{
                $this -> error('添加失败','plantAdd');
            }

        }
    }

    /**
     * 修改植物
     */
    public function updatePlant(){
        if(Request::instance() -> isGet()){
            $id = input('get.id',0)*1;
            if(!$id){
                $this -> error('参数缺失','plantList');exit;
            }
            $plantInfo = db('plant') -> find($id);
            $cateInfo = db('category') -> field('id,name') -> select();
            foreach ($cateInfo as $k => $v){
                if($v['id'] == $plantInfo['cate_id']){
                    $cateInfo[$k]['select'] = 'selected';
                }else{
                    $cateInfo[$k]['select'] = '';
                }
            }
            $this -> assign('cateInfo',$cateInfo);
            $this -> assign('plantInfo',$plantInfo);
            return $this -> fetch();
        }else if(Request::instance() -> isPost()){
            $data['name'] = input('post.name','');
            $data['cate_id'] = input('post.cate_id',0);
            $data['plant_code'] = input('post.plant_code','');
            $id = input('post.id','');
            if(!$data['name'] || !$data['cate_id'] || !$data['plant_code'] || !$id){
                $this -> error('参数缺失','plantAdd');
                exit;
            }
            $res = db('plant') -> where(['id' => $id]) -> update($data);
            if($res){
                cache('allplant',null);
                $this -> success('修改成功','plantList');
            }else{
                $this -> error('修改失败','updatePlant');
            }

        }
    }
}