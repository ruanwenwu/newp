<?php
/**
 * 用户登陆逻辑层
 */
namespace app\index\logic;

use think\Model;
use think\Config;
use think\Cookie;

class Login extends Model{
    //检查用户名和密码格式是否合乎要求
    public function checkUserInput($phone,$password){
        $validateService = \think\Loader::model("Validate","service");
        if(!$validateService -> checkPhone($phone)){
            return array("message"=>"手机号格式不正确","errorPoint"=>"phone","status"=>false);
        }
        
        if(!$validateService -> checkPassword($password)){
            return array("message"=>"密码错误","errorPoint"=>"password","status"=>false);
        }
        
        return array("status"=>true);
    }
    
    //检查用户名和密码是否匹配,注意这个接口返回的message只是给程序员看的，返回给用户比较隐晦的信息
    public function checkUserRightful($phone,$password){
        //首先看用户是否存在
        $userModel = \think\Loader::model("User","model");
        $userBasicInfo  = $userModel -> getUser(array("phone"=>$phone));
        if (!$userBasicInfo) {
            return array("message"=>"用户不存在","status"=>false);
        }
        
        //存在的话，拿到盐和密码相比较
        $savedPassword = $userBasicInfo['passwd'];
        $salt          = $userBasicInfo['salt'];
        //生成的密码
        $secreteService = \think\Loader::model("Secrete","service");
        $generatedPassword = $secreteService -> getRealPassword($phone,$password,$salt);
        
        if ($savedPassword == $generatedPassword){
            return array("status"=>true,"userInfo"=>$userBasicInfo);
        }else{
            return array("status"=>false,"message"=>"密码错误");
        }
    }
    
    /**
     * 写cookie
     * @author rww
     * @date 2018/02/12
     */
    public function writeCookie($param){
        $option = array(
            "ipaddr"    =>  "",     //长整型ip地址
            "uid"       =>  "",     //用户主键id
            "ctime"     =>  time(), //创建时间
            "lastloginid"=> "",     //最新登陆记录id
        );

        if ($param && is_array($param)) $option = array_merge($option, $param);
        
        extract($option);
        
        if (!$ipaddr || !$uid || !$ctime || !$lastloginid) return false;

        $cookieKey = \API_Security_Algos::des3Encrypt(array(
            "value"     =>  "{$ipaddr}:{$uid}:{$ctime}:{$lastloginid}",
            "cryptkey"  =>  Config::get("secretekey")['login'],  
        ));
        $config = \API_Config::get("my_basic");
        $cookieDomain = $config['cookie_domain'];
        setcookie("ipck",$cookieKey,null,"/",$cookieDomain,false,true); 
    }
    

}