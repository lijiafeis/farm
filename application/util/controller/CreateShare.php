<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/7 0007
 * Time: 11:47
 */
namespace app\util\controller;
use think\Controller;
const SHARE_NAME = 'share17';
class CreateShare extends Controller{

    //创建二维码图片
    public function createShare($user_id,$url){
        if(!$user_id){
            return 0;
        }
//        //判断是否有二维码和分享图片
//        if (!file_exists ( 'share/qr/' . $user_id . '.png' )) {
//            import('phpqrcode.phpqrcode',EXTEND_PATH,'.php');
//            $data = $url;
//            $level = 'L';
//            $size =4;
//            $QRcode = new \QRcode();
//            $qr_path = 'share/qr/' . $user_id . '.png';
//            $QRcode->png($data,$qr_path,$level,$size,2);
//        }
        return $this -> web_qr($user_id);

    }

    private function web_qr($user_id){
        if(!is_dir('share/'.SHARE_NAME.'/')){
            mkdir('share/'.SHARE_NAME.'/');
        }
        if (file_exists ('share/'.SHARE_NAME.'/'.$user_id.'.png')) {
            return '/share/'.SHARE_NAME.'/'.$user_id.'.png';
        }
        // $erweima_img='share/qr/'.$user_id.'.png';
        //相关参数
        $info = cache('qrset');
        if(!$info){
            $info = db('qrset')->find();
            cache('qrset',$info);
        }
        $erweima_height=$erweima_width=$info['qr_size'];
        $dst_path=substr($info['pic_url'],1);
        $str=$user_id;
        $font_size=19;
        $fnt_x=220;
        $fnt_y=482;
        //载入字体zt.ttf
        $fnt = "static/home/css/msyh.ttf";

//        $this->img_suo_png($erw   eima_img,$erweima_width,$erweima_height,$user_id);
//        $src ='share/qr/'.$user_id.'.png';
//        $erweima_img = imagecreatefromstring(file_get_contents($src));
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        //$src = imagecreatefromstring(file_get_contents($src_path));
        //获取水印图片的宽高
        //将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果
        //imagecopymerge($dst, $src1, $info[0]['head_x'], $info[0]['head_y'], 0, 0, $head_width, $head_height, 100);
//        imagecopymerge($dst,   $erweima_img, $info['qr_x'], $info['qr_y'], 0, 0, $erweima_width, $erweima_height, 100);
        //如果水印图片本身带透明色，则使用imagecopy方法
//        imagecopy($dst, $erweima_img, $info['qr_x'], $info['qr_y'], 0, 0, $erweima_width, $erweima_width);
        //创建颜色，用于文字字体的白和阴影的黑
        $white=imagecolorallocate($dst,222,229,207);
        $black=imagecolorallocate($dst,50,50,50);
        imagettftext($dst,$font_size, 0, $fnt_x+1, $fnt_y+1, $white, $fnt, $str);
        imagettftext($dst,$font_size, 0, $fnt_x, $fnt_y, $black, $fnt, $str);
        ImagePng($dst,'share/'.SHARE_NAME.'/'.$user_id.'.png'); // 保存图片,但不显示
        //销毁对象
        ImageDestroy($dst);
        return '/share/'.SHARE_NAME.'/'.$user_id.'.png';
    }

    function img_suo_png($img='head.jpg',$new_width=100,$new_height=100,$user_id){
        list($width, $height) = getimagesize($img);
        $image_p = imagecreate($new_width, $new_height);
        $image = imagecreatefrompng($img);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        ImagePNG($image_p,'share/qr/'.$user_id.'.png');
    }

}
