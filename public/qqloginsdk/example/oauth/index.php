<?php
//记录原地址
require("/data/wwwroot/cloud/api.php");
$backurl = $_GET['backurl'];
$config = \API_Config::get("my_basic");
$cookieDomain = $config['cookie_domain'];
setcookie("auth_back_url",$backurl,null,"/",$cookieDomain,false,true);
require_once("../../API/qqConnectAPI.php");
$qc = new QC();
$qc->qq_login();
