<?php
namespace project\modules\bot_messages;

class bot_messages
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['bot_messages'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);

        switch($settings['type']){
            case 'select_a_language':
                $lanList = arrayQuery('SELECT * FROM `s_langs` l WHERE id IN (SELECT id_lan FROM dialogue_translate)');
                $kb = [];
                foreach ($lanList as $lan)
                    $kb[]= [['text' => $lan['name'], 'callback_data' => $lan['iso'] ]];

                $kb = ['inline_keyboard' => $kb];
                send_mess([ 'chat_id'=> $chat_id, 'body' => '🌐 Select a language'.PHP_EOL.'/lan - calling the language menu', 'kb'=> $kb ]);

                set_pos('bot_messages_listener_select', $par);
                return;
        }
        
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
        

        return true;
    }

    public function bot_messages_listener_select($par){
        global $obj, $chat_id, $user_settings, $message_id;

        $iso=$obj['message']['text']??'';
        if(!$lan=singleQuery('SELECT * FROM `s_langs` WHERE iso LIKE (?)', [ $iso ])){
            return notification('Select from the list');
        }

        setUserSetting(['chat_id'=> $chat_id, 'var'=> 'lan', 'val'=> $lan['id'], 'unique'=> true]);
        $user_settings['lan']=$lan['id'];
        $par[$par['script_step']] = $lan['iso'];
        $par[$par['script_step'].'_id'] = $lan['id'];
        methods()->edit_message('🌐 <b>'.strtoupper($lan['iso']).'</b>', false, $chat_id, $message_id);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }
    
}