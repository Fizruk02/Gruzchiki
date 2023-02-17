<?php

namespace project\modules\input_phone;

class input_phone {
    
    public function start( array $par=[] )
    {   
        if(!$par = echo_message_from_par($par)) return false;
        set_pos($par['step'], $par);
        
        return true;
    }
    
    public function listener( array $par=[] )
    {
        global $text_message, $obj;

        $settings = json_decode($par['input_phone'], true);
				
            $skip_commands = preg_split("/\\r\\n?|\\n/", $settings['skip_commands']);
            $skip_commands = array_values($skip_commands);
            if($settings['skip_commands']&&array_search($text_message, $skip_commands)!==false){
            $par[$par['script_step']]='';
            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'],$par);
            return;
        }
				
		if($obj['message']['forward_from'] &&$obj['message']['contact']) return notification('На этом шаге нельзя пересылать сообщения');
		
		if($settings['only_send_contacts']&&!$obj['message']['contact']) return notification('Нажмите на кнопку, чтобы отправить свой номер телефона');
      
        $pars_num  = $obj['message']['contact']['phone_number'];
        if(!$pars_num) $pars_num = $text_message;

        $pars_num = numbers($pars_num);
        if(strlen($pars_num)<10) return tgMess('введите корректный номер');

        if($settings['only10']) $pars_num = strrev(substr(strrev($pars_num), 0, 10));
				
        $par[$par['script_step']]=$pars_num;
        if(!intermediate_function($par)) return;
        unset($par['input_phone']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
}