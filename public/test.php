<?php
include 'Resis.php';
$handler =new Resis();
ini_set('session.save_handler',"user");
ini_set('session.auto_start', 0);
session_set_save_handler($handler, true);
session_start();
$_SESSION['a'] = 666;
var_dump($_SESSION);die;