<?php
namespace app\util\controller;
use think\Controller;
class Sanwei extends Controller{
	/*
	@西瓜科技
	@$user_id——新购买人的用户id
	@$pid——新购买人的父级id
	@
	 */
	function contact_deal($user_id){
		$users = db('user');
		$user_contact = db('user_contact');
		/*判断此用户之前有无站位*/
		$res = $user_contact -> where("children_id = '$user_id' and level = 1") -> find();
		if($res != null){return;}
		$pid = $users -> getFieldByUser_id($user_id,'p_id');
		if(!$pid){return;}
		$pid_level = $users -> getFieldByUser_id($pid,'level');
		/*此层未满，往这一层排列*/
		do{
			$user_num = $user_contact -> where("user_id = '$pid' and level = '$pid_level' ") -> count();
			if($user_num < pow(3,$pid_level)){
				break;
			}else{
				$users -> where("user_id = '$pid' ") -> setInc('level');
				$pid_level++;
			}

		}while($user_num >= pow(3,$pid_level));
		$this -> add_contact($pid,$pid_level,$user_id);
	}
	/*往特定人的某特定层排列下层*/
	function add_contact($pid,$level,$children_id){
		$user_contact = db('user_contact');
		/*找出往上一层谁的下面排列*/
		if($level == 1){
			$user_id = $pid;
			$user_num = $user_contact -> where("user_id = '$user_id' and level = 1 ") -> count();
		}else{
			$u_level = $level - 1;
			$user_list = $user_contact -> where("user_id = '$pid' and level = '$u_level' ") -> order("id asc") -> select();
			foreach($user_list as $val){
				/*查询下层用户是否满员*/
				$v_user_id = $val['children_id'];
				$user_num = $user_contact -> where("user_id = '$v_user_id' and level = 1 ") -> count();
				if($user_num < 3){
					$user_id = $v_user_id;
					break;
				}
			}
			if(!isset($user_id)){
				$level++;
                $users = db('user');
				$users -> where("user_id = '$pid' ") -> setInc('level');
				$this->add_contact($pid,$level,$children_id);
				return;
			}
		}
		
		$first_data = array('user_id'=>$user_id,'children_id'=>$children_id,'level'=>1,'time'=>time());
		$user_contact -> insert($first_data);
		if($user_num == 2){db('user') -> where("user_id = '$user_id' ") -> setInc('level');}
		$now_contact = $user_contact -> where("children_id = '$user_id' and level < 9 ") -> order("level asc") -> select();
		/*插入新关系数据*/
		foreach($now_contact as $val){
			$data  = array('user_id'=>$val['user_id'],'children_id'=>$children_id,'level'=>$val['level']+1,'time'=>time());
			$user_contact -> insert($data);

		}
	}
}