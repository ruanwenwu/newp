<?php 
//获得发送短信相关的数据
namespace app\index\model;

use think\Model;
use think\Db;

class Phonecode extends Model{
    //查询用户接收短信的记录
    public function getPhonecodeLog($param){
        $option = array(
            "type"  =>  "", //业务类型,比如login,或者auth_register
            "phone"    =>"" 
        );
        
        if($param && is_array($option)) $option = array_merge($option,$param);
        
        extract($option);
        
        if (!$type || !$phone) return false;
        
        $sql = "select * from lsf_phonecode_log where phone='{$phone}' and business_type = '{$type}' order by id desc limit 1";
            

        $res = Db::query($sql);
        if ($res) return $res[0];
        
        return false;
    }
    
    //记录短信发送记录
    public function recordPhonecodeLog($param){
        $option = array(
        "type"  =>  "", //业务类型,比如login,或者auth_register
        "userid"=>  "", //用户id
        "code"  =>  "", //短信验证码
        "phone" =>  "", //手机号
        "openid"=>  "", //如果尚未注册成功时
        "oauthname"=>"" //第三方尚未
         );
        
        if($param && is_array($option)) $option = array_merge($option,$param);
        
        extract($option);
        
        if (!$code || !$phone || !$type) return false;
        
        $nowTime = date("Y-m-d H:i:s");
        $sql = "insert into lsf_phonecode_log (uid,code,oauth_id,oauth_name,phone,business_type,ctime) values ('$userid','$code','$openid','$oauthname','$phone','$type','$nowTime')";
        return Db::execute($sql);
    }

    //更新验证码的剩余验证次数
    public function updateRemainTime($param){
        $option = array(
            "id"    =>  "",
            "time"  =>  1, //减去的次数
        );
        
        if($param && is_array($option)) $option = array_merge($option,$param);
        
        extract($option);
        if (!$id || !$time) return false;
        $sql = "update lsf_phonecode_log set remaintime = remaintime - $time where id = '$id'";
        return Db::execute($sql);
    }

}