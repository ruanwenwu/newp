<?php 
/**
 * 用于加密和解密操作
 * @author rww
 * @date 2017/02/11
 */
namespace app\index\service;

use think\Model;

class Secrete extends Model{
    /**
     * 从用户输入的密码得到数据库存储的密码
     * @param string $phone 手机号
     * @param string $password 输入的密码
     * @param string $salt 盐
     */
    public function getRealPassword($phone,$password,$salt){
        return md5(md5(substr($phone,0,6).$password).$salt);
    }
}