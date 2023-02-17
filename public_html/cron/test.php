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

$cl = loadModule('yes_yes_yes');

while (true) {
    try {
        $cl->start_yes();
    } catch(\Exeption $e) {
	echo $e->getMessage().PHP_EOL;
    }
}


?>