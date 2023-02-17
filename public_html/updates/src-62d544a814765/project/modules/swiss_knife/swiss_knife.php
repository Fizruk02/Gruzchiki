<?php

namespace project\modules\swiss_knife;

class swiss_knife
{
    public function start( array $par=[] )
    {   
        global $chat_id, $message_id;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['swiss_knife'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);
       
		foreach($settings['functions'] as $func)
		switch($func){
			case 'delete_this_inline_keyboard':
				methods()->delete_this_inline_keyboard();
			break;
			case 'delete_this_message':
   			    methods()->delete_mess($chat_id, $message_id);
			break;
			case 'deletePressedKey':
   			    keyboards()->deletePressedKey();
			break;
			case 'copyMessage':
   			    methods()->copyMessage($settings['copy_message_from'], $settings['copy_message_id'], $settings['copy_message_to']);
			break;
			case 'editPreviousMessage':
				$s=$par['sendMessages'][$settings['edit_previous_message_step_id']]??false;
				if($s)
   			    methods()->edit_message_text_or_caption($settings['edit_previous_message_text'], false, $s[0][0]['channel_id']??0, $s[0][0]['message_id']??0);
			break;
			case 'remove_keyboard':
   			    $sm=send_mess(['kb'=>["remove_keyboard" => true ], 'body'=> $settings['remove_keyboard_message']?:'...' ]);
                if(!$settings['remove_keyboard_message']) methods()->delete_mess($chat_id, $sm[0]['message_id']);
			break;
		}
		
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
}