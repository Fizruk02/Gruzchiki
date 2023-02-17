<?php

namespace project\modules\yandex_speechkit;

class yandex_speechkit
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['yandex_speechkit'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);

        $method = $settings['method'];
        
        $text = $settings['text'];
        $speed = $settings['speed'];
        $language = $settings['language'];
        $voice = $settings['voice'];
        $emotion = $settings['emotion'];
       
       
       
        $success = true;
        $res = '';
        //if(!$key){
        //    tgMess(DIALTEXT('yandexDictionaryMissingKey'));
        //    $success = false;
        //}
        $key = 'd5c4bb17-77e6-4f54-860b-894d257474f1';

        if($method == 'speech-synthesis'){
            if($success){

                
                $format = 'mp3';
                
                
                $parameters = [
                    
                     'key'=> $key
                    ,'text'=> $text
                    ,'lang'=> $language
                    ,'emotion'=> $emotion
                    ,'speed'=> $speed
                    ,'speaker'=> $voice
                    ,'format'=> $format
                    # ,'quality'=> 'hi'
                    ];
                
                //$res = curl()->get('https://tts.voicetech.yandex.net/generate', $parameters);
                      
                $link = 'https://tts.voicetech.yandex.net/generate?'.http_build_query($parameters);
                $fileName = uniqid().'.'.$format;
                $save_link = $_SERVER['DOCUMENT_ROOT'].'/files/loaded/'.$fileName;
                $url_Link = _dir_.'/files/loaded/'.$fileName;
    
                
                if( copy($link, $save_link) ){
                    
                } else
                    tgMess('Не уалось сгенерировать файл');
                 
                methods()->sendAudio([ 'chat_id'=> $chat_id, 'audio'=> $url_Link ]);
                unlink($save_link);
            }
                    
        }

        if($method == 'speech-recognition'){
            
            qwe($original);
            
            
            
        $name = uniqid(); # часть имени файла
        $voice = $original['message']['voice']['file_id'];
        
        
        
        //if(!count($voice))
        //    return tgMess(DIALTEXT('tesseractOcrNeedToSendAnImage'));
            
            
        $voice = loadFiles()->getFileFromId($voice);
        qwe($voice);
        tgMess($voice['link']);
        
        
        
        
        
        
        
        

$folderId = "b1gvmob95yysaplct532"; # Идентификатор каталога
$audioFileName = $voice['dir'];

$file = fopen($audioFileName, 'rb');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&format=oggopus"); //&folderId=${folderId}
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $key, 'Transfer-Encoding: chunked'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

curl_setopt($ch, CURLOPT_INFILE, $file);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($audioFileName));
$res = curl_exec($ch);
curl_close($ch);
$decodedResponse = json_decode($res, true);
if (isset($decodedResponse["result"])) {
    tgMess( $decodedResponse["result"] );
} else {
    tgMess( "Error code: " . $decodedResponse["error_code"] . "\r\n".
     "Error message: " . $decodedResponse["error_message"] . "\r\n");
}
        
        
        
        
        
        
        
        
        
        if(!$voice['link'] || !$voice['dir'])
            return err(DIALTEXT('Ошибка при загрузке файла'));
        
            
            
        unlink($voice['dir']);    
            qwer($par);
        }


        //$par[$par['script_step']] = "";
       
       
       
        
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }
    
}

















