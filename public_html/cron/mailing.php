<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public_html';
require $_SERVER['DOCUMENT_ROOT'] . '/admin/functions/functions.php';

set_time_limit(0);
$Telegram_botkey = setting("bot_key");

/**
 * ПОВТОРЫ
 */


$repeats = arrayQuery('
    SELECT * FROM `mailings` m
    WHERE `status` = 10 AND date_begin <= NOW() AND
    (
        (    `repeat` = "daily" AND weekdays LIKE(CONCAT("%",DAYOFWEEK(date_begin),"%")) AND NOT EXISTS( SELECT * FROM mailings WHERE repeat_src = m.id AND date(date_begin) = date(NOW()) )    ) OR
        (    `repeat` = "monthly" AND DAYOFMONTH(date_begin) = DAYOFMONTH(NOW()) AND NOT EXISTS( SELECT * FROM mailings WHERE repeat_src = m.id AND date(date_begin) = date(NOW()) )    ) OR
        (    `repeat` = "interval" AND `interval`>0 AND MOD(FLOOR((  UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date_begin)  )/3600), `interval`) = 0 AND NOT EXISTS(  SELECT * FROM mailings WHERE repeat_src = m.id AND date_begin > NOW()-INTERVAL m.`interval` HOUR )    )
    )
    ');
//        (    `repeat` = "weekly" AND DAYOFWEEK(date_begin) = DAYOFWEEK(NOW()) AND NOT EXISTS( SELECT * FROM mailings WHERE repeat_src = m.id AND date(date_begin) = date(NOW()))    ) OR
foreach ($repeats as $repeat) {
    if (($filter = $repeat['filter']) >= 0) {
        $query = $filter ? singleQuery('SELECT `query` FROM `mailings_filters` WHERE id=?', [$filter])['query'] : 'SELECT id, chat_id, username, first_name, 1 `check` FROM `usersAll`';
        $addressList = arrayQuery($query, [], true);
    } else {
        $addressList = array_map(function ($it) {
            return json_decode($it['data'], 1);
        }, arrayQuery('SELECT `data` FROM `mailing_address` WHERE id_mailing = ?', [$repeat['id']]));

    }
    if(!count($addressList)) continue;

    if ($repeat['repeat'] == 'interval') {
        $date = date("Y-m-d H:i:s", time());
    } else {
        $date = date("Y-m-d") . ' ' . date("H:i:s", strtotime($repeat['date_begin']));
    }


    $param = [
        'name' => $repeat['name']
        , 'date_begin' => $date
        , 'keyboard' => $repeat['keyboard']
        , 'text' => $repeat['body']
        , 'filegroup' => $repeat['files']
        , 'filter' => 0
        , 'event'=> [
            'status' => $repeat['event_status']
            , 'end' => $repeat['event_end']
            , 'text' => $repeat['event_notify']
            , 'ntftext' => $repeat['event_notify_time']
            , 'ntftime' => $repeat['event_notify_wkdays']
            , 'text' => json_decode($repeat['ntfwkdays'])
        ]
        , 'repeat_src' => $repeat['id']
        , 'bot_key' => $Telegram_botkey
        , 'address' => json_encode($addressList)
    ];

    if ($repeat['data']) {
        $rd = json_decode($repeat['data'], 1);
        if (is_array($rd))
            $param = array_merge($param, $rd);
    }

    $res = post(_dir_ . '/admin/mailing/p.php?q=set', $param);

    //$res = json_decode($res, true);
    //if($res['success']=='ok')
    //    tgMess('Рассылка создана');
    //else
    //    tgMess($res['err']? $res['err']:'Ошибка при создании рассылки');


}



/**
 * Рассылаем уведомления об окончании события, если:
 * событие установлено
 * окончание события больше текущей даты
 * текущая дата больше даты начала рассылки
 * текущее время больше времени начала уведомлений
 */
