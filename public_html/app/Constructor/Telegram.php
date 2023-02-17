<?php namespace App\Constructor;

/**
 * Class Telegram
 * @package app\components
 *
 * @property $key string Ключ бота
 * @property $tgHost string АПИ Телеграм
 * @property $dir string Ссылка на свой сайт с папкой
 * @property $chat_id integer CHAT ID
 */
class Telegram {
    protected $key = null;
    protected $tgHost = null;
    protected $dir = null;
    protected $chat_id = null;

    public function __construct($key, $chat_id = null)
    {
        $this->key = $key;
        $this->tgHost = 'https://api.telegram.org/bot' . $this->key;
        $this->dir = 'https://'.request()->getHttpHost();

        $this->chat_id = $chat_id;
    }

    /*
     * -------------------------------------------------------------
     * Отправка сообщений
     * -------------------------------------------------------------
     */

    public function send_mess($par, $chat_id = null)
    {
        if (!$chat_id) $chat_id = $this->chat_id;

        $filehost = 'https://'.request()->getHttpHost();
        $res_arr = [];
        $curlPar = [];
        $host = $this->tgHost;
        $curlPar['chat_id'] = $par['id_chat'] ?: $chat_id;
        $rest=false;

        if (isset($par['reply_to_message_id'])) $curlPar['reply_to_message_id'] = $par['reply_to_message_id'];
        $curlPar['disable_notification']=$par['disable_notification']??false;
        $curlPar['disable_web_page_preview']=$par['disable_web_page_preview']??false;
        $kb=$par['inline_keyboard']??($par['kb']??false);

        # сначала сотавляем структуру сообщения
        $st_text = 0; # текст отсутствует
        $body = '';
        $files = [];

        if ($par['files'] && !is_numeric($par['files'])) $par['files'] = [$par['files']];

        if (isset($par['npm'])) $par['body'] = $par['npm']['body'];
        if(isset($par['body']))$par['body']=trim($par['body']);
        if (is_array($par['files'])) {

            foreach ($par['files'] as $it) {

                if (!pathinfo($it, PATHINFO_EXTENSION)) {
                    $itype = exif_imagetype($it);
                    switch ($itype) {
                        case 2:
                        case 3:

                            $file = 'files/loaded/' . preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '', parse_url($it)['host'] . parse_url($it)['path']) . ($itype == 2 ? '.jpg' : '.png');

                            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file)) {
                                if (!copy($it, $_SERVER['DOCUMENT_ROOT'] . '/' . $file) || !chmod($_SERVER['DOCUMENT_ROOT'] . '/' . $file, 0644)) {
                                    continue 2;
                                }

                            }
                            $it = $file;
                            break;
                        default:
                            continue 2;
                    }

                }

                $files[] = ['id_file' => 0, 'type_file' => 'img', 'small_size' => $it, 'medium_size' => $it, 'large_size' => $it];
            }
        } elseif ($par['files'] != '' && (int)$par['files'] > 0) {
            $files = arrayQuery("SELECT id, id_file, type_file, small_size, medium_size, large_size FROM files WHERE id_group = '{$par['files']}' LIMIT 10");
        }

        $format_text = $par['format_text'];
        if (isset($par['body']) && $par['body'] != '') {
            $curlPar['parse_mode'] = strpos($par['body'],'</')?'html': '';

            $st_text = 1;
            $rar = array_filter(explode('||', $par['body']), function($k) {
                return trim($k);
            });
            $body=$rar[array_rand($rar)];
            if ($format_text) {
                $body = str_replace('<p', "\n<p", $body);
                $body = str_replace('<br>', "\n", $body);
                $body = str_replace('&nbsp;', ' ', $body);
                $body = str_replace('<li', "\n● <li", $body);
                $links = [];
                $link = 'link';
                while ($link !== '') {
                    $link = getStrBetween($body, '<a ', '>');
                    if (!strpos($link, 'style')) break;
                    $href = getStrBetween($link, 'href="', '"');
                    $body = str_replace('<a ' . $link . '>', '[link' . count($links) . ']', $body);
                    array_push($links, $href);
                }
                for ($i = 0; $i < count($links); $i++) {
                    $body = str_replace("[link$i]", '<a href="' . $links[$i] . '">', $body);
                }

                $img = '';
                while ($img !== '') {
                    $img = getStrBetween($body, '<img', '>');
                    if ($img == '') break;
                    $link = getStrBetween($img, 'src="', '"');
                    if (!$link) $link = getStrBetween($img, 'src=\'', '\'');
                    if ($link) {
                        array_push($files, ['id_file' => 0, 'type_file' => 'img', 'small_size' => $link, 'medium_size' => $link, 'large_size' => $link]);
                        $body = str_replace('<img' . $img . '>', ' 🏞[изображение ' . count($files) . ']', $body);
                    }
                }
                $countImgArr = count($files);
                if ($countImgArr) {
                    $countImgArr == 1 ? 1 : 2;
                }

                $body = strip_tags($body, '<i><a><b><u><s><code>');
            }
        }

        switch (count($files)) {
            case 0:
                $st_files = 0;
                break; # файлы отсутствуют
            case 1:
                $st_files = 1;
                break; # один файл
            default:
                $st_files = 2; # несколько файлов
        }


        $maxlen=$st_files !== 0?1000:4095;
        if (mb_strlen($body) > $maxlen){
            $len= mb_strrpos(mb_substr($body, 0,$maxlen), " ")?:$maxlen;
            $rest=['body'=> '...'.mb_substr($body, $len, 100000), 'id_chat'=> $curlPar['chat_id']];
            $rest['disable_notification']=$par['disable_notification'];
            $rest['disable_web_page_preview']=$par['disable_web_page_preview'];

            //  kb reply_markup

            $body = mb_strimwidth($body, 0, $len+3, "...");
        }
        if($rest) {
            if ($kb) $rest['kb'] = $kb;
        } else {
            if ($kb) $curlPar['reply_markup'] = json_encode($kb);
        }

        foreach ($files as $key => $file) {
            $orgfile = $file['small_size'] ?: $file['large_size'];

            if ($file['type_file'] == 'doc') {
                if (!$orgfile) continue;
                $pth=pathinfo($orgfile, PATHINFO_EXTENSION);
                if($pth==='mp3'){
                    $files[$key]['type_file']='audio';
                    continue;
                }

                if (in_array($pth, ['pdf', 'zip', 'gif']) || strpos($orgfile, 'http') !== false) continue;
                $turl = '/files/loaded/' . uniqid() . '.zip';
                zip($_SERVER['DOCUMENT_ROOT'] . '/' . $orgfile, $_SERVER['DOCUMENT_ROOT'] . $turl);
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $turl)) {
                    $files[$key]['small_size'] = $filehost . $turl;
                    $files[$key]['delete'] = $_SERVER['DOCUMENT_ROOT'] . $turl;
                }
            }

            if (pathinfo($orgfile, PATHINFO_EXTENSION) === 'gif') {
                $files[$key]['type_file'] = 'animation';
            }
        }

        $curlPar['text'] = $body;
        if ($st_text == 1 && $st_files == 0) { #если только текст
            $json = $this->post($host . '/sendMessage', $curlPar);
            $json = json_decode($json, true);
            if ($json['error_code'] && $json['description']) notification(if_error_code($json));
            else array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 1));
        }
        if ($st_files == 1) { #если только одна картинка или файл
            $file = $files[0];
            $link = $file['large_size'] ?: ($file['medium_size'] ?: $file['small_size']);
            if(in_array(strtolower(pathinfo($link, PATHINFO_EXTENSION)), ['jpeg', 'jpg']))
                $link = $file['medium_size'] ?: ($file['small_size'] ?: $file['large_size']);

            if (!strpos(' ' . $link, 'http')) $link = $filehost . "/" . $link;
            $id_file_in_telegram = $file['id_file'];
            $curlPar['caption'] = $body ?: '';
            $json = false;

            switch ($file['type_file']) {
                case 'img':
                    if ($id_file_in_telegram) {
                        $curlPar['photo'] = $id_file_in_telegram;
                        $json = curlSend($host . '/sendPhoto', $curlPar);
                        $json = json_decode($json, true);
                    }
                    if (!$json['ok']) {
                        $curlPar['photo'] = $link;
                        $json = curlSend($host . '/sendPhoto', $curlPar);
                        $json = json_decode($json, true);
                        if ($file['id'] && ($tgfid = $json['result']['photo'][0]['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                    }

                    break;
                case 'doc':

                    if ($id_file_in_telegram) {
                        $curlPar['document'] = $id_file_in_telegram;
                        $json = curlSend($host . '/sendDocument', $curlPar);
                        $json = json_decode($json, true);
                    }
                    if (!$json) {
                        $curlPar['document'] = $link;
                        $json = curlSend($host . '/sendDocument', $curlPar);
                        $json = json_decode($json, true);
                        if ($file['id'] && ($tgfid = $json['result']['document']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                    }
                    break;
                case 'audio':

                    if ($id_file_in_telegram) {
                        $curlPar['audio'] = $id_file_in_telegram;
                        $json = curlSend($host . '/sendAudio', $curlPar);
                        $json = json_decode($json, true);
                    }
                    if (!$json) {
                        $curlPar['audio'] = $link;
                        $json = curlSend($host . '/sendAudio', $curlPar);
                        $json = json_decode($json, true);
                        if ($file['id'] && ($tgfid = $json['result']['audio']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                    }
                    break;
                case 'video':
                    if ($id_file_in_telegram) {
                        $curlPar['video'] = $id_file_in_telegram;
                        $json = curlSend($host . '/sendVideo', $curlPar);
                        $json = json_decode($json, true);
                    }
                    if (!$json) {
                        $curlPar['video'] = $link;
                        $json = curlSend($host . '/sendVideo', $curlPar);
                        $json = json_decode($json, true);
                        if ($file['id'] && ($tgfid = $json['result']['video']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                    }

                    break;
                case 'animation':
                    if ($id_file_in_telegram) {
                        $curlPar['animation'] = $id_file_in_telegram;
                        $json = curlSend($host . '/sendAnimation', $curlPar);
                        $json = json_decode($json, true);
                    }
                    if (!$json) {
                        $curlPar['animation'] = $link;
                        $json = curlSend($host . '/sendAnimation', $curlPar);
                        $json = json_decode($json, true);
                        if ($file['id'] && ($tgfid = $json['result']['animation']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                    }
                    break;
            }

            if ($json['error_code'] && $json['description']) notification(if_error_code($json));
            else array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 3));


        }
        if ($st_files == 2) {
            $mediaarr = [];
            foreach ($files as $file) {

                if($file['id_file']){
                    $link=$file['id_file'];
                }else{
                    $link = $file['large_size'] ?: ($file['medium_size'] ?: $file['small_size']);
                    if(in_array(strtolower(pathinfo($link, PATHINFO_EXTENSION)), ['jpeg', 'jpg']))
                        $link = $file['medium_size'] ?: ($file['small_size'] ?: $file['large_size']);
                    if (strpos($link, 'http') === false) $link = $filehost . "/" . $link;
                }


                switch ($file['type_file']) {
                    case 'img':
                        array_push($mediaarr, array('type' => 'photo', 'media' => $link));
                        break;
                    case 'doc':
                        array_push($mediaarr, array('type' => 'document', 'media' => $link));
                        break;
                    case 'video':
                    case 'animation':
                        array_push($mediaarr, array('type' => 'video', 'media' => $link));
                        break;
                }
            }

            if (!$kb) {
                $mediaarr[0]['caption'] = $curlPar['text'];
                $mediaarr[0]['parse_mode'] = $curlPar['parse_mode']??'';
            }

            $temp = $curlPar;
            $temp['media'] = json_encode($mediaarr);
            $json_media = curlSend($host . '/sendMediaGroup', $temp);
            # записываем медиа после текстового файла
            $json_media = json_decode($json_media, true);
            if ($json_media['error_code'] && $json_media['description']) notification($json_media['description']);
            foreach ($json_media['result'] as $r_media) {
                array_push($res_arr, array('message_id' => $r_media['message_id'], 'channel_id' => $r_media['chat']['id'], 'channel_name' => $r_media['chat']['username'], 'type' => 5));
                $rplMsg = $r_media['message_id'];
            }

            if ($kb) {

                $json = curlSend($host . '/sendMessage', $curlPar);
                $json = json_decode($json, true);
                array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 1));
            }


        }

        if($rest) $res_arr=array_merge($res_arr,send_mess($rest));

        foreach ($files as $file)
            if ($file['delete']) unlink($file['delete']);

        return $res_arr;
    }

    public function tgMess($text, $chat_id = null)
    {
        if (!$chat_id) $chat_id = $this->chat_id;
        return $this->post($this->tgHost . '/sendMessage', ['text' => $text, 'chat_id' => $chat_id, 'parse_mode' => strpos($text,'</')?'html': '']);
    }

    /*
     * -------------------------------------------------------------
     * Медоты телеграм
     * -------------------------------------------------------------
     */
    public function getUserProfilePhotos($chat_id){
        return $this->post($this->tgHost.'/getUserProfilePhotos', ['user_id'=>$chat_id]);
    }

    public function sendLocation($heading, $longitude, $latitude, $keyboard, $chat_id){
        $par = ['chat_id'=>$chat_id, 'longitude'=>$longitude, 'latitude'=>$latitude, 'heading' => $heading];
        if($keyboard) $par['reply_markup'] = json_encode($keyboard);
        return $this->post($this->tgHost.'/sendLocation', $par);
    }

    public function answerInlineQuery($par){
        return $this->post($this->tgHost.'/answerInlineQuery', $par);
    }

    public function approveChatJoinRequest($chat_id, $user_id){
        $res= $this->post($this->tgHost.'/approveChatJoinRequest', ['chat_id'=>$chat_id, 'user_id'=>$user_id]);
        return json_decode($res,1);
    }

    public function sendDice($chat_id, $emoji){
        return $this->post($this->tgHost.'/sendDice', [ 'chat_id'=>$chat_id, 'emoji'=> $emoji ]);
    }

    public function sendPoll($obj){
        return json_decode($this->post($this->tgHost.'/sendPoll', $obj), 1);
    }

    public function exportChatInviteLink($chat_id){
        return $this->post($this->tgHost.'/exportChatInviteLink', ['chat_id'=>$chat_id]);
    }

    public function sendAudio($par=[/* chat_id, audio*/]){
        return json_decode(
            $this->post($this->tgHost.'/sendAudio', $par)
            , true);
    }

    public function sendVoice($par=[/* chat_id, voice*/]){
        return json_decode(
            $this->post($this->tgHost.'/sendVoice', $par)
            , true);
    }

    public function sendPhoto($chat_id, $photo){
        return $this->post($this->tgHost.'/sendPhoto', ['chat_id'=> $chat_id, 'photo'=> $photo]);
    }

    public function sendDocument($chat_id, $doc, $caption=''){
        return $this->post($this->tgHost.'/sendDocument', ['chat_id'=> $chat_id, 'document'=> $doc, 'caption'=> $caption]);
    }

    public function getChatAdministrators($chat_id){
        return $this->post($this->tgHost.'/getChatAdministrators', ['chat_id'=>$chat_id]);
    }

    public function getMe(){
        return $this->post($this->tgHost.'/getMe', []);
    }

    public function sendChatAction($action, $chat_id){
        return $this->post($this->tgHost.'/sendChatAction', ['chat_id'=>$chat_id, 'action'=>$action]);
    }

    public function getChat($chat_id){
        return $this->post($this->tgHost.'/getChat', [ 'chat_id'=>$chat_id ]);
    }


    public function unbanChatMember($chat_id, $user_id){
        return json_decode(
            $this->post($this->tgHost.'/unbanChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function banChatMember($chat_id, $user_id){
        return json_decode(
            $this->post($this->tgHost.'/banChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function getChatMember($chat_id, $user_id){
        return json_decode(
            $this->post($this->tgHost.'/getChatMember', [ 'chat_id'=> $chat_id, 'user_id'=> $user_id ])
            , true);
    }

    public function setMyCommands($commands){
        $commands = json_encode($commands);
        return json_decode(
            $this->post($this->tgHost.'/setMyCommands', [ 'commands'=> $commands ])
            , true);
    }

    public function editMessageMedia($chat_id, $id_message, $link, $caption='',$kb=false){
        $type= 'photo';
        if($link&&is_numeric($link)){
            $file=singleQuery('SELECT * FROM `files` WHERE id_group=?',[$link]);
            $link = $file['large_size'] ? : ($file['medium_size'] ?: $file['small_size']);
            if (strpos( $link, 'http')===false) $link = str_replace('https:', 'http:', $this->dir)."/".$link;
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
            $this->post($this->tgHost.'/editMessageMedia', $par)
            , true);
    }

    public function pinned_mess($chat_id, $id_message){
        return $this->post($this->tgHost.'/pinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }

    public function unpinned_mess($chat_id, $id_message){
        return $this->post($this->tgHost.'/unpinChatMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }


    public function delete_mess($chat_id, $id_message){
        return $this->post($this->tgHost.'/deleteMessage', ['chat_id'=>$chat_id, 'message_id'=>$id_message]);
    }



    public function editKb($par=[ /*'inline_message_id'=>, 'kb'=> */ ]){
        global $original;
        $p=['reply_markup'=>json_encode($par['kb'])];
        $id=$par['inline_message_id']??($original['callback_query']['inline_message_id']??'');
        if($id){
            $p['inline_message_id']=$id;
        } else {
            $p['message_id']=$par['message_id']??($original['callback_query']['message']['message_id']??'');
            $p['chat_id']=$par['chat_id']??($original['callback_query']['message']['chat']['id']??'');
        }
        return json_decode($this->post($this->tgHost.'/editMessageReplyMarkup', $p), 1);
    }

    public function edit_inline_keyboard($chat_id, $message_id, $inline_keyboard){
        return json_decode($this->post($this->tgHost.'/editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>json_encode($inline_keyboard)]), 1);
    }

    public function edit_message($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return $this->post($this->tgHost.'/editMessageText', $p);


    }

    public function edit_message_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);

        return $this->post($this->tgHost.'/editMessageCaption', $p);
    }



    public function editMsg( $par=[ /*'text'=> , 'inline_message_id'=>, 'kb'=> */ ] ){
        global $original;
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

        $res = json_decode($this->post($this->tgHost.'/editMessageText', $p),1);
        if(!$res['ok'])
            $res = json_decode($this->post($this->tgHost.'/editMessageCaption', $p),1);
        return $res;
    }


    public function edit_message_text_or_caption($text, $keyboard, $chat_id, $message_id, $par=[]){
        $p = ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'caption'=>$text, 'text'=>$text ];
        $p['parse_mode'] = strpos($text,'</')?'html': '';
        if($par['disable_web_page_preview'])
            $p['disable_web_page_preview']='true';

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        $res = json_decode($this->post($this->tgHost.'/editMessageText', $p),1);
        if(!$res['ok'])
            $res = json_decode($this->post($this->tgHost.'/editMessageCaption', $p),1);
        return $res;
    }

    public function forward_message($from_chat_id, $message_id, $chat_id){
        return $this->post($this->tgHost.'/forwardMessage', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id]);
    }

    public function answerCallbackQuery($text, $id_callback_query, $show_alert = 1){
        return $this->post($this->tgHost.'/answerCallbackQuery', ['callback_query_id'=>$id_callback_query, 'text'=>$text, 'show_alert'=>$show_alert, 'cache_time'=>'0']);
    }

    public function delete_this_inline_keyboard($message_id, $chat_id){ # удалить инлайн клавиатуру у текущего сообщения
        $kb=json_encode(["inline_keyboard"=>[[]]]);
        return $this->post($this->tgHost.'/editMessageReplyMarkup', ['chat_id'=>$chat_id, 'message_id'=>$message_id, 'reply_markup'=>$kb]);
    }

    public function copyMessage($from_chat_id, $message_id, $chat_id, $keyboard = false, $par = []){
        $p = array_merge(['chat_id'=>$chat_id, 'message_id'=>$message_id, 'from_chat_id'=>$from_chat_id], $par);

        if($keyboard)
            $p['reply_markup']=json_encode($keyboard);
        return json_decode( $this->post($this->tgHost.'/copyMessage', $p), true);
    }

    public function error($arr){
        if($arr['error_code'])
            notification($arr['description'].PHP_EOL.'error_code: '.$arr['error_code']);
    }

    /*
     * -------------------------------------------------
     * Функции для работы с файлами
     * -------------------------------------------------
     */

    function deleteFilegroup($group_id) {
        $files = arrayQuery('SELECT small_size, medium_size, large_size FROM `files` WHERE id_group = ?',[$group_id]);
        foreach($files as $file){
            if($file['small_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['small_size'] );
            if($file['medium_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['medium_size'] );
            if($file['large_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['large_size'] );
        }
        query('DELETE FROM `files` WHERE id_group = ?',[$group_id]);
    }



    # функция получения местонахождения файла
    function getPhotoPath($file_id) {
        $getFile = $this->post($this->tgHost.'/getFile', ['file_id' => $file_id]);
        $res = json_decode($getFile,true);

        if(pathinfo($res['result']['file_path'], PATHINFO_EXTENSION)=='htaccess') {
            $this->tgMess('Предупреждение! Вы попытались загрузить файл .htaccess, администратору приложения отправлено уведомление');
            return false;
        }
        return  $res['result']['file_path'];
    }

    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    public function getFileFromId($file_id) {
        $name = uniqid();
        $file = $this->copyFile($this->getPhotoPath($file_id), $_SERVER['DOCUMENT_ROOT'].'/files/downloads', $name);

        $fileDir = $_SERVER['DOCUMENT_ROOT'].'/files/downloads/'.$file;

        return [
            'dir'=> $fileDir
            ,'link'=> $this->dir.'/files/downloads/'.$file
        ];

    }
# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    # копируем документ в папку
    public function copyFile($file_path, $save_dir, $file_name) {

        # ссылка на файл в телеграме
        $file_from_tgrm = 'https://api.telegram.org/file/bot' . $this->key."/".$file_path;

        # достаем расширение файла
        $ext =  end(explode(".", $file_path));
        $link = "$save_dir/$file_name.$ext";
        # назначаем свое имя здесь $file_name.расширение_файла
        if(copy($file_from_tgrm, $link))
            return "$file_name.$ext";
        else
            return '';
    }

    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


    # сохраняем файл, делаем запись в базе
    public function saveVideo($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $video = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$video)
            return false;

        $video='files/documents/'.$video;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "video", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $video]);

        return [ 'file'=> $video ,'id_file'=> $insertId ];
    }


    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


    # сохраняем файл, делаем запись в базе
    public function saveDocument($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $doc = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$doc)
            return false;

        $doc='files/documents/'.$doc;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "doc", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $doc]);

        return [ 'file'=> $doc ,'id_file'=> $insertId ];
    }
    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    public function getPhotoFromId($file_id) {
        $name = uniqid();
        $photo = $this->copyFile($this->getPhotoPath($file_id), $_SERVER['DOCUMENT_ROOT'].'/files/images', $name);


        $photoDir = $_SERVER['DOCUMENT_ROOT'].'/files/images/'.$photo;

        return [
            'dir'=> $photoDir
            ,'link'=> $this->dir.'/files/images/'.$photo
            ,'shortlink'=> 'files/images/'.$photo
        ];
    }
    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    # сохраняем фото, делаем запись в базе
    public function savePhoto($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла

        $small = $this->copyFile($this->getPhotoPath($data[0]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'small_'.$name);
        $medium = $this->copyFile($this->getPhotoPath($data[1]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'medium_'.$name);
        if($data[2])
            $large = $this->copyFile($this->getPhotoPath($data[2]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'large_'.$name);
        else
            $large = $medium;

        if(!$small && !$medium && !$large)
            return false;

        if(!$medium) $medium = $large?$large:$small;
        if(!$large) $large = $medium?$medium:$small;
        $small='files/images/'.$small;
        $medium='files/images/'.$medium;
        $large='files/images/'.$large;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "img", :small_size, :medium_size, :large_size, -1)', [ ':id_file'=> $id_file, ':small_size'=> $small, ':medium_size'=> $medium, ':large_size'=> $large ]);

        return [ 'file'=> $medium ,'id_file'=> $insertId ];
    }


    public function getFileGroup() {
        return singleQuery("SELECT IFNULL(max(id_group),0)+1 result FROM `files`")['result'];
    }

    /**
     * Получить массив файлов для веба
     */
    public function getFilesforweb($id_group) {
        if(!$id_group||$id_group==="0"||$id_group==="false") return [];

        if($id_group=='all'){
            $arr=arrayQuery('SELECT * FROM `files` ORDER BY id DESC');
        } else {
            $arr=arrayQuery('SELECT * FROM `files` WHERE id_group = ?', [ $id_group ]);
        }

        return array_map(function($it) {
            $preview = $it['small_size']?:($it['medium_size']?:$it['large_size']);
            $file =    $it['large_size']?:($it['medium_size']?:$it['small_size']);
            $ext=strtolower(pathinfo($file, PATHINFO_EXTENSION));
            switch($ext){
                case 'mov':
                    $it['type_file'] = 'doc';
                    break;
            }
            return [
                'id_group'=> $it['id_group']
                ,'preview'=> strpos($preview, 'http')===false? '/'.$preview : $preview
                ,'file'=> strpos($file, 'http')===false? '/'.$file : $file
                ,'fileid'=> $it['id']
                ,'type'=> $it['type_file']
                ,'ext'=> $ext
                ,'lg'=> $it['large_size']
                ,'md'=> $it['medium_size']
                ,'sm'=> $it['small_size']
            ];
        }, $arr);
    }

    /*
     * -------------------------------------------------
     * Сетевые функции
     * -------------------------------------------------
     */

    /**
     * @param $url
     * @param array $par
     * @param array $add
     * @return mixed
     */
    public function post($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POSTFIELDS => is_array($par)?http_build_query($par):$par,
                //CURLOPT_INTERFACE => '62.217.176.144'
            ]
        );

        if($add&&isset($add['header'])){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }

        $res = curl_exec($curl);
        curl_reset($curl);
        return $res;
    }

    /**
     * @param $url
     * @param array $par
     * @param array $add
     * @return mixed
     */
    function get($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        $query = '';

        if(count($par))
            $query = '?'.http_build_query($par);

        $ch = curl_init($url.$query);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER => false,
                CURLOPT_INTERFACE => '62.217.176.144'
            ]
        );

        if($add&&isset($add['header'])){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * @param $url
     * @param array $par
     * @param array $add
     * @return mixed
     */
    public function put($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_INTERFACE, '62.217.176.144');
        if($add['header']){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        $res = curl_exec($ch);
        curl_reset($ch);
        return $res;
    }
}
