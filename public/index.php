<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
$data = ['update','delete','select','drop','show','script'];
$post = file_get_contents("php://input");
if($post){
    foreach ($data as $value) {
        if(stripos($post,$value) ){
            file_put_contents('errorLog/public_index.php',$post,FILE_APPEND);
            die();
        }
    }
}elseif($_POST){
    $post = '';
    foreach($_POST as $k=>$v){
        $post .= $v;
    }
    foreach ($data as $value) {
        if(stripos($post,$value) ){
            file_put_contents('errorLog/public_index.php',$post,FILE_APPEND);
            die();
        }
    }
}
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
