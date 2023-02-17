<?php

namespace telegram\methods;

class methods {

    public function getUserProfilePhotos($chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/getUserProfilePhotos', ['user_id'=>$chat_id]);
    }

    public function sendLocation($heading, $longitude, $latitude, $keyboard, $chat_id){
        global $tgHost;
        $par = ['chat_id'=>$chat_id, 'longitude'=>$longitude, 'latitude'=>$latitude, 'heading' => $heading];
        if($keyboard) $par['reply_markup'] = json_encode($keyboard);
        return curl()->post($tgHost.'/sendLocation', $par);
    }

    public function answerInlineQuery($par){
        global $tgHost;
        return curl()->post($tgHost.'/answerInlineQuery', $par);
    }

    public function approveChatJoinRequest($chat_id, $user_id){
        global $tgHost;
        $res= curl()->post($tgHost.'/approveChatJoinRequest', ['chat_id'=>$chat_id, 'user_id'=>$user_id]);
        return json_decode($res,1);
    }

    public function sendDice($chat_id, $emoji){
        global $tgHost;
        return curl()->post($tgHost.'/sendDice', [ 'chat_id'=>$chat_id, 'emoji'=> $emoji ]);
    }

    public function sendPoll($obj){
        global $tgHost;
        return json_decode(curl()->post($tgHost.'/sendPoll', $obj), 1);
    }

    public function exportChatInviteLink($chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/exportChatInviteLink', ['chat_id'=>$chat_id]);
    }

    public function sendAudio($par=[/* chat_id, audio*/]){
        global $tgHost;
        return json_decode(
            curl()->post($tgHost.'/sendAudio', $par)
            , true);
    }

    public function sendVoice($par=[/* chat_id, voice*/]){
        global $tgHost;
        return json_decode(
            curl()->post($tgHost.'/sendVoice', $par)
            , true);
    }

    public function sendPhoto($chat_id, $photo){
        global $tgHost;
        return curl()->post($tgHost.'/sendPhoto', ['chat_id'=> $chat_id, 'photo'=> $photo]);
    }

    public function sendDocument($chat_id, $doc, $caption=''){
        global $tgHost;
        return curl()->post($tgHost.'/sendDocument', ['chat_id'=> $chat_id, 'document'=> $doc, 'caption'=> $caption]);
    }

    public function getChatAdministrators($chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/getChatAdministrators', ['chat_id'=>$chat_id]);
    }

    public function getMe(){
        global $tgHost;
        return curl()->post($tgHost.'/getMe', []);
    }

    public function sendChatAction($action, $chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/sendChatAction', ['chat_id'=>$chat_id, 'action'=>$action]);
    }

    public function getChat($chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/getChat', [ 'chat_id'=>$chat_id ]);
    }