foreach(arrayQuery('SELECT * FROM `mailings` WHERE `status`=2 AND event_status=1 AND event_end>NOW() AND
                           ((event_notify_type="days" AND event_notify_time<=CURTIME() AND date(NOW())>date(date_begin)) OR 
                           (event_notify_type="hours" AND NOW()>date_begin))') as $m) {

    $def=strtotime($m['event_end'])-time();
    $days=round($def/86400);
    $hours=round($def/3600);
    $minute=round($def/60);

    if($m['event_notify_type']==='days'){
        $wkdays=json_decode($m['event_notify_wkdays'], 1);
        $n=date( "N" )-1;
        if(!$wkdays[$n]) continue;
        $users=arrayQuery('SELECT * FROM `mailing_address` WHERE id_mailing=? AND date(IFNULL(event_end_notify,NOW()-INTERVAL 1 DAY)) <> date(NOW())', [ $m['id'] ]);

    }elseif($m['event_notify_type']==='hours'){
        if($minute%($m['hoursto']*60)) continue;
        $users=arrayQuery('SELECT * FROM `mailing_address` WHERE id_mailing=? AND(event_end_notify IS NULL OR event_end_notify<NOW()-INTERVAL 50 MINUTE)', [ $m['id'] ]);
    }

    if(!count($users)) continue;

    $days=text()->num_word($days, ['день', 'дня', 'дней']);
    $hours=text()->num_word($hours, ['час', 'часа', 'часов']);
    $vr=[ '#days#'=> $days, '#hours#'=> $hours, ];
    $text=$m['event_notify_text'];
    $text=str_replace(array_keys($vr), array_values($vr), $text);

    foreach($users as $a){

        send_mess([
            'body'=> $text,
            'id_chat'=> $a['id_chat'],
            'reply_to_message_id'=> $a['id_message'],
        ]);
        query('UPDATE `mailing_address` SET `event_end_notify` = NOW() WHERE id=?', [ $a['id'] ]);
    }


}

/**
 * Отмечаем события завершенными
 */
if($m=singleQuery('SELECT * FROM `mailings` WHERE event_end < NOW() AND event_status=1 LIMIT 1')){
    query('UPDATE `mailings` SET `event_status` = 10 WHERE id=?', [ $m['id'] ]);
    $text=$m['body'].PHP_EOL.PHP_EOL.'<b>✅ ЗАВЕРШЕНО</b>';

    foreach(arrayQuery('SELECT * FROM `mailing_address` WHERE id_mailing=?', [ $m['id'] ]) as $u) {
        $data = json_decode($u['data'], 1);
        $body = $text;
        foreach ($data as $k => $d) $body = str_replace("#$k#", $d, $body);

        methods()->editMsg( [ 'message_id'=> $u['id_message'], 'chat_id'=> $u['id_chat'], 'text'=> $body ] );

    }

    exit;
}


/**********************************/



$row = singleQuery("SELECT * FROM mailings m WHERE m.status = 1");
if (!$row) {
    $row = singleQuery("SELECT * FROM mailings m WHERE m.status = 0 AND m.date_begin < NOW() ORDER BY id LIMIT 1");

    if($row) {
        if($f=get_custom_function(setting('function_before_mailing'))) {
            $f['funcName']($row);
        }
    }
}

if (!$row) exit;

$id_mailing = $row['id'];
$files = $row['files'];
$market_items = json_decode($row['market_items'], true);


$body = $row['body'];
$type_mailing = $row['type_mailing'];
query('UPDATE mailings SET status = 1 WHERE id = ?', [ $id_mailing ]);


$result_address = arrayQuery('SELECT * FROM mailing_address WHERE id_mailing = ? AND status = 0 LIMIT 200', [$id_mailing]);

if (!count($result_address))
    return query('UPDATE mailings SET status = 2 WHERE id = ?', [$id_mailing]);


foreach ($result_address as $row_address) {
    $id = $row_address['id'];
    if(!singleQuery('SELECT * FROM mailing_address WHERE id=? AND status = 0', [ $id ])) continue;
    $data = json_decode($row_address['data'], 1);
    $keyboard = $row['keyboard'];
    $keyboard = text()->variables($keyboard, array_merge($row_address, $data ?: []));
    $keyboard = json_decode($keyboard, 1);
    $tbody = $body;
    foreach ($data as $k => $d)
        $tbody = str_replace("#$k#", $d, $tbody);
    $id_chat = $row_address['id_chat'];
    $par = ['id_chat' => $id_chat, 'body' => $tbody, 'kb' => $keyboard, 'files' => $files];


    /**
     * если вместо отправки указана функция
     * возвращайте true или false
     */
    if($f=get_custom_function(setting('send_mess_in_mailing'))) {
        $st=$f['funcName']($par);
        if($st===false) $st=1;
        $sm=[['message_id'=> 0]];
    } else {
        $sm = send_mess($par);
        $st=isset($sm[0])?1:-1;
    }
    //echo setting('send_mess_in_mailing');
    print_r($st);
    query('UPDATE mailing_address SET status = ?, id_message=? WHERE id = ?', [$st,  $st===1?$sm[0]['message_id']:0, $id]);

}
   
    
    
