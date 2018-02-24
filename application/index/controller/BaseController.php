<?php
namespace app\index\controller;

use think\Config;
use think\Controller;
use think\Request;

class BaseController extends Controller{
    //分配公共模板变量调用此方法
    public function assignPublicForView(){
        $staticHost = $this->staticHost =  Config::get("static_host");
        $mainHost   = $this->mainHost   =  Config::get("main_host");
        $this->assign("staticHost",$staticHost);
    }
}