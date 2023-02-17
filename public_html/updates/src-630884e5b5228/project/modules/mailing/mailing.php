<?php

namespace project\modules\mailing;

class mailing
{
    public function start( array $par=[] )
    {   
        global $chat_id, $Telegram_botkey;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['mailing'], true);
        foreach($settings as $key=> &$tSetting){
            $tSetting = text()->variables($tSetting, $par);
            # if($key!='addresses')
            $tSetting = text()->shortcodes($tSetting, $par);
        }
           

        //$market_items = $transmitData['step_collection_of_items_0yt70q'];
        $market_items = [];

        
        $addressList = [];
        
        if(is_array($settings['addresses'])){
            foreach($settings['addresses'] as $address)
                $addressList[] = ['id'=>$address];
        } else {
            switch($settings['addresses']){
                case 'my':
                    $addressList = [['id'=>$chat_id]];
                break;
                
                
            }
        }

    
        $param = [
                    
                    'mailing_name' => 'from bot'
                    ,'date_begin' => $settings['date']
                    ,'time_begin' => $settings['time']
                    ,'text_message' => $settings['text']
                    ,'mailingType' => 'message'
                    ,'filegroup' => $settings['files']
                    ,'fromBot' => $Telegram_botkey
                    ,'address' => json_encode($addressList)
                    //,'market_items' => json_encode($market_items)
    
            ];

        $res = post(_dir_.'/admin/mailing/mailing_set.php', $param);
     
        $res = json_decode($res, true);
        if($res['success']=='ok')
            tgMess('Рассылка создана');
        else
            tgMess($res['err']? $res['err']:'Ошибка при создании рассылки');
 





        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
    function mailingCustomvariant($par){
        global $chat_id;
        $mid=$par['mid'];
        if(!is_numeric($mid)) return notification('Некорректный id рассылки');
        if(singleQuery('SELECT * FROM `mailing_voting_answers` WHERE id_mailing=? AND id_user=?',[ $mid,$chat_id ])) return notification('Вы уже отвечали');
        tgmess('Пришлите свой вариант ответа');
        set_pos('mailingCustomvariantListener', $par);
    }

    function mailingCustomvariantListener($par){
        global $chat_id, $text_message;
        $mid=$par['mid'];
        query('INSERT INTO `mailing_voting_answers` (`custom_answer`, `id_user`, `id_mailing`) VALUES (?,?,?)',[ $text_message,$chat_id, $mid ]);
        if($e=db()->err()) tgmess('Ошибка:'.PHP_EOL.$e['err']);
        tgmess('Записал');
        set_pos('');
    }

    function mlngCntr($par){
        global $original, $chat_id, $from_id;

        $user_id=$from_id?:$chat_id;

        $counter = $par['n'];
        $dismiss = $par['ds'];
        $id_mailing = $par['mid'];
        $type = $par['tp']?:1;
        $dismissStatus = false;


   	    if($counterData = singleQuery('SELECT * FROM `mailing_voting_answers` WHERE id_user = ? AND id_chat = ? AND id_mailing = ?', [ $user_id, $chat_id, $id_mailing  ]))
    	{
    	
        	if(!$dismiss)
        	    return notification('Вы уже голосовали', false);
        	else {
        	    
        	    if($counterData['id']==$counter) return;
        	    query('DELETE FROM `mailing_voting_answers` WHERE id = ?',[$counterData['id']]);
        	    $dismissStatus = true;

        	    
        	}
    	}
    	
    	query('INSERT INTO `mailing_voting_answers` (`id_variant`, `id_user`, `id_chat`, `id_mailing`) VALUES (?,?,?,?)', [ $counter,$user_id,$chat_id,$id_mailing ]);
    	
        $label = singleQuery('SELECT variant FROM `mailing_voting_variants` WHERE id=?', [$counter])['variant'];

        notification($label, false);

        $reply_markup = $original['callback_query']['message']['reply_markup'];
        foreach($reply_markup['inline_keyboard'] as &$rows){
            foreach($rows as &$keys){
                $cd = json_decode($keys['callback_data'], 1);
                if($cd['mtd']=='mlngCntr'){
                    $count = (int) singleQuery('SELECT count(*) c FROM `mailing_voting_answers` WHERE id_variant=? AND id_mailing=?', [ $cd['n'], $cd['mid'] ])['c'];
                    $name=singleQuery('SELECT variant FROM `mailing_voting_variants` WHERE id=?', [$cd['n']])['variant'];
                    
                    $keys['text'] = $name.' '.($count?'('.$count.')':'');
                }
            }
            
        }

        $users = arrayQuery('SELECT id_chat, id_message FROM `mailing_address`
                             WHERE id_mailing = :mid AND id_message>0 AND id_chat IN(SELECT id_chat FROM `mailing_voting_answers` WHERE id_mailing = :mid)
                             ORDER BY IF(id_chat = :us,0,1)', [ ':mid'=>$id_mailing, ':us'=>$chat_id ]);
        foreach($users as $user){
            methods()->edit_inline_keyboard($user['id_chat'], $user['id_message'], $reply_markup);
        }


        

    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}