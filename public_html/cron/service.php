<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public_html';
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

set_time_limit(0);
$Telegram_botkey = setting("bot_key");

/* парсим id пользователей в таблице log_connect и записываем в колонку chat_id в этой же таблице */
query("ALTER TABLE `log_connect` ADD `chat_id` BIGINT(20) NULL DEFAULT NULL AFTER `data`, ADD `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD INDEX `chat_id` (`chat_id`), ADD INDEX `date` (`date`);");
foreach(arrayQuery('SELECT * FROM `log_connect` WHERE chat_id IS NULL') as $r){
    $d=json_decode($r['data'],1);
    if(isset($d['callback_query']))$d['message']=$d['callback_query'];
    if(isset($d['message'])&&(isset($d['message']['chat']))&&($ch=$d['message']['chat']['id'])>0){
        query('UPDATE `log_connect` SET `chat_id`=?, `date`=? WHERE id='.$r['id'],[$ch,date("Y-m-d H:i:s", $d['message']['date'])]);
    } else query('DELETE FROM `log_connect` WHERE id='.$r['id']);
}

# удаляем неиспользуемые условия
query( 'DELETE FROM `s_conditions` WHERE id_group NOT IN(SELECT conditions FROM `s_steps_messages`)' );