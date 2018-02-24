<?php 
namespace app\index\model;
use think\Model;
use think\Db;

class Auth extends Model
{
    //oauth表添加绑定记录
    public function addAuthLog($param){
        $option = array(
            "uid"     =>  "",
            "oauth_id"   =>  "",
            "oauth_name"    =>  "",
            "ctime"    =>  ''
        );
        
        if ($param && is_array($param)) $option = array_merge($option, $param);
        
        extract($option);
         
        if (!$ctime) $ctime = date("Y-m-d H:i:s");
        
        if (!$uid || !$oauth_id || !$oauth_name) return false;
        
        $sql = "insert into lsf_oauth (uid,oauth_id,oauth_name,ctime) values (?,?,?,?)";
        $queryParam = array($uid,$oauth_id,$oauth_name,$ctime);
        Db::execute($sql,$queryParam);
        return Db::name('user')->getLastInsID();
    }
}