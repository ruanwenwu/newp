<?php 
namespace app\index\controller;

use think\Request;
use think\Controller;
use think\Cookie;
use think\Loader;

class Auth extends BaseController{
    //auth授权后，补充手机信息
    public function register(){
            
        $this->assignPublicForView();   //公共模板变量
        $userCookieInfo = unserialize(Cookie::get("authinfo"));
        $authBackUrl    = Cookie::get("auth_back_url");
        if (!$userCookieInfo || !$authBackUrl){
            header("Location:{$this->mainHost}");   //如果没有cookie信息就跳转
            die;
        }
        
        //$res1 = \API_Redis::set('Default','uname','wss');
        //$res2 = \API_Redis::get('Default','uname');
        //var_dump($res1);
        //var_dump($res2);
        //短信接口
        /*$res = \API_Sms::send(array(
            "mobile" =>  15901173164,
            "tplType" =>  "register",
            "codeVal"=>  "#code#=1234",
        ));*/
        $captcheKeyInfo = \app\index\service\Captche::generateKey(array("vtime"=>2));    //获得验证码信息
        $this->assign("requestMsgUrl","/ajax/sendmsg/oauthregister");
        $this->assign("doregisterUrl","/ajax/doauthregister");
        $this->assign("verifycodeunderkey",$captcheKeyInfo['verifykey']);
        $this->assign("verifycodekey",$captcheKeyInfo['key']);
        $this->assign("backUrl",$authBackUrl);
        $this->assign("pagetype","auth");
        return $this->fetch();
    }
    
    public function login(){
        //判断oauth表中是否存在记录，没有或者没有绑定用户id的话，直接跳到oauth_register,如果有的话，跳到aoth_login
        $authInfo = unserialize(Cookie::get("authinfo"));
        $authId   = $authInfo['openid'];
        $authType = $authInfo['oauth_type'];
        $userModel = \think\Loader::model("User","model");
        $oauthRecord = $userModel -> getOauthUser(array(
            "oauthid"   =>  $authId,
            "oauthname" =>  $authType,
        ));
        if (!$oauthRecord || !$oauthRecord['uid']){
            //如果没有记录，或者没有绑定，调到oauth_register页面
            $hostName = "http://".$_SERVER['HTTP_HOST'];
            header("Location:{$hostName}/auth/register");
            die;
        } else {
            //如果有记录,写cookie，然后跳到backurl
            $loginLogic      = \think\Loader::model("Login","logic");   //加载逻辑层
            //写cookie,使用3des加密  ipaddr+uid+timestamp+login_id
            $ip2longAddr = ip2long($_SERVER['REMOTE_ADDR']);
            $userCookieInfo = unserialize(Cookie::get("authinfo"));
            $lastloginid = $userModel -> addUserLoginLog(array(
                "userid"    =>  $oauthRecord['uid'],
                "ipaddr"    =>  $ip2longAddr,
                "source"    =>  $userCookieInfo['oauth_type'],
            ));
            $loginLogic->writeCookie(array(
                "ipaddr"    =>  $ip2longAddr,     //长整型ip地址
                "uid"       =>  $oauthRecord['uid'],     //用户主键id
                "ctime"     =>  time(), //创建时间
                "lastloginid"=> $lastloginid,     //最后登陆id
            ));
            $authBackUrl    = Cookie::get("auth_back_url");
            header("Location:$authBackUrl");   //如果没有cookie信息就跳转
            die;
        }
        
    }
}
?>