<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/functions/functions.php';
//require_once __DIR__ . '/../src/project/modules/yes_yes_yes/yes_yes_yes.php';
//use project\modules\yes_yes_yes\yes_yes_yes;
//set_time_limit(0);
$Telegram_botkey = setting("bot_key");
//
//$yes_yes_yes = new yes_yes_yes();
//$yes_yes_yes->start_yes();

$data_admin = singleQuery("SELECT `id`, `id_chat` 
               FROM `users` WHERE `phone` = ? 
               AND `id_cms_privileges` = 3
               AND `status` = 1 AND `cabinet_id` = ?", [ 79374413849, 3 ]);
echo count($data_admin);
//echo count($data_admin);
?>