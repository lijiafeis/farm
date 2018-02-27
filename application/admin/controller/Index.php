<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 13:40
 * 主页面
 */
namespace app\admin\controller;

class Index extends Action{

    public function index(){
        return $this -> fetch();
    }

    public function top(){
        return $this -> fetch();
    }

    /**
     * 用户点击上面的退出按钮
     */
    public function out(){
        session(null);
        $this -> redirect(url('User/index'));
    }

    public function left(){
        return $this -> fetch();
    }
}