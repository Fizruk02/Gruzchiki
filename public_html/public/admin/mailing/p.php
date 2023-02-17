<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
if (!($h = permission_to_use()['access'])&&$_POST['bot_key']!=setting('bot_key')) return response_if_error($gethash['mess']);
require __DIR__.'/methods.php';
if(!method_exists(($cl = new cl($_POST)), ($method = $_GET['q']))) return response_if_error('метод '.$method.' не найден');
echo is_array( $resp =  $cl->$method( array_merge(['accessdata'=>$h?:false],$_POST) ) ) ? json_encode( $resp ) : $resp;