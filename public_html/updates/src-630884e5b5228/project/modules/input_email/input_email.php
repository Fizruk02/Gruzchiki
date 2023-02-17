<?php

namespace project\modules\input_email;

class input_email {
    
    public function start( array $par=[] )
    {   
        if(!$par = echo_message_from_par($par)) return false;
        
        $settings = json_decode($par['input_email'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);
        
        $par['input_email']=json_encode($settings);
        
        if(!$type = $settings['type']) $type = 'input';
        
        switch($type){
            case 'input':
                set_pos($par['step'], $par);
            break;
            case 'send':
                
                
                $addresses=$settings['addresses'];
                if(!is_array($addresses)){
                    if(is_array(json_decode($addresses,true)))
                        $addresses=json_decode($addresses,true);
                    else
                        $addresses = explode(',', $addresses);
                }
                
                $fail = [];
                $success=[];
                foreach($addresses as $address){
                    $headers  = 'MIME-Version: 1.0' . PHP_EOL.'Content-type: text/html; charset=utf-8'. PHP_EOL;
                    if(!mail($address, $settings['title'], $settings['text'], $headers)) $fail[]='«'.$address.'»'; else $success[]='«'.$address.'»';
                }
                
                if(count($fail)===1)
                    tgMess('Не удалось отправить сообщение на почту '.$fail[1]);
                elseif(count($fail)>1)
                    tgMess('Не удалось отправить сообщение на следующие адреса: '.PHP_EOL.implode(',', $fail));
                    
                if(count($success)>0)
                    tgMess('Отправлено '.text()->num_word(count($success), ['письмо', 'письма', 'писем']));
                    
                $r=[];
                if(count($success)) $r['success']=$success;
                if(count($fail)) $r['fail']=$fail;
                $par[$par['script_step']]=$r;
                        
                if(!intermediate_function($par)) return;
                unset($par['input_email']);
                set_pos($par['step'], $par);
                the_distribution_module($par['script_source'], $par);
            break;
        }
        

        
        return true;
    }
    
    public function listener( array $par=[] )
    {
        global $text_message;
        
        $settings = json_decode($par['input_email'], true);
        $email = strip_tags($text_message);

        if(! filter_var($email, FILTER_VALIDATE_EMAIL) ){
            tgMess('введите корректную почту');
            return false;
        }
        
        if($settings['confirmation_code']){
            $code = rand(1, 9).rand(1, 9).rand(1, 9).rand(1, 9);
            $body = 'Код подтверждения:<br<br><h1>'.$code.'</h1><br>';
            $headers  = 'MIME-Version: 1.0' . PHP_EOL.'Content-type: text/html; charset=utf-8'. PHP_EOL;
        
            if(!mail($email, 'Код подтверждения', $body, $headers))
        		return tgMess('Ошибка при отправке кода авторизации на почту');
        	$par['input_email_confirmation_code']=$code;
        	$par['input_email_confirmation_code_attempt']=0;
        	$par['input_email_confirmation_code_limit']=5;
        	$par[$par['script_step']]=$email;
        	set_pos('input_email_confirmation_code', $par);
        	
        	tgMess('Пришлите код подтверждения, который мы отправили на адрес (код может идти до 20 минут, так же проверьте папку спам)'.$email);
        	return;
        }

        
        
        $par[$par['script_step']]=$email;
        if(!intermediate_function($par)) return;
        unset($par['input_email']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
    
    function input_email_confirmation_code($par){
        global $text_message;
        
        $limit = $par['input_email_confirmation_code_limit'];
        
        if(trim($text_message)!==$par['input_email_confirmation_code']){
            $n=$par['input_email_confirmation_code_attempt'];
            if($limit==$n+1) return tgMess('Вы ввели не верный код '.text()->num_word($limit, ['раз', 'раза', 'раз']).', попытки исчерпаны');
            $n++;
            $par['input_email_confirmation_code_attempt']=$n;
            set_pos('input_email_confirmation_code', $par);
            return tgMess('Код не верный, еще '.text()->num_word($limit-$n, ['попытка', 'попытки', 'попыток']));
        }
            
            
        if(!intermediate_function($par)) return;
        unset($par['input_email']);
        unset($par['input_email_confirmation_code']);
        unset($par['input_email_confirmation_code_limit']);
        unset($par['input_email_confirmation_code_attempt']);
        
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        
    }
    
}


















