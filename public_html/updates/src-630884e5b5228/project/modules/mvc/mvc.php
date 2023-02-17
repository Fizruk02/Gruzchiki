<?php
namespace project\modules\mvc;

class mvc
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['mvc'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);

        send_mess(['body'=>'Модуль <b>mvc</b>', 'id_chat'=> $chat_id]);
        
        /**  данные из test формы
        $var1 = $settings['textarea'];
        $var2 = $settings['input'];

        $kb=[];
        array_push($kb, [
            ['text' => $var2, 'callback_data' => $var2]
        ]); 
        $kb=["inline_keyboard"=>$kb];
        
        send_mess(['body'=>$var1, 'id_chat'=> $chat_id, 'kb'=> $kb]);
        */
        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
}