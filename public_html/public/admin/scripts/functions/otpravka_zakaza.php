<?php
# не меняйте имя функции
function otpravka_zakaza($par){
    global $chat_id;
    $orderNum=rand(100,1000);
    $text='Заказ №'.$orderNum.PHP_EOL;
    $text.= 'Пользователь: '.text()->userLink($chat_id).PHP_EOL.PHP_EOL;

    foreach($par as $k=>$p){
    if(!$d=singleQuery('SELECT * FROM `script_blocks` WHERE type="input" AND id=?', [ $k ])) continue;
        $text .= '<b>'.$d['text'].'</b>'.PHP_EOL.' - '.$p.PHP_EOL;
    }

    send_mess(['body'=> 'Заказ отправлен продавцу! Номер заказа: <b>'.$orderNum.'</b>']);
    send_mess(['body'=> '(ПРИМЕР) Продавцу будет отправлен заказ:']);
    send_mess(['body'=> $text]);
}