<?php
ini_set("display_errors",'off');
header("content-type:text/html;charset=utf-8");
require_once("../../API/qqConnectAPI.php");
echo '<pre>';
$qc = new QC();  
$acs = $qc->qq_callback();  
$oid = $qc->get_openid();  
$qc = new QC($acs,$oid);  
$uinfo = $qc->get_user_info();  
var_dump($uinfo);die;
