<?php
namespace project\modules\tbot_api;

class tbot_api
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $message_id, $username, $first_name, $text_message;
        // if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['tbot_api'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);
        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }

    protected function set_approved_new_user( array $input ){
        if(query("UPDATE `users` SET `status` = 0, `cabinet_id` = ?, `id_cms_privileges` WHERE id_chat = ?", [ $input['cabinet_id'], $input['id_chat'] ]))
            send_mess(['body'=>"Вас одобрили. Ожидайте заказы!", 'id_chat'=> $input['id_chat']]);
    }
}

/*

$send_data = [
    'chat_id' => $chat_id,
    'text' => "ура"
];

function SendMessage($send_data)
{
    $url = "https://api.telegram.org/bot".TOKEN."/sendMessage";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // times out after 4s
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$send_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch); // run the whole process
    echo $result;
    curl_close($ch);
}

*/