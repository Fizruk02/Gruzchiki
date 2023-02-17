<?php

namespace systems\classes\keyboards;

class keyboards
{

    /**
     * @param $keys
     * @param int $imit
     * @return array
     * РАЗБИТЬ МАССИВ ПО КОЛИЧЕСТВУ СИМВОЛОВ КЛЮЧА "TEXT"
     */
    public function array_strlen_chunk( $keys, $imit=20 ){
        $sp=[]; $l=[]; $s=0; $len='';
        foreach ($keys as $k=>$tr){
            $s+=($len=mb_strlen($tr['text']));

            if($s>$imit||count($l)===8) {
                $sp[]=$l;
                $l=[]; $s=$len;
            }
            $l[]=$tr;
            if($k===(count($keys)-1)) $sp[]=$l;
        }
        return $sp;
    }


    /**
     * @param array $par
     * @return mixed
     * ИЗМЕНИТЬ НАЖАТУЮ КЛАВИШУ
     */
    public function changeThePressedKey( $par=[] ){
        /**
         *
        # изменить текст и callback_data передачей параметров
        keyboards()->changeThePressedKey([
        'text'=>
        ,'callback_data'=>
        ,'edit'=> true
        ,'type'=> callback_data по умолчанию
        ]);

        # изменить текст и callback_data через маску. В маску передадутся text и callback_data изменяемых параметров и метод должен их вернуть
        keyboards()->changeThePressedKey([
        'class'=> get_class()
        ,'mask'=> 'mask'
        ,'edit'=> true
        ]);

        # пример метода
        public function mask($par){
        $text = $par['text'];
        $callback_data = $par['callback_data'];
        return
        [
        'text' => $text
        ,'callback_data'=> $callback_data
        ];
        }
         */
        global $original, $chat_id, $message_id;
        $newText = $par['text'];

        $type = $par['type'] ? $par['type']:'callback_data';
        $edit_inline_keyboard = isset($par['edit']) ? $par['edit'] : true;


        $body = $original['callback_query'];
        $data = $body['data'];
        $newCallbackData = $par['callback_data']?$par['callback_data']:$data;
        $reply_markup = $body['message']['reply_markup'];
        foreach($reply_markup['inline_keyboard'] as &$rows){
            foreach($rows as &$keys){
                if($keys['callback_data']==$data){
                    if(isset($par['class']) && isset($par['mask'])){
                        $method = $par['mask'];
                        $className = end(explode("\\", $par['class']));
                        $class = loadModule($className);

                        if(method_exists($class, $method)){

                            $resp = $class->$method([
                                'text'=> $keys['text']
                                ,'callback_data'=> $keys['callback_data']
                            ]);

                            $keys['text']=$resp['text'];
                            $keys['callback_data']=$resp['callback_data'];
                        } else {
                            err('error: changeThePressedKey');
                        }

                    } else {
                        $keys['text']=$newText;
                        $keys[$type]=$newCallbackData;
                    }

                    break 2;
                }
            }

        }

        if( $edit_inline_keyboard )
            methods()->edit_inline_keyboard($chat_id, $message_id, $reply_markup);
        return $reply_markup;


    }


    /**
     * УДАЛИТЬ НАЖАТУЮ КЛАВИШУ
     */
    public function deletePressedKey( $par=[] ){
        global $original, $chat_id, $message_id;

        $body = $original['callback_query'];
        $data = $body['data'];
        $reply_markup = $body['message']['reply_markup'];
        foreach($reply_markup['inline_keyboard'] as &$rows){
            foreach($rows as $key=> &$keys){
                if($keys['callback_data']==$data){
                    unset($rows[$key]);

                    break 2;
                }
            }

        }
        methods()->edit_inline_keyboard($chat_id, $message_id, $reply_markup);
    }

    function textPressedInlinekey(){
        global $original, $chat_id, $message_id;

        $body = $original['callback_query'];
        $data = $body['data'];
        $reply_markup = $body['message']['reply_markup'];
        foreach($reply_markup['inline_keyboard'] as &$rows){
            foreach($rows as $key=> &$keys){
                if($keys['callback_data']==$data) return $keys;

            }

        }
    }

    /**
     * УДАЛИТЬ ПУСТЫЕ INLINE КЛАВИШИ
     */
    public function deleteEmptyInlkeys( $tkb=[] ){
        $kb = isset($tkb['inline_keyboard'])? $tkb['inline_keyboard']:$tkb;
        foreach($kb as $mk=>$kbr)
            foreach($kbr as $k=>$r){
                $b=false;
                foreach($r as $it) if($it=="")$b=true;
                if($b) unset($kb[$mk][$k]);
            }
        return isset($tkb['inline_keyboard'])?['inline_keyboard'=> $kb]:$kb;
    }

