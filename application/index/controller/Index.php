<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Session;

class Index extends Controller
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }
    
    public function test($user,$blog_id){
        
        $res = Db::query('select * from lsf_user where phone=?',[15901173164]);
        var_dump($res);die;
        echo $user.'-'.$blog_id;
        var_dump($this->request->param());die;
    }
    
    public function modeltest(){
        $user = Loader::model('User');
        $user->getUser();
        die;
        $res = $user::get(1);
        $res->username = "ruanwenwu";
        $res->save();
        halt($res);
    }
    
    public function dd(){
       /*$redis = new \Redis();
    $redis->connect('localhost', 6379);
    $redis->auth('ruan[wenwu_2012');
    $redis->set('key', 'hello world');
    echo $redis->get('key');*/
        Session::set('named','thinkphp');
    die;
        $handler =new Redis();
        session_set_save_handler($handler, true);
        $_SESSION['a'] = 4;
        var_dump($_SESSION);die;
        //\API_Test::dd();
        $link = \API_Db_Lsf::instance();
        $res = $link->getRow("select * from lsf_user");
        var_dump($res);
    }


    
    
}
