<?

namespace systems\classes\language;

class language
{
    public function get_message( array $par=[] ){
        global $user_settings;
        $name = $par['name'];
        
        $chat_id = $GLOBALS['chat_id'];
        
        /**
         *  1. ищем язык в настройках пользователя, если он выбрал его в боте, если нашли, то указываем его
         *  2. ищем в базе список языков с  заполненными переводами
         *    2.1. если только один, то указываем его
         *    2.2. если таких языков несколько, то:
         *      2.2.1. берем пользовательский язык в телеграме, ищем его в списке из п.2, если находим, указываем его
         *      2.2.2. если предыдущий пункт неудачный, то отправляем пользователю список языков для выбора. На текущую
         *             сессию устанавливается язык по умолчанию, если такого нет, то первый в списке языков
         */
         
        # Если передали chat_id, то ищем язык пользователя, переданного в параметрах
        if( $par['chat_id'] && $par['chat_id'] != $chat_id ){
            $parChatId = $par['chat_id'];
            # ищем язык в настройках пользователя
            $lan = singleQuery('SELECT value FROM `user_settings` WHERE id_chat = :id_chat AND variable = "lan"', [ ':id_chat'=> $parChatId ])['value'];
            $chat_id = $parChatId;
        } else
            $lan = $user_settings['lan']; # язык в настройках пользователя, которые выведены при старте сессии в файле бота
        
        if(!$lan){
            $lanList = arrayQuery('SELECT * FROM `s_langs` l WHERE id IN (SELECT id_lan FROM dialogue_translate)');
            # если только один вариант, то в настройках указываем его
            if(count($lanList)==1){
                $lan = $lanList[0]['id'];
                
            }
                
            else
            {   # если несколько вариантов, то берем язык из базы usersAll и ищем его в списке доступных языков
                $b = false;
                $userAllQueryLan = singleQuery('SELECT * FROM `usersAll` WHERE chat_id = :chat_id', [ ':chat_id'=> $chat_id ])['lan'];
                foreach($lanList as $lanRow)
                if($lanRow['iso'] == $userAllQueryLan){
                    $lan = $lanRow['id'];
                    $b = true;
                }
                
                # если не нашли, то ставим язык по умолчанию, если по умолчанию не установлен, то первый язык
                if(!$b){
                    $lan = false;
                    foreach($lanList as $lanRow)
                    if($lanRow['by_default']==1){
                        $lan = $lanRow['id'];
                        break;
                    }
                    
                    if($lan===false)
                    $lan = $lanList[0]['id'];
                    
                    $this->languageSendTheLanguageSelectionMenu([ 'defaultLanguage'=> $lan, 'chat_id'=> $chat_id ]);
                    
                    
                }
            }
            
            if( $parChatId = $par['chat_id'] && $parChatId != $chat_id ) {
                
            } else
            $user_settings['lan'] = $lan;
            
            setUserSetting(['chat_id'=> $chat_id, 'var'=> 'lan', 'val'=> $lan, 'unique'=> true]);
        }
        
        $row = singleQuery('SELECT dt.body, d.files FROM `dialogue_translate` dt, `dialogue` d WHERE dt.id_dial = d.id AND d.name = :name AND id_lan = :id_lan', [ ':name'=> $name, ':id_lan'=> $lan ]);
        
        if (!$row)
            $row = singleQuery('SELECT dt.body, d.files FROM `dialogue_translate` dt, `dialogue` d WHERE dt.id_dial = d.id AND d.name = :name', [ ':name'=> $name ]);

        return $row ? $row:['body' => 'error: no message text ('.$name.')'];
    }
    
    public function selectUserLanguage( array $par=[] ){
        global $chat_id;
        $idLan = $par['id'];
        if(!$idLan)
            return notification( 'Не передан id языка' );
        
        setUserSetting(['chat_id'=> $chat_id, 'var'=> 'lan', 'val'=> $idLan, 'unique'=> true]);

        notification( 'Language changed!', false );
    }
    
   
    public function languageSendTheLanguageSelectionMenu( array $par=[] ){
        $chat_id = $par['chat_id'] ? $par['chat_id'] : $GLOBALS['chat_id'];
        $lanList = arrayQuery('SELECT * FROM `s_langs` l WHERE id IN (SELECT id_lan FROM dialogue_translate)');
        $kb = [];
        foreach ($lanList as $_lanRow) 
            array_push($kb, [['text' => $_lanRow['name'], 'callback_data' => json_encode(['system' => 'selectUserLanguage', 'id' => $_lanRow['id'] ]) ]]);
        
        $kb = ['inline_keyboard' => $kb];
        send_mess([ 'chat_id'=> $chat_id, 'body' => '🌐 Select a language'.PHP_EOL.'/lan - calling the language menu'.PHP_EOL.'/start - to update the menu', 'kb'=> $kb ]);
    }
   
   
   
   
   
    
    
}