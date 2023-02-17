<?

class settings
{
    public function save( $inputPar ){

    $key   = $inputPar["key"];
    $value = $inputPar["value"];

    if(!$key)
        return response_if_error('недостаточно данных');
      
    query('UPDATE settings SET value = :val WHERE t_key = :key', [ ':val'=> $value, ':key'=> $key ]); 
    
    $res = ['success'=>'ok'];

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
        deleteQuery('DELETE FROM settings WHERE t_key = "username"');
        $info = json_decode(file_get_contents("https://api.telegram.org/bot$value/getMe"));
        if($info-> result && $username = $info-> result-> username)
                query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("username",?,"",0,"text",0)', [ $username ]);
        
        if(get_headers(_dir_)){
            $url = _dir_.'/SECRETFOLDER/'.$botfile;
        }
        else
        {
            $resQuery = json_decode(file_get_contents('https://b2bot.ru/webhookredirect/?url='.urlencode(_dir_.'/SECRETFOLDER/'.$botfile) ), true);
            if(!$url = $resQuery['url'])
                return response_if_error('ошибка: '.$resQuery['err']);
        }
        
        $res['url'] = 'https://api.telegram.org/bot'.$value.'/setWebhook?url='.$url;
        
        
    }

    return json_encode($res);
    }
}