    public function unbanChatMember($chat_id, $user_id){
        global $tgHost;
        return json_decode(
            curl()->post($tgHost.'/unbanChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function banChatMember($chat_id, $user_id){
        global $tgHost;
        return json_decode(
            curl()->post($tgHost.'/banChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function getChatMember($chat_id, $user_id){
        global $tgHost;
        return json_decode(
            curl()->post($tgHost.'/getChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function setMyCommands($commands){
        global $tgHost;
        $commands = json_encode($commands);
        return json_decode(
            curl()->post($tgHost.'/setMyCommands', [ 'commands'=> $commands ])
            , true);
    }

    public function editMessageMedia($chat_id, $id_message, $link, $caption='',$kb=false){
        global $tgHost;
        $type= 'photo';
        if($link&&is_numeric($link)){
            $file=singleQuery('SELECT * FROM `files` WHERE id_group=?',[$link]);
            $link = $file['large_size'] ? : ($file['medium_size'] ?: $file['small_size']);
            if (strpos( $link, 'http')===false) $link = str_replace('https:', 'http:', _dir_)."/".$link;
            switch ($file['type_file']) {
                case 'img':
                    $type= 'photo';
                    break;
                case 'doc':
                    $type= 'document';
                    break;
                case 'video':
                    $type= 'video';
                    break;
            }
        }
        $media = ['type'=>$type, 'media'=>$link, 'caption'=> $caption];
        $media['parse_mode'] = strpos($caption,'</')?'html': '';
        $par = ['chat_id'=>$chat_id, 'message_id'=>$id_message, 'media'=>json_encode($media)];
        if($kb) $par['reply_markup']=json_encode($kb);
        return json_decode(
            curl()->post($tgHost.'/editMessageMedia', $par)
            , true);
    }

    public function pinned_mess($chat_id, $id_message){
        global $tgHost;
        return curl()->post($tgHost.'/pinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }

    public function unpinned_mess($chat_id, $id_message){
        global $tgHost;
        return curl()->post($tgHost.'/unpinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }


    public function delete_mess($chat_id, $id_message){
        global $tgHost;
        return curl()->post($tgHost.'/deleteMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }



    public function editKb($par=[ /*'inline_message_id'=>, 'kb'=> */ ]){
        global $tgHost,$original;
        $p=['reply_markup'=>json_encode($par['kb'])];
        $id=$par['inline_message_id']??($original['callback_query']['inline_message_id']??'');
        if($id){
            $p['inline_message_id']=$id;
        } else {
            $p['message_id']=$par['message_id']??($original['callback_query']['message']['message_id']??'');
            $p['chat_id']=$par['chat_id']??($original['callback_query']['message']['chat']['id']??'');
        }
        return json_decode(curl()->post($tgHost.'/editMessageReplyMarkup', $p), 1);
    }

    public function edit_inline_keyboard($chat_id, $message_id, $inline_keyboard){
        global $tgHost;
        return json_decode(curl()->post($tgHost.'/editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode($inline_keyboard)]), 1);
    }

    public function edit_message($text, $keyboard, $chat_id, $message_id, $par=[]){
        global $tgHost;
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return curl()->post($tgHost.'/editMessageText', $p);


    }

    public function edit_message_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        global $tgHost;
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return curl()->post($tgHost.'/editMessageCaption', $p);
    }



    public function editMsg( $par=[ /*'text'=> , 'inline_message_id'=>, 'kb'=> */ ] ){
        global $tgHost, $original;
        $text=$par['text']??'';

        $p = ['caption'=>$text, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        $id=$par['inline_message_id']??false;
        if(!$id&&!$par['message_id']) $id=$original['callback_query']['inline_message_id']??false;
        if($id){
            $p['inline_message_id']=$id;
        } else {
            $p['message_id']=$par['message_id']??($original['callback_query']['message']['message_id']??'');
            $p['chat_id']=$par['chat_id']??($original['callback_query']['message']['chat']['id']??'');
        }
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($par['kb'])
            $p['reply_markup']=json_encode($par['kb']);

        $res = json_decode(curl()->post($tgHost.'/editMessageText', $p),1);
        if(!$res['ok'])
            $res = json_decode(curl()->post($tgHost.'/editMessageCaption', $p),1);
        return $res;
    }


    public function edit_message_text_or_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        global $tgHost;
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        $res = json_decode(curl()->post($tgHost.'/editMessageText', $p),1);
        if(!$res['ok'])
            $res = json_decode(curl()->post($tgHost.'/editMessageCaption', $p),1);
        return $res;
    }

    public function forward_message($from_chat_id, $message_id, $chat_id){
        global $tgHost;
        return curl()->post($tgHost.'/forwardMessage', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id]);
    }

    public function answerCallbackQuery($text, $id_callback_query, $show_alert = 1){
        global $tgHost;
        return curl()->post($tgHost.'/answerCallbackQuery', ['callback_query_id'=>$id_callback_query, 'text'=>$text, 'show_alert'=>$show_alert, 'cache_time'=>'0']);
    }

    public function delete_this_inline_keyboard(){ # удалить инлайн клавиатуру у текущего сообщения
        global $tgHost, $message_id, $chat_id;
        $kb=json_encode(["inline_keyboard"=>[[]]]);
        return curl()->post($tgHost.'/editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>$kb]);
    }

    public function copyMessage($from_chat_id, $message_id, $chat_id, $keyboard = false, $par = []){
        global $tgHost;
        $p = array_merge(['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id], $par);

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        return json_decode( curl()->post($tgHost.'/copyMessage', $p), true);
    }

    public function error($arr){
        if($arr['error_code'])
            notification($arr['description'].PHP_EOL.'error_code: '.$arr['error_code']);
    }
}