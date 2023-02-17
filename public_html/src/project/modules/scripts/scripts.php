<?php

namespace project\modules\scripts;

class scripts
{
    
    public static $cicle = 0;
    public static $PAR = [];
    
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['scripts'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);

        $last = $this->getProjectLastStep();
        if($last['position']!=='start-scripts')
            set_pos('start-scripts', $par);
            
        if($settings['type']=='script'){
            unset($par['scripts']);
            //set_pos($par['step'], $par);
            $this->sendpos(false);
            $this->distr([ 'id_script'=> $settings['id_script'] ]);
            return;
        }

        if($settings['type']=='logClear'){
            $this->sendpos(false);
        		set_pos($par['step'], $par);
        		the_distribution_module($par['script_source'], $par);
            return;
        }



        if( $trigger = singleQuery('SELECT id_script FROM `script_triggers` WHERE :mess LIKE(`trigger`)', [ ':mess'=> $text_message ]) ){
            $this->sendpos(false);
            $this->distr([ 'id_script'=> $trigger['id_script'] ]);
            return;
        }

        //if($last['position']!=='start-scripts'){
        //    $this->sendpos(false);
        //    return;
        //}

        if( $last = singleQuery('SELECT p.id_script, p.id_block, p.parameters, b.type
                                 FROM `script_position` p
                                 JOIN script_blocks b ON b.id = p.id_block
                                 WHERE p.id_user = :id_user', [ ':id_user'=> $chat_id ]) ){

            if($last['type']=='input'){
                $arr = json_decode($last['parameters'], true);
                $arr['id_script'] = $last['id_script'];
                $arr['id_block'] = $last['id_block'];
                return $this->inputListener( $arr );
                
            }

            if($last['type']=='inputFiles'){
                $arr = json_decode($last['parameters'], true);
                $arr['id_script'] = $last['id_script'];
                $arr['id_block'] = $last['id_block'];
                return $this->inputfilesListener( $arr );
                
            }   
     
            $this->distr( $last );
            return;
        }


        if( $trigger = singleQuery('SELECT id_script FROM `script_triggers` WHERE `trigger` LIKE("default")') ){
            $this->sendpos(false);
            $this->distr([ 'id_script'=> $trigger['id_script'] ]);
            return;
        }
        //set_pos($par['step'], $par);
        //the_distribution_module($par['script_source'], $par);

    }
    
    private function getProjectLastStep(){
        global $chat_id;
        return singleQuery('SELECT * FROM `steps` WHERE id_chat = ? ORDER BY `id`  DESC', [$chat_id]);
    }
    
    
    
    
    public function goTo( $script, $par=[] ){
        $script = trim($script);

        if(!$scr = singleQuery('SELECT id FROM `script_list` WHERE (id = :script OR name LIKE (:script))', [ ':script'=> $script ]))
            return send_mess(['body'=> 'Скрипт "'.$script.'" не найден' ]);

        unset($par['parameters']);
        unset($par['id']);
        unset($par['name']);
        unset($par['text']);
        unset($par['par']);
        $par['id_script'] = $scr['id'];
        //$this->sendpos($par['id_script'], $par['id'], $par);
        $this->distr( $par );
        return $this->$PAR;
    }


    function gotopos( $par ){
        global $chatId;
        $script = $par['par']['script'];
        $block = $par['par']['block'];
        $actionblock = "";
        $blocks = arrayQuery('SELECT id FROM `script_blocks` WHERE id_script=? ORDER BY num', [ $script ]);
        if($blocks){
            $blocks = array_map(function($value) {
                return $value['id'];
            }, $blocks);
            $sb = array_search($block, $blocks);
            if($sb!==false)
                $actionblock = $blocks[$sb-1];
        }

        query('UPDATE `script_position` SET id_block=?, id_script=? WHERE id_user=?', [ $actionblock, $script, $chatId ]);

        $par['id_script'] = $script;

        $this->distr($par);
    }

