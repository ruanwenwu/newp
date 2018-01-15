<?php 
namespace app\index\model;
use think\Model;
use think\Db;

class User extends Model
{
     public function getUser(){
         $res = Db::query('select * from lsf_user where id=?',[1]);
         var_dump($res);die;
     }
}
