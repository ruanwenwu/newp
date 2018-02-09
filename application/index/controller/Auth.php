<?php 
namespace app\index\controller;

use think\Request;
use think\Controller;

class Auth extends BaseController{
    //auth授权后，补充手机信息
    public function register(){
        $this->assignPublicForView();   //公共模板变量
        
        //$res1 = \API_Redis::set('Default','uname','wss');
        $res2 = \API_Redis::get('Default','uname');
        //var_dump($res1);
        var_dump($res2);
        //短信接口
        /*$res = \API_Sms::send(array(
            "mobile" =>  15901173164,
            "tplType" =>  "register",
            "codeVal"=>  "#code#=1234",
        ));*/
        
        return $this->fetch();
    }
}
?>