<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
session_start();
if (  !$_SESSION['user']['role_id'] && (!($h = permission_to_use()['access'])&&$_POST['bot_key']!=setting('bot_key'))   ) return response_if_error($h['mess']);

echo json_encode([
    'success'=> 'ok'
    ,'data'=> loadFiles()->getFilesforweb( $_POST['group'] )
]);