<?php 
namespace app\index\controller\ajax;

use think\Request;
use think\Controller;
use think\Validate;
use think\Cookie;

class User extends Controller{
    //使用账户名和密码登陆
    public function doUsernameLogin(Request $request){      
        $phone     = $request->post("phone");
        $password  = $request->post("password");

        $loginLogic      = \think\Loader::model("Login","logic");   //加载逻辑层
        $userModel = \think\Loader::model("User","model");
        
        $userInputStatus = $loginLogic -> checkUserInput($phone,$password); //验证手机和密码格式
        
        if (!$userInputStatus['status']){
            return $userInputStatus;
        }

        //数据库验证
        $loginStatus = $loginLogic -> checkUserRightful($phone,$password);
        
        if ($loginStatus['status']){//登陆成功
            $userBasicInfo = $loginStatus['userInfo'];
            $ip2longAddr = ip2long($_SERVER['REMOTE_ADDR']);
            //写登陆日志
            $lastloginid = $userModel -> addUserLoginLog(array(
                "userid"    =>  $userBasicInfo['id'],
                "ipaddr"    =>  $ip2longAddr,
            ));
            
            //写cookie,使用3des加密  ipaddr+uid+timestamp+login_id
            $loginLogic->writeCookie(array(
                "ipaddr"    =>  $ip2longAddr,     //长整型ip地址
                "uid"       =>  $userBasicInfo['id'],     //用户主键id
                "ctime"     =>  time(), //创建时间
                "lastloginid"=> $lastloginid,     //最后登陆id
            ));
            
            //返回登陆成功信息
            return array("message"=>"登陆成功","status"=>true);
        } else {
            //返回错误信息
            return array("message"=>"用户名或者密码错误","status"=>false,"errorPoint"=>"public");
        }
        
    }
    
    /**
     * 判断手机号是否已经注册
     */
    public function checkIfPhoneReg(Request $request){
        $phone = $request->post("phone");
        $validateModel = \think\Loader::model("Validate","service");
        if (!$validateModel->checkPhone($phone)) return array("message"=>"手机格式不正确","status"=>false);
        
        $userModel = \think\Loader::model("User","model");
        $userInfo = $userModel -> getUser(array("phone"=>$phone));
        if ($userInfo){
            return array("status"=>true);
        }else{
            return array("status"=>false);
        }
    }

    /**
     * oauth_register发送短信验证码
     */
public function sendMsgForAuthRegister(Request $request){
        $phone = $request->post("phone");
        $picCode=$request->post("picCode"); //图形验证码,手机号
        $picCheckCode = $request->post("picCheckCode"); //图形码验证键值

        if (!$phone){
            return array("status"=>false,"message"=>"请输入手机号");
        }
        if (!$picCode){
            return array("status"=>false,"message"=>"请输入图形验证码");
        }

        if (!$picCheckCode){
            return array("status"=>false,"message"=>"hack detected");
        }

        //检测该号码是不是已经注册过
        $res = $this->checkIfPhoneReg($request);

        if ($res['status']){
            return array("status"=>false,"message"=>"系统检测到该手机号已经注册过，请<a href=\"/auth/bind\">前往绑定</a>");
        }
        //检查图形验证码是否正确
        $captcheService = \app\index\service\Captche::verifyCode($picCheckCode,$picCode);

        if (!$captcheService['status']){
            return array('status'=>false,'message'=>$captcheService['message']);
        }
        
        $userCookieInfo = unserialize(Cookie::get("authinfo"));
        if (!$userCookieInfo){
            return array("status"=>false,"message"=>"系统错误!");
        }

        $randStr = \API_String::randomStr(array("length"=>4,"numberonly"=>true));
        $globaService = \think\Loader::model("Globa","service");
        $sendStatus = $globaService -> sendMsg(array(
                "type"  =>  "auth_register", //业务类型,比如login,或者auth_register
                "openid"=>  $userCookieInfo['openid'], //如果尚未注册成功时
                "oauthname"=>$userCookieInfo['oauth_type'], //第三方尚未注
                "phone"    =>$phone,//手机号
                "tpltype"  =>"register",//模板类型
                "codeval"  =>"#code#={$randStr}",//模板替换内容
        ));

        if ($sendStatus['status']){
            $phonecodeModel = \think\Loader::model("Phonecode","model");
            $phonecodeModel -> recordPhonecodeLog(array(
                "type"  =>  "auth_register", //业务类型,比如login,或者auth_register
                "code"  =>  $randStr, //短信验证码
                "phone" =>  $phone, //手机号
                "openid"=>  $userCookieInfo['openid'], //如果尚未注册成功时
                "oauthname"=>$userCookieInfo['oauth_type'] //第三方尚未
            ));
            $sendStatus['waittime'] = 60;
        }
        
        return $sendStatus;
    }
    
