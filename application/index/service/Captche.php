<?php 
namespace app\index\service;

use think\Model;
use think\Config;

class Captche extends Model{
    private static $sescretkey;
    
    /**
     * 生成验证码验证下标和验证码请求参数
     * @param unknown $param
     */
    public static function generateKey($param=array()){
        $option = array(
            "width" =>  "100",
            "height"=>  "50",
            "type"  =>  0,
            "length"=>  4,
            "vtime"  => 1,  //验证次数1是1次，2是2次，0是不限次数
        );
        
        if ($param && \is_array($param)) $option = array_merge($option, $param);
        
        if (!\is_array($option)) return false;
        
        $option['timestamp'] = time();
        $verifyKy = "imgvcode:".uniqid();//生成验证下标
        $option['verifyky'] = $verifyKy;
        $orikey = serialize($option);
        $secreteKey = self::getSecreteKey();
        $key = \API_Security_Algos::des3Encrypt(array(
            'value'       => $orikey, #加密的字符
            'cryptkey'    => $secreteKey #加密用的key
        ));
        return array(
            "verifykey" =>  $verifyKy,
            "key"       =>  $key,
        );
    }
    
    public static function decodeKey($key){
        if (!$key) return false;
        
        $secreteKey = self::getSecreteKey();
        $keyInfo = \API_Security_Algos::des3Dencrypt(array(
            'value'       => $key, #加密的字符
            'cryptkey'    => $secreteKey #加密用的key
        ));
        return unserialize($keyInfo);
    }
    
    public static function getSecreteKey(){
        if (self::$sescretkey){
            return self::$sescretkey;
        }
        return Config::get("secretekey")['verifycode'];
    }
    
    //验证图形验证码
    public static function verifyCode($key,$value){
        if (!$key || !$value) return false;
        
        $keyVal = \API_Redis::get('Default',$key);

        if (!$keyVal){
            return array("status"=>false,"message"=>"图形验证码已过期");
        }
        
        if ($keyVal['value'] != $value){
            return array("status"=>false,"message"=>"图形验证码错误");
        }
        
        if ($keyVal['remain_verify_time'] > 0){
            $keyVal['remain_verify_time'] = $keyVal['remain_verify_time'] - 1;
            if ($keyVal['remain_verify_time'] <= 0){
                //如果没有验证机会了,删掉键值节省空间
                \API_Redis::delete('Default',$key);
            }else{
                $remainTime = \API_Redis::ttl('Default',$key);
                if ($remainTime > 0){
                    \API_Redis::set('Default',$key,$keyVal,$remainTime);
                }
            }
        }
        
        return array("status"=>true);
    }

}