    public function distr( $par ){
        global $chat_id;
        unset($par['parameters']);
        $this->$cicle++;
        if($this->$cicle>10)
            return send_mess(['body'=> 'Защита от цикла']);
        $id_script = $par['id_script'];
        
        $lastPar = [];
        if($last = singleQuery('SELECT id_script, id_block, parameters FROM `script_position` WHERE id_user = :id_user', [ ':id_user'=> $chat_id ])){
            $lastPar=json_decode($last['parameters'], true);
        }
        
        $blocks = arrayQuery('SELECT * FROM `script_blocks` WHERE id_script = :id_script AND activate = 1 ORDER BY num', [ ':id_script'=> $id_script ]);
        if(!count($blocks)) return;
        $target=0;
        if($last['id_script']==$id_script){
            foreach($blocks as $key=> $block)
                if($last['id_block']==$block['id']) $target = $key+1;
        }
        query('DELETE FROM `script_block_control` WHERE id_user=? AND type="stop" AND status = 0', [ $chat_id ]);
        if($target<count($blocks)){
            
            $f = $blocks[$target]['type'];
            //$par = $blocks[$target];
            
            $par['id'] = $blocks[$target]['id'];
            $par['display'] = $blocks[$target]['display'];
            $par['name'] = $blocks[$target]['name'];
            $par['id_script'] = $blocks[$target]['id_script'];
            $par['text'] = $blocks[$target]['text'];
            $par['par'] = json_decode($blocks[$target]['par'], true);
            $par['keyboard'] = json_decode($blocks[$target]['keyboard'], true);

            
            $blockControl=json_decode($par['par']['blockControl'], true);
            if($blockControl['timeout'] && $blockControl['script']){
                query('DELETE FROM `script_block_control` WHERE id_user=? AND id_script=? AND id_block=? AND status = 0', [ $chat_id, $id_script, $par['id'] ]);
                query('INSERT INTO `script_block_control` (`date_go_to_script`, `go_to_script`, `type`, `id_user`, `id_script`, `id_block`) VALUES (NOW()+INTERVAL ? MINUTE,?,?,?,?,?)',
                                                    [ $blockControl['timeout'], $blockControl['script'], $blockControl['type'], $chat_id, $id_script, $par['id'] ]);
            }
            if($f==='goto') $f='gotopos';
            $this->$f($par);
        }
        
        else{
            $this->sendpos(false);
            $last = $this->getProjectLastStep();
            if($last['position']=='start-scripts'){
                query('DELETE FROM `steps` WHERE id=?', [ $last['id'] ]);
                $lastPar = json_decode($last['parameters'], true);
                
                foreach(['id', 'display', 'name', 'text', 'par', 'id_block', 'send'] as $v) unset($par[$v]);
                
                $blocks=arrayQuery('SELECT id, display, type, name FROM `script_blocks`');
                $par['block_data']=[];
                foreach(array_keys($par) as $p){
                    foreach($blocks as $block)
                        if($block['display']==$p)
                            $par['block_data'][$p]=$block;
                }

                $lastPar[$lastPar['script_step']]=$par;
                set_pos($lastPar['step'], $lastPar);
                the_distribution_module($lastPar['script_source'], $lastPar);
            }

        }

    }


    
    private function mainBlockchain( $par ){
        $this->sendpos(false);
        $techname = $par['par']['techname'];
        foreach(['id', 'display', 'name', 'text', 'par', 'id_block', 'send'] as $v) unset($par[$v]);
        
        $blocks=arrayQuery('SELECT id, display, type, name FROM `script_blocks`');
        $nPar=[];
        foreach(array_keys($par) as $p)
            foreach($blocks as $block)
                if($block['display']==$p)
                    $par[$p]=$block;
        go_to_blockchain($techname, ['scripts_data'=>$nPar]);
    }
    
    private function func( $par ){
        
        $text = $par['text'];
        $parBlock = $par['par'];
        $func = $parBlock['name'];
        $funcDir = $_SERVER['DOCUMENT_ROOT'].'/admin/scripts/functions/'.$func.'.php';
        if(file_exists($funcDir)){
            require $funcDir;
            $resp = $func($par);
            if($resp=='stop') return;
            $par[$par['display']]=$resp;
        }
        
        $this->sendpos($par['id_script'], $par['id'], $par);
        $this->distr($par);
    }
    