    /**
     * auth_register注册请求
     */
    public function doauthRegister(Request $request){
        $phone = trim($request->post("phone"));
        $picCode=trim($request->post("picCode")); //图形验证码,手机号
        $picCheckCode = trim($request->post("picCheckCode")); //图形码验证键值
        $phoneCode    = trim($request->post("phoneCode"));    //短信验证码
        $password     = trim($request->post("password")); //密码
        $userModel = \think\Loader::model("User","model");
        $oauthModel= \think\Loader::model("Auth","model");
        $phoneCodeModel = \think\Loader::model("Phonecode","model");
        $nowTime = time();
        
        if (!$phone){
            return array("status"=>false,"message"=>"请输入手机号");
        }
        if (!$picCode){
            return array("status"=>false,"message"=>"请输入图形验证码");
        }
        if (!$phoneCode){
            return array("status"=>false,"message"=>"请输入短信验证码");
        }
        if (!$password){
            return array("status"=>false,"message"=>"请输入密码");
        }
        if (!$picCheckCode){
            return array("status"=>false,"message"=>"hack detected");
        }

        $userCookieInfo = unserialize(Cookie::get("authinfo"));
        if (!$userCookieInfo){
            return array("status"=>false,"message"=>"系统错误!");
        }
        
        //检测该号码是不是已经注册过
        $res = $this->checkIfPhoneReg($request);
        
        if ($res['status']){
            return array("status"=>false,"message"=>"系统检测到该手机号已经注册过，请<a href=\"/auth/bind\">前往绑定</a>");
        }
        //检查图形验证码是否正确
        $captcheService = \app\index\service\Captche::verifyCode($picCheckCode,$picCode);
        
        if (false && !$captcheService['status']){
            return array('status'=>false,'message'=>$captcheService['message']);
        }
        
        //检查短信验证码是否有效
        $phoneCodeInfo = $phoneCodeModel->getPhonecodeLog(array(
            "type"  =>  "auth_register", //业务类型,比如login,或者auth_register
            "phone" =>  $phone,
        ));
        $phoneCodeTimeDiff = $nowTime - strtotime($phoneCodeInfo['ctime']) ;

        if (!$phoneCodeInfo['remaintime'] || $phoneCodeTimeDiff > 60000){
            return array("status"=>false,"message"=>"验证码已失效");
        }
        
        if($phoneCodeInfo['code'] != $phoneCode){
            return array("status"=>false,"message"=>"验证码输入错误");
        }

        //更新验证码的剩余次数
        $phoneCodeModel->updateRemainTime(array(
            "id"    =>  $phoneCodeInfo['id'],
        ));
        
        //获得随机字符串用作密码加盐
        $salt = \API_String::randomStr(array(
            "length"    =>  4,
        ));
        //获得随机用户名
        $username = \API_String::randomStr(array(
            "length"    =>  6,
        ));
        //写用户信息，手机号，随机盐，保存密码规则，随机用户名 （这个可以写一个model）
        $secreteService = \think\Loader::model("Secrete","service");
        $generatedPassword = $secreteService -> getRealPassword($phone,$password,$salt);
        $userid = $userModel->addUser(array(
            "salt"     =>  $salt,
            "passwd"   =>  $generatedPassword,
            "phone"    =>  $phone,
            "username" =>  $username,
        ));
        //将得到的用户id在auth表中和openid进行绑定
        if (!$userid){
            return array("status"=>false,"message"=>"系统错误!");
        }

        $oauthId = $oauthModel -> addAuthLog(array(
            "uid"     =>  $userid,
            "oauth_id"   =>  $userCookieInfo['openid'],
            "oauth_name"    =>  $userCookieInfo['oauth_type'],
        ));
        
        if (!$oauthId){
            return array("status"=>false,"message"=>"系统错误!");
        }else{
            //登陆成功，写入登陆cookie
            $ip2longAddr = ip2long($_SERVER['REMOTE_ADDR']);
            //写登陆日志
            $lastloginid = $userModel -> addUserLoginLog(array(
                "userid"    =>  $userid,
                "ipaddr"    =>  $ip2longAddr,
            ));
            
            $loginLogic      = \think\Loader::model("Login","logic");   //加载逻辑层
            //写cookie,使用3des加密  ipaddr+uid+timestamp+login_id
            $loginLogic->writeCookie(array(
                "ipaddr"    =>  $ip2longAddr,     //长整型ip地址
                "uid"       =>  $userid,     //用户主键id
                "ctime"     =>  time(), //创建时间
                "lastloginid"=> $lastloginid,     //最后登陆id
            ));
            return array("status"=>true);
        }
    }

