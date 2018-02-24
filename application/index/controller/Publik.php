<?php 
//公用控制器，比如图形验证码之类的
namespace app\index\controller;

use think\Controller;
use think\Request;

class Publik extends Controller{
    public function getVerifyCode(Request $request){
        $key   =  $request->get('key');
        $nowTime= time();
        
        if (!$key){
            return json(array("status"=>false,"message"=>"nokeyinputed"));
        }

        //从key中机密内容
        $captcheKeyInfo = \app\index\service\Captche::decodeKey($key);
        //如果key已经超过5分钟，返回false
        if (!$captcheKeyInfo || $nowTime - $captcheKeyInfo['timestamp'] > 300){
            return false;
        }
        
        $verifyCodeModule = new \API_Verifycode($captcheKeyInfo['length'],$captcheKeyInfo['type'],$captcheKeyInfo['height'],$captcheKeyInfo['width']);
        $code = $verifyCodeModule->getCode();
        //得到图形验证码验证key
        $verifyCodeKey = $captcheKeyInfo['verifyky'];
        //得到图形验证码验证次数
        $verifyTime    = $captcheKeyInfo['vtime'];  //得到验证次数
        $verifyTime = $verifyTime > 0 ? $verifyTime : "N";  //0的话设置为N，表示不限制次数
        $storeVal = array(
            "remain_verify_time"    =>  $verifyTime,
            "value"                 =>  $code,
        );
        //以验证码key为键写数据
        \API_Redis::set('Default',$verifyCodeKey,$storeVal,300);
        $verifyCodeModule->outImage();
    }
}