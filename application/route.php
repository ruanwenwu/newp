<?php
use think\Route;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/*return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];*/

/*Route::get('/',function(){
    return 'Hello,world!';
});*/

Route::rule('/','index/index/index','GET');
Route::rule(':user/:blog_id','index/index/test','GET');
Route::get("/modeltest","index/index/modeltest");
Route::get('/login','index/login/index');
Route::get("/test",'index/index/dd');
Route::get("/captche",'index/publik/getVerifyCode');
Route::get('/auth/register','index/auth/register');//第三方注册
Route::get('/auth/login','index/auth/login');   //第三方登陆入口
Route::get('/auth/bind','index/auth/bind');     //第三方账号和手机号绑定页面
Route::get('/register','index/login/register');
//ajax请求
Route::post("/ajax/login",'index/ajax.user/doUsernameLogin');    //用户登陆接口
Route::post("/ajax/checkPhoneExists",'index/ajax.user/checkIfPhoneReg');    //判断手机号是否注册接口
Route::post("/ajax/sendmsg/oauthregister",'index/ajax.user/sendMsgForAuthRegister');    //oauth注册发送短信验证码接口
Route::post("/ajax/doauthregister",'index/ajax.user/doauthRegister');   //oauthregister请求接口
Route::post("/ajax/sendmsg/register",'index/ajax.user/sendMsgForOrdinaryRegister');    //oauth注册发送短信验证码接口
Route::post("/ajax/doregister",'index/ajax.user/doOrdinaryRegister');   //oauthregister请求接口

