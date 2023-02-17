<?php
$d=$_SERVER['DOCUMENT_ROOT'].'/admin';
include_once($d.'/functions/functions.php');

$permission_to_use = permission_to_use();

if(!$permission_to_use['access']){
    echo "<script>
        var cookies = document.cookie.split(/;/);
        for (var i = 0, len = cookies.length; i < len; i++) {
            var cookie = cookies[i].split(/=/);
            document.cookie = cookie[0] + '=;max-age=-1';
        }
        window.location.href='/admin/login.php'
        </script>";
    exit;
}

if(singleQuery('SELECT * FROM `settings` WHERE `t_key`="startFunction" AND `value`="the_bot_is_unavailable"'))
    die( '<div style="display: grid; height: 96vh; align-items: center; justify-content: center;">
<h1 style="font-family: monospace; font-size: 600%;">ПРОЕКТ НЕДОСТУПЕН</h1>
</div>');

define('user_status', $permission_to_use['user_status']);
define('uidindb', $permission_to_use['uid']);
define('dep_access', $permission_to_use['dep_access']);
define('id_chat', $permission_to_use['id_chat']);

if(setting('yagnimode')) echo '<style>[yagni] { display:none; }</style>';

$AccountImageFromPermission = '/files/systems/nophoto.jpg';
if($permission_to_use['image'])
    $AccountImageFromPermission = '/'.singleQuery('SELECT small_size FROM `files` WHERE id_group = ? ORDER BY id DESC', [$permission_to_use['image']])['small_size'];    