    private function message( $par ){
        
        
        $kb=$this->keyboard($par['keyboard']);
        
        $text = explode('||', $par['text']);
        $smpar = ['body'=> text()->variables($text[array_rand($text, 1)], $par), 'files'=> $par['par']['files'] ];
        if($kb) $smpar['kb'] = $kb;
        $sm = send_mess($smpar);
        $par['send'][$par['id']]=$sm;
        $this->sendpos($par['id_script'], $par['id'], $par);
        $this->distr($par);
        $this->$PAR = $par;
    }
    
    private function keyboard($kbs){
        $keys = $kbs['keys'];
        $cols = $kbs['cols']?:1;
        
        
        foreach($keys as $key)
        if($key[0]!='' && $key[1]!='')
            $kb[] =  ['text' => $key[0], strpos($key[1], '//')?'url':'callback_data' => $key[1]];
        
        if(!count($kb)) return false;
        $kb = array_chunk($kb,$cols);
        $kb=["inline_keyboard"=>$kb];
        return $kb;
        
    }
    
    private function inputFiles( $par ){
        $text = $par['text'];
        
        $mpar = ['body'=> text()->variables($text, $par), 'files'=> $par['par']['files'] ];

        
        send_mess($mpar);
        $this->sendpos($par['id_script'], $par['id'], $par);
        $this->$PAR = $par;
    }
    
    
    private function inputfilesListener( $par ){
        global $obj, $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        
        $sendBtnLabel = 'Отправить файлы';
        $settings = $par['par'];
        if($text_message == $sendBtnLabel) { # если наэали на кнопку, отправляющую файлы на сервер (окончание)
        
            $group = loadFiles()->getFileGroup();
            
            foreach($settings['files'] as $fileItem)
                query('UPDATE `files` SET `id_group` = :id_group  WHERE `id` = :id', [':id_group'=> $group, ':id'=> $fileItem['id_file']]);
            
            $par[$par['display']]=$group;
            $this->distr($par);
            return;
        }
        
        
       
        $sm = send_mess(['body'=>DIALTEXT('inputFilesUploadingAFile'), 'id_chat'=>$chat_id]);
        $id_system_mess=$sm[0]['message_id'];
        
        
        
        $document = $obj['message']['document'];
        $photo = $obj['message']['photo'];
        $video = $obj['message']['video'];

        # когда загружают несколько файлов, бот сначала их загружает, потом обрабатывает, поэтому, чтобы потом сообщения не вываливались разом, контролируем это
        $mdGrpId=['number'=>0, 'status'=>0];
        if(isset($obj['message']['media_group_id'])){ 
            $media_group_id = 'media_group_id_'.$obj['message']['media_group_id'];
            if(isset($par[$media_group_id]))
                $par[$media_group_id]['number']++;
            else 
                $par[$media_group_id]=['number'=>1, 'status'=>0];
         
            $mdGrpId = $par[$media_group_id];
        }   
        
        if(count($settings['files'])==10){
            methods()->delete_mess($chat_id, $id_system_mess);
            
            if($mdGrpId['number']<2 || $mdGrpId['status']!=3)
                tgMess(DIALTEXT('inputFilesTheMaximumNumberOfFilesIs10'));
                
            if($media_group_id) $par[$media_group_id]['status']=3;
            set_pos($par['step'], $par);
            
            return;
        }
       
        
        if(!count($photo)&&!count($document)&&!count($video)){
            methods()->delete_mess($chat_id, $id_system_mess);
            tgMess(DIALTEXT('inputFilesSendTheFiles'));
            return;
        }
        
        methods()->edit_message(DIALTEXT('inputFilesSearchForAGroupOfFilesInAGroup'), '', $chat_id, $id_system_mess);
        
        
        methods()->edit_message(DIALTEXT('inputFilesSendingAFileToTheServer'), '', $chat_id, $id_system_mess);
        if(count($photo))
        $save_info = loadFiles()->savePhoto($photo, $photo[0]['file_id']);
        
        if(count($document))
        $save_info = loadFiles()->saveDocument($document, $document['file_id']);
        
        if(count($video))
        $save_info = loadFiles()->saveVideo($video, $video['file_id']);
        
        
      
        if($save_info==false) return $send_mess = methods()->edit_message(DIALTEXT('inputFilesUploadingAFileAnErrorOccurredWhileUploadingTheFileToTheServer'), '', $chat_id, $id_system_mess);
        
        methods()->delete_mess($chat_id, $id_system_mess); 
        

        
        
        $kb = [ [[ "text" => $sendBtnLabel ]] ];
        if($settings['kb'])
            $kb = array_merge($kb, $settings['kb']);

         $keyboard = [
             "keyboard" =>  $kb ,
             "one_time_keyboard" => false,
             "resize_keyboard" => true ];
        
        $settings['files'][] = $save_info;
        
        if(count($photo)){
            if($mdGrpId['number']<2 || $mdGrpId['status']!=1)
             send_mess(["body"=> str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesPhotoAddedSuccessfully') ), 'id_chat'=> $chat_id, "kb"=>$keyboard]);
             if($media_group_id) $par[$media_group_id]['status']=1;
             
            
        }

        if(count($document)){
            if($mdGrpId['number']<2 || $mdGrpId['status']!=1)
            send_mess(["body"=> str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesFileAddedSuccessfully') ), 'id_chat'=> $chat_id, "kb"=>$keyboard]);
            if($media_group_id) $par[$media_group_id]['status']=1;
        }
             
        
        if(count($video)){
            if($mdGrpId['number']<2 || $mdGrpId['status']!=1)
            send_mess(["body"=> str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesVideoAddedSuccessfully') ), 'id_chat'=> $chat_id, "kb"=>$keyboard]);
            if($media_group_id) $par[$media_group_id]['status']=1;
        }
             
        
        
        
        $par['par'] = $settings; 
        $this->sendpos($par['id_script'], $par['id'], $par);
        $this->$PAR = $par;

    }
    
    
    private function input( $par ){
        $text = $par['text'];
        
        $mpar = ['body'=> text()->variables($text, $par), 'files'=> $par['par']['files'] ];
        
        $kbType=$par['par']['answersKeyboard'];
        if($kbType=="") $kbType='answersInlinekeyboard';
        
        
        $kbdef=$this->keyboard($par['keyboard']);
         
        if($kbType == 'answersInlinekeyboard' || $kbType == 'answersKeyboard'){
            $variants = arrayQuery('SELECT answer FROM `script_answers` WHERE id_block = ? AND answer <>""', [$par['id']]);
            if(count($variants>0)){
                $cols = $par['par']['inlineCols']*1;
                if($cols<1) $cols=1;
                if($cols>8) $cols=8;
                
                $kb=[];
                
                if($kbType == 'answersInlinekeyboard'){
                    foreach($variants as $variant)
                        $kb[] = ['text' => $variant['answer'], 'callback_data' => $variant['answer'] ];
                    $kb= array_chunk($kb, $cols);
                    if($kbdef) $kb = array_merge($kbdef['inline_keyboard'],$kb);
                    $mpar['kb']=["inline_keyboard"=>$kb];
                }

                if($kbType == 'answersKeyboard'){
                    foreach($variants as $variant)
                        $kb[] = ['text' => $variant['answer']];
                    $kb= array_chunk($kb, $cols);
                    $mpar['kb']=["keyboard" => $kb, "one_time_keyboard" => false, "resize_keyboard" => true ]; 
                }


            }
        }
        
        
        if(!$mpar['kb']&&$kbdef)$mpar['kb']=$kbdef;

        send_mess($mpar);
        $this->sendpos($par['id_script'], $par['id'], $par);
        $this->$PAR = $par;
    }

    
    private function inputListener( $par ){
        global $message_id, $text_message, $chat_id, $original;
        //methods()->delete_mess($chat_id, $message_id);
        if($cbq=$original['callback_query']??false){
            $txt=$cbq['message']['text']??'';
            $txt .= PHP_EOL.PHP_EOL.'<i>'.$text_message.'</i>';
            methods()->edit_message_text_or_caption($txt, false, $chat_id, $message_id);

        }

        //methods()->delete_this_inline_keyboard();

        $type = $par['par']['type'] ?:'text';
        if($type=='number'){
            $num = str_replace(',', '.', $text_message);
            if(!is_numeric($num))
                return send_mess(['body'=> 'надо прислать число' ]);
            $num = (float) $num;
            $from = $par['par']['from'] ? (float) str_replace(',', '.', $par['par']['from']):0;
            $to = $par['par']['to'] ? (float) str_replace(',', '.', $par['par']['to']):0;
            
            if( ($from!=0 || $to!=0) && ($num<$from || $num>$to) ){
                $from = number_format($from, strlen((string) explode('.', $from)[1]), ',', ' ');
                $to = number_format($to, strlen((string) explode('.', $to)[1]), ',', ' ');
                return send_mess(['body'=> 'Введите число в диапазоне от '.$from.' до '.$to ]);
            }
        }
        
        $id_block = $par['id_block'];
        

        
        
        $par[$par['display']]=$text_message;
        $answers = arrayQuery('SELECT `answer`, `action`, `actionblock` FROM `script_answers` WHERE id_block = :id_block AND `answer` <> ""', [ ':id_block'=> $id_block ]);
        if(!count($answers)){
            if($par['par']['record']==1)
                query('INSERT INTO `script_user_answers` (`id_chat`, `block`, `answer`) VALUES (?,?,?)',[ $chat_id, $id_block, $text_message ]);

            //methods()->delete_this_inline_keyboard();
            $this->distr($par);
            return;
        }
        
        $t='';
        foreach($answers as $key=> $answer){
            $t = $t?
            $t.($key==count($answers)-1?' или ':', ')."«{$answer['answer']}»":
                "«{$answer['answer']}»";
            if(preg_match('/'.$text_message.'/iu', $answer['answer'])){
                
                if($par['par']['record']==1)
                    query('INSERT INTO `script_user_answers` (`id_chat`, `block`, `answer`) VALUES (?,?,?)',[ $chat_id, $id_block, $text_message ]);
                            
                switch($answer['action']){
                    case '':
                        $this->distr( $par );
                    break;
                    
                    case 'exit':
                        $this->sendpos(false);
                    break;
                    default:
                        
                        /**
                         * Если перешли на конкретный шаг в скрипте, то ищем предыдущий шаг и записываем его
                         */
                        methods()->delete_this_inline_keyboard();
                        $actionblock = "";
                        if($answer['actionblock']!==0){
                            $blocks = arrayQuery('SELECT id FROM `script_blocks` WHERE id_script = ? AND activate = 1 ORDER BY num', [ $answer['action'] ]);
                            $blocks = array_map(function($value) {
                                return $value['id'];
                            }, $blocks);
                            $sb = array_search($answer['actionblock'], $blocks);
                            if($sb!==false)
                                $actionblock = $blocks[$sb-1];
                        }
                        
                        $this->sendpos($answer['action'], $actionblock, $par);

                        $par['id_script'] = $answer['action'];
                        $this->distr($par);
                }
                
                return;
            }
        }
        


        $text = $t? "пришлите $t" :"ответ не верный, попробуйте еще раз";
        send_mess(['body'=> $text]);
        
        
    }
    
    
    function substitution_of_values( $text, $par ){
        
        foreach($par as $key=>$r)
            $text = str_replace('{'.$key.'}', $r, $text);
        return $text;
    }
    
    
    public function deleteIdBlock(){
        global $chat_id;
        if($last = singleQuery('SELECT id, id_script, id_block, parameters FROM `script_position` WHERE id_user = :id_user', [ ':id_user'=> $chat_id ])){
            $lastPar=json_decode($last['parameters'], true);
            unset($lastPar['id']);
            unset($lastPar['id_block']);
            query('UPDATE `script_position` SET parameters = ?, id_block = "" WHERE id = ?', [ json_encode($lastPar), $last['id']]);
            
        }
    }
    
    
    private function sendpos($id_script=false, $id_block=false, $parameters=[]){
        global $chat_id;

        query('DELETE FROM `script_position` WHERE id_user = ?', [ $chat_id ]);
        if($id_script)
        query('INSERT INTO `script_position` (`id_user`, `id_script`, `id_block`, `parameters`) VALUES (:id_user, :id_script, :id_block, :parameters)',
             [
                 ':id_user'=> $chat_id
                ,':id_script'=> $id_script
                ,':id_block'=> $id_block
                ,':parameters'=> json_encode($parameters)
             ]);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}