    public function getCurrentkeyboard( $par=[] ){
        global $obj;
        return isset( $obj['message']['reply_markup'] ) ? $obj['message']['reply_markup'] : false;
    }

    public function inlineKeyGoBack($text_edit=true){
        global $message_id, $chat_id, $obj, $original;

        $confirmation_message = false;
        foreach ($original['callback_query']['message']['reply_markup']['inline_keyboard'] as $trow) foreach ($trow as $t) {
            if ($t['callback_data'] == 'confirmation_message') $confirmation_message = true;
        }

        if(!$confirmation_message)
            query('INSERT INTO `s_data_before_the_update` (`id_mess`, `id_chat`, `body`) VALUES (:id_mess, :id_chat, :body)',
                [':id_mess'=> $message_id, ':id_chat'=> $chat_id, ':body'=> json_encode($original)]);

        else {
            $row = singleQuery('SELECT id, body FROM s_data_before_the_update WHERE id_mess = :id_mess AND id_chat = :id_chat ORDER BY id DESC', [ ':id_mess'=> $message_id, ':id_chat'=> $chat_id]);
            query("DELETE FROM s_data_before_the_update WHERE id = :id", [ ':id'=> $row['id'] ]);
        }
        return [
            ["text"=>"« Назад","callback_data"=> json_encode([ 'system'=> 'return_the_keyboard','editText'=> $text_edit ])]
        ];
    }

    public function return_the_keyboard_text_no_edit(){
        $this-> return_the_keyboard(['editText'=> false]);
    }

    public function return_the_keyboard( $par=['editText'=>true] ){
        global $obj, $message_id, $chat_id;

        $row = singleQuery('SELECT id, body FROM s_data_before_the_update WHERE id_mess = :id_mess AND id_chat = :id_chat ORDER BY id DESC', [ ':id_mess'=> $message_id, ':id_chat'=> $chat_id]);
        if ($row) {
            $body = json_decode(urldecode($row['body']), true);
            if (isset($body['callback_query']['message'])) $origmess = $body['callback_query']['message'];
            if (!$origmess && isset($body['message'])) $origmess = $body['message'];
            $rm = $origmess['reply_markup'] ? $origmess['reply_markup'] : false;
            $text = $origmess['text'];


            if($par['editText'] && $text && !is_array(json_decode($text, true))){
                methods()->error(methods()->edit_message_text_or_caption($text, $rm, $chat_id, $message_id));
            } else {
                methods()->edit_inline_keyboard($chat_id, $message_id, $rm);
            }
            query("DELETE FROM s_data_before_the_update WHERE id = :id", [ ':id'=> $row['id'] ]);
            if($rm)
                $obj['message']['reply_markup'] = $rm;
        }
    }


    public function getKeyboard( $techname ){
        if(!$techname)
            return '';
        include_once ($_SERVER['DOCUMENT_ROOT'] . "/SECRETFOLDER/keyboards/{$techname}.php");
        $type = singleQuery('SELECT t_type FROM `s_keyboards` WHERE techname = :techname', [ ':techname'=> $techname ])['t_type'];
        $kb = $techname();
        return $kb;
    }

    public function getInlineKeyboardFromSystemMess( array $par=[] ){

        $customKb = [];
        $kb_name = $par['script_messages'][0]['kb_name'];
        if($kb_name){
            include_once($_SERVER['DOCUMENT_ROOT']."/SECRETFOLDER/keyboards/$kb_name.php");

            if(function_exists($kb_name)){
                $kb= $kb_name();

                foreach($kb['inline_keyboard'] as $r1){

                    foreach($r1 as $key=> &$r2)
                        foreach($r2 as &$r3){
                            $r3 = text()->variables($r3, $par);
                            $r3 = text()->shortcodes($r3, $par);
                        }

                    array_push($customKb, $r1);
                }

            }
        }

        return count($customKb)?$customKb:false;
    }

    public function getKeyboardFromSystemMess( array $par=[] ){

        $customKb = [];
        $kb_name = $par['script_messages'][0]['kb_name'];
        if($kb_name){
            include_once($_SERVER['DOCUMENT_ROOT']."/SECRETFOLDER/keyboards/$kb_name.php");

            if(function_exists($kb_name)){
                $kb= $kb_name();

                foreach($kb['keyboard'] as $r1){

                    foreach($r1 as $key=> &$r2)
                        foreach($r2 as &$r3){
                            $r3 = text()->variables($r3, $par);
                            $r3 = text()->shortcodes($r3, $par);
                        }
                    array_push($customKb, $r1);
                }

            }
        }

        return count($customKb)?$customKb:false;
    }



}