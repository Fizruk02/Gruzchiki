<?php

namespace project\modules\message;

class message {
    
    public function start( array $par=[] )
    {
		$settings = json_decode($par['message'], true);
        foreach($settings as &$tSetting){
			$tSetting = text()->variables($tSetting, $par);
			$tSetting = text()->shortcodes($tSetting, $par);
    	}

    	if($settings['chat_id'])
    	foreach($par['script_messages'] as &$script_message)
    	    $script_message['chat_id']=$settings['chat_id'];
    	
        if(!$par = echo_message_from_par($par)) return false;
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'],$par);
        return true;
    }

    
}