<?php
ini_set("display_errors","on");
error_reporting(E_ALL);
require_once("../../API/qqConnectAPI.php");
$qc = new QC();
$qc->qq_login();
