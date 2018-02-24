<?php
header("content-type:text/html;charset=utf-8");
require_once("../../API/qqConnectAPI.php");
require("/data/wwwroot/cloud/api.php");
$qc = new QC();  
$acs = $qc->qq_callback();  
$oid = $qc->get_openid();  
$qc = new QC($acs,$oid);  
$uinfo = $qc->get_user_info();  
$uinfo['openid'] = $oid;
$uinfo['oauth_type'] = "qq";
$config = API_Config::get("my_basic");
$cookieDomain = $config['cookie_domain'];
$authLoginUrl = $config['authlogin'];
setcookie("authinfo",serialize($uinfo),null,"/",$cookieDomain,false,true);
header("Location:$authLoginUrl");die;   //保存oauth信息跳转到oauth登陆进行统一处理

