<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/29 0029
 * Time: 09:23
 */
namespace app\admin\controller;
use think\Controller;

class Test extends Controller{
    public function index(){
        return $this -> fetch();
    }
}