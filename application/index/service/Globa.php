<?php 
//全局公用方法
namespace app\index\service;

use think\Model;
use think\Config;

class Globa extends Model{
    /**
     * 发送短信验证码接口
     */
    public function sendMsg($param){
        $option = array(
            "type"  =>  "", //业务类型,比如login,或者auth_register
            "userid"=>  "", //用户id
            "openid"=>  "", //如果尚未注册成功时
            "oauthname"=>"", //第三方尚未注册时的类型，比如是qq还是weixin
            "phone"    =>"",//手机号
            "tpltype"  =>"",//模板类型
            "codeval"  =>"",//模板替换内容
        );
        
        if($param && is_array($option)) $option = array_merge($option,$param);

        extract($option);

        if (!$type || !$phone || !$tpltype || !$codeval) return false;
        
        $allowedBusiness = Config::get("msg_type");

        if (!in_array($type,$allowedBusiness)){
            return false;   //如果不是允许发送短信的业务，直接拒绝
        }
        
        $phoneCodeModel = \think\Loader::model("Phonecode","model");
        
        //查看最近发送短信的记录,如果60秒以内发送过，返回剩余时间
        $res = $phoneCodeModel -> getPhonecodeLog(array(
            "type"  =>  $type, //业务类型,比如login,或者auth_register
            "userid"=>  $userid, //用户id
            "openid"=>  $openid, //如果尚未注册成功时
            "oauthname"=>$oauthname //第三
        ));
        
        if ($res){
            //得到发送时间
            $lastTime = strtotime($res['ctime']);
            $nowTime  = time();
            $timeDiff = $nowTime - $lastTime;
            if ($timeDiff < 60){
                $waitTime = 60 - $timeDiff;
                return array("status"=>false,"message"=>"发送太过频繁","waittime"=>$waitTime);
            }
        }

        //直接发送
        $res = \API_Sms::send(array(
            "mobile" =>  $phone,
            "tplType" => $tpltype,
            "codeVal"=>  $codeval
        ));

        if ($res['status']){
            //发送成功写日志
            
            return array("status"=>true);
        }else{
            //失败的话返回
            return array("status"=>false,"message"=>$res['msg']);
        }
    }
}