    /**
     * 普通注册发送验证码
     */
    public function sendMsgForOrdinaryRegister(Request $request){
        $phone = $request->post("phone");
        $picCode=$request->post("picCode"); //图形验证码,手机号
        $picCheckCode = $request->post("picCheckCode"); //图形码验证键值
    
        if (!$phone){
            return array("status"=>false,"message"=>"请输入手机号");
        }
        if (!$picCode){
            return array("status"=>false,"message"=>"请输入图形验证码");
        }
    
        if (!$picCheckCode){
            return array("status"=>false,"message"=>"hack detected");
        }
    
        //检测该号码是不是已经注册过
        $res = $this->checkIfPhoneReg($request);
    
        if ($res['status']){
            return array("status"=>false,"message"=>"系统检测到该手机号已经注册过，请<a href=\"/login\">登录</a>");
        }
        //检查图形验证码是否正确
        $captcheService = \app\index\service\Captche::verifyCode($picCheckCode,$picCode);
    
        if (!$captcheService['status']){
            return array('status'=>false,'message'=>$captcheService['message']);
        }
    
    
        $randStr = \API_String::randomStr(array("length"=>4,"numberonly"=>true));
        $globaService = \think\Loader::model("Globa","service");
        $sendStatus = $globaService -> sendMsg(array(
            "type"  =>  "register", //业务类型,比如login,或者auth_register
            "phone"    =>$phone,//手机号
            "tpltype"  =>"register",//模板类型
            "codeval"  =>"#code#={$randStr}",//模板替换内容
        ));

        if ($sendStatus['status']){
            $phonecodeModel = \think\Loader::model("Phonecode","model");
            $phonecodeModel -> recordPhonecodeLog(array(
                "type"  =>  "register", //业务类型,比如login,或者auth_register
                "code"  =>  $randStr, //短信验证码
                "phone" =>  $phone, //手机号
            ));
            $sendStatus['waittime'] = 60;
        }
    
        return $sendStatus;
    }
    
    /**
     * 普通register注册请求
     */
    public function doOrdinaryRegister(Request $request){
        $phone = trim($request->post("phone"));
        $picCode=trim($request->post("picCode")); //图形验证码,手机号
        $picCheckCode = trim($request->post("picCheckCode")); //图形码验证键值
        $phoneCode    = trim($request->post("phoneCode"));    //短信验证码
        $password     = trim($request->post("password")); //密码
        $userModel = \think\Loader::model("User","model");
        $oauthModel= \think\Loader::model("Auth","model");
        $phoneCodeModel = \think\Loader::model("Phonecode","model");
        $nowTime = time();
    
        if (!$phone){
            return array("status"=>false,"message"=>"请输入手机号");
        }
        if (!$picCode){
            return array("status"=>false,"message"=>"请输入图形验证码");
        }
        if (!$phoneCode){
            return array("status"=>false,"message"=>"请输入短信验证码");
        }
        if (!$password){
            return array("status"=>false,"message"=>"请输入密码");
        }
        if (!$picCheckCode){
            return array("status"=>false,"message"=>"hack detected");
        }
    
        //检测该号码是不是已经注册过
        $res = $this->checkIfPhoneReg($request);
    
        if ($res['status']){
            return array("status"=>false,"message"=>"系统检测到该手机号已经注册过，请<a href=\"/login\">登录</a>");
        }
        //检查图形验证码是否正确
        $captcheService = \app\index\service\Captche::verifyCode($picCheckCode,$picCode);
    
        if (false && !$captcheService['status']){
            return array('status'=>false,'message'=>$captcheService['message']);
        }
    
        //检查短信验证码是否有效
        $phoneCodeInfo = $phoneCodeModel->getPhonecodeLog(array(
            "type"  =>  "register", //业务类型,比如login,或者auth_register
            "phone" =>  $phone,
        ));

        if (!$phoneCodeInfo){
            return array("status"=>false,"message"=>"hack detected");
        }

        if($phoneCodeInfo['code'] != $phoneCode){
            return array("status"=>false,"message"=>"验证码输入错误");
        }

        $phoneCodeTimeDiff = $nowTime - strtotime($phoneCodeInfo['ctime']) ;
    
        if (!$phoneCodeInfo['remaintime'] || $phoneCodeTimeDiff > 60000){
            return array("status"=>false,"message"=>"验证码已失效");
        }
    
        //更新验证码的剩余次数
        $phoneCodeModel->updateRemainTime(array(
            "id"    =>  $phoneCodeInfo['id'],
        ));
    
        //获得随机字符串用作密码加盐
        $salt = \API_String::randomStr(array(
            "length"    =>  4,
        ));
        //获得随机用户名
        $username = \API_String::randomStr(array(
            "length"    =>  6,
        ));
        //写用户信息，手机号，随机盐，保存密码规则，随机用户名 （这个可以写一个model）
        $secreteService = \think\Loader::model("Secrete","service");
        $generatedPassword = $secreteService -> getRealPassword($phone,$password,$salt);
        $userid = $userModel->addUser(array(
            "salt"     =>  $salt,
            "passwd"   =>  $generatedPassword,
            "phone"    =>  $phone,
            "username" =>  $username,
        ));
        //将得到的用户id在auth表中和openid进行绑定
        if (!$userid){
            return array("status"=>false,"message"=>"系统错误!");
        }else{
            //登陆成功，写入登陆cookie
            $ip2longAddr = ip2long($_SERVER['REMOTE_ADDR']);
            //写登陆日志
            $lastloginid = $userModel -> addUserLoginLog(array(
                "userid"    =>  $userid,
                "ipaddr"    =>  $ip2longAddr,
            ));
    
            $loginLogic      = \think\Loader::model("Login","logic");   //加载逻辑层
            //写cookie,使用3des加密  ipaddr+uid+timestamp+login_id
            $loginLogic->writeCookie(array(
                "ipaddr"    =>  $ip2longAddr,     //长整型ip地址
                "uid"       =>  $userid,     //用户主键id
                "ctime"     =>  time(), //创建时间
                "lastloginid"=> $lastloginid,     //最后登陆id
            ));
            return array("status"=>true);
        }
    }
    
}