<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 14:16
 */
namespace app\admin\controller;
use think\Controller;

class Action extends Controller{
    function __construct(){
        parent::__construct();
        $adminInfo = session('admin_info');
        if(!$adminInfo){
            $this->error('您还未登录，请登录',url('User/index'),5);
        }else{
            $this -> admin_id = $adminInfo;
        }
    }

    public function __destruct(){
        session('is_true',null);
    }

}