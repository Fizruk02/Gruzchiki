<?php
    namespace project\modules\access;
    
    class access
    {
        public function start( array $par=[] )
        {   
            global $original, $chat_id, $username, $message_id, $text_message, $user_settings, $user_status;
            if(!$par = echo_message_from_par($par)) return false;
            $settings = json_decode($par['access'], true);
            foreach($settings as &$tSetting)
                $tSetting = text()->variables($tSetting, $par);
            
            if(isset($settings['roles'])&&is_array($settings['roles'])&&count($settings['roles'])&&array_search($user_status,$settings['roles'])===false){
                $mess=$settings['mess']??"";
                if($mess) tgmess($mess);
                set_pos('access denied');
                return;
            }
            //$par[$par['script_step']] = ""; # передача данных текущего шага дальше
            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'], $par);
            return true;
        }
        
    }