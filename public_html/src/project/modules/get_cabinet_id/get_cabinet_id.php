<?php
namespace project\modules\get_cabinet_id;

class get_cabinet_id
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $message_id, $username, $first_name, $text_message, $bot_id;
        // if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['get_cabinet_id'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);

        
				$cabinet = singleQuery("SELECT `cabinet_id` FROM `bot` WHERE `id` = ?", [ $bot_id ]);
        
        $par['cabinet_id'] = $cabinet['cabinet_id'];
        
        /**
        $kb=[];
        $kb[]= [
            ['text' => '', 'callback_data' => json_encode([ 'mtd'=> 'getCabinetId2', 'id'=> $id ])]
        ]; 
        $kb=["inline_keyboard"=>$kb];
        
        send_mess([ 'body'=>$text, 'kb'=> $kb ]); // , 'reply_to_message_id'=> $message_id, 'id_chat'=> $chat_id
        
        methods()->delete_mess($chat_id, $id_message);
        methods()->editKb([ 'kb'=> $kb ]);
        methods()->editMsg([ 'text'=> $text, 'kb'=> $kb ]);
        
        DIALTEXT('');
        
        text()->num_word($period_banned, ['день', 'дня', 'дней']);
        $userLink = $user['username'] ? '@'.$user['username'] : "<a href=\"tg://user?id=$id\">{$user['first_name']}</a>";
        
        */
        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
    private function get_cabinet_id_1( $input ){
        global $original, $chat_id, $message_id;
        
    }
    
    private function getCabinetId2( $input ){
        global $original, $chat_id, $message_id;
        $id = (int) $input['id'];
    }
    
    
    /**
     * МОДЕЛИ
     */
     
    private function getUser( $id ){
        $id = (int) $id;
        return singleQuery('SELECT * FROM `tables` WHERE `id` = '.$id);
    }
    
}