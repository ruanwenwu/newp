<?php 
namespace app\index\model;
use think\Model;
use think\Db;

class User extends Model
{
     /**
      * 通过手机号或者用户id得到用户基本信息
      * @param unknown $param
      */
     public function getUser($param){
         $option = array(
             "phone"    =>  "",
             "uid"   =>  "",
         );
         
         if ($param && is_array($param)) $option = array_merge($option, $param);
         
         extract($option);
         
         if (!$uid && !$phone) return false;
         
         if ($uid){
             $sql = "select * from lsf_user where id=?";
             $queryParam = array($uid);
         }else if ($phone){
             $sql = "select * from lsf_user where phone=?";
             $queryParam = array($phone);
         }
         $res = Db::query($sql,$queryParam);  
         
         if (!$res) return false;
         
         return $res[0];
     }
     
     /**
      * 添加用户登陆日志
      * @author rww
      * @date 2018/02/11
      */
     public function addUserLoginLog($param){
         $option = array(
             "userid"   =>  "",
             "ipaddr"   =>  "",
             "source"   =>  "phone",
         );
         
         if ($param && is_array($param)) $option = array_merge($option, $param);
         
         extract($option);
         
         if (!$userid || !$ipaddr) return false;
         
         $nowTime = date("Y-m-d H:i:s");
         $sql = "insert into lsf_login_log (userid,ipaddr,ctime,lastactive,source) values (?,?,?,?,?)";
         $queryParam = array($userid,$ipaddr,$nowTime,$nowTime,$source);
         Db::execute($sql,$queryParam);
         return Db::name('user')->getLastInsID();
     }
     
     /**
      * 查找oauth用户,可以通过oauthid和oathname查询，也可以通过userid查询
      * @author rww
      * @date 2018/02/12
      */
     public function getOauthUser($param){
        $option = array(
             "userid"   =>  "",
             "oauthid"   =>  "",
             "oauthname"=>  "",
         );
          
         if ($param && is_array($param)) $option = array_merge($option, $param);
          
         extract($option);
          
         if (!$userid && !($oauthid && $oauthname)) return false;
         
         if ($userid){
             $sql = "select * from lsf_oauth where uid = '$userid'";
         } else if ($oauthid && $oauthname){
             $sql = "select * from lsf_oauth where oauth_id = '$oauthid' and oauth_name='$oauthname'";
         }

         $res = Db::query($sql);
         
         if ($res){
             return $res[0];
         } 
         
         return false;
     }

     /**
      * 添加用户
      */
     public function addUser($param){
         $option = array(
             "salt"     =>  "",
             "passwd"   =>  "",
             "phone"    =>  "",
             "username" =>  '',
             "ctime"    =>  ''
         );

         if ($param && is_array($param)) $option = array_merge($option, $param);
          
         extract($option);
         
         if (!$ctime) $ctime = date("Y-m-d H:i:s");
          
         if (!$salt || !$passwd || !$phone || !$username) return false;
         
         $sql = "insert into lsf_user (salt,passwd,phone,username,ctime) values (?,?,?,?,?)";
         $queryParam = array($salt,$passwd,$phone,$username,$ctime);
         Db::execute($sql,$queryParam);
         return Db::name('user')->getLastInsID();
     }
}
