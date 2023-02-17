<?php
include_once(__DIR__.'/../_data.php');
$mysqli = new mysqli(dbHost, dbUser, dbPass, dbName);
$password_hash = password_hash($adminPass, PASSWORD_BCRYPT);
$mysqli -> query("INSERT INTO `users` (`id`,`id_chat`, `name`,`status`,`t_login`, `t_password`) VALUES (1,'', 'admin', 100, 'admin', '{$password_hash}');");
$mysqli -> query("UPDATE `settings` SET `value` = '{$projectName}' WHERE t_key = 'company'");

file_put_contents(__DIR__.'/log.txt',$projectName.' '.$adminPass);