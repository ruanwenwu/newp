<?php 
/**
 * 做各种格式验证，比如手机，邮箱等等
 */
namespace app\index\service;

use think\Model;

class Validate extends model{
    /**
     * 验证手机号
     * @param string $phoneNum 手机号
     */
    public function checkPhone($phoneNum){
        $pattern = "/^1\d{10}$/";
        $res = preg_match($pattern,$phoneNum);
        return $res ? true : false;
    }
    
    /**
     * 验证密码
     * @param string $pasword 密码
     * @param string $minlength 最小密码长度
     */
    public function checkPassword($password,$minlength=7){
        if (strlen($password) < $minlength) return false;
        
        $patternNum = "/\d+/";
        $patternCha = "/[a-zA-Z]/";
        
        if(!preg_match($patternNum, $password) || !preg_match($patternCha, $password)) return false;
        
        return true;
        
    }
   
}