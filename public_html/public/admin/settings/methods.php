<?php

class cl
{
    public function save( $inputPar ){

    $key   = $inputPar["key"];
    $value = $inputPar["value"];

    if(!$key)
        return bterr('недостаточно данных');
      
    
    
    $data = [];

    if($key=='bot_key'){
        $dsf = $_SERVER['DOCUMENT_ROOT'].'/SECRETFOLDER/';
        $files = array_diff(scandir($dsf), []);
        
        $num = 0;
        $botfile = '';
        for($i=0;$i<count($files);$i++){
            $file = $files[$i];
            if(strpos(' '.$file, 'tgbot'))
            $botfile = $file;
            
        }



        
        //if(get_headers(_dir_)){
        //    $url = _dir_.'/SECRETFOLDER/'.$botfile;
        //}
        //else
        //{
        //    $resQuery = json_decode(file_get_contents('https://b2bot.ru/webhookredirect/?url='.urlencode(_dir_.'/SECRETFOLDER/'.$botfile) ), true);
        //    if(!$url = $resQuery['url'])
        //        return bterr('ошибка: '.$resQuery['err']);
        //}
        //
        
        
        
        
        $url = 'https://api.telegram.org/bot'.$value.'/setWebhook?url='._dir_.'/SECRETFOLDER/'.$botfile; //.'&drop_pending_updates=1'
 
        $resp = json_decode( curl()->get( $url ), 1);
        
        /**
         * Если вебхук установлен, то заполняем информацию о боте и удаляем старый вебхук
         */
        if( $resp['ok'] ){
            $info = json_decode(curl()->get("https://api.telegram.org/bot$value/getMe"),1);
            
            
            $info2 = json_decode(curl()->get("https://api.telegram.org/bot$value/getWebhookInfo"),1);
            qwe($info2);
            if($info['result']){
                query('DELETE FROM `settings` WHERE t_key IN("username", "bot_id", "first_name")');
                query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("username",?,"",0,"text",0)', [ $info['result']['username'] ]);
                query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("bot_id",?,"",0,"text",0)', [ $info['result']['id'] ]);
                query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("first_name",?,"",0,"text",0)', [ $info['result']['first_name'] ]);
            }
            
            $old=singleQuery('SELECT * FROM `settings` WHERE t_key = "bot_key"');
            if( $old && $old['value'] && $old['value']!==$value ) {
                curl()->get('https://api.telegram.org/bot'.$old['value'].'/deleteWebhook');
            }
        }
        
        $data=$resp;
    }
    
    query('UPDATE settings SET value = ? WHERE t_key = ?', [ $value, $key ]); 

    return [
        'success'=>'ok',
        'data'=> $data
        ];
    }
}

