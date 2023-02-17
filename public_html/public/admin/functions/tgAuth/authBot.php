<?php
include('../functions/db_connect.php');
include('../functions/functions.php');
use systems\classes\loadFiles\loadFiles as loadFiles;

$Telegram_botkey = setting('bot_key'); 
define('BOT_TOKEN', $Telegram_botkey); // place bot token of your bot here

function checkTelegramAuthorization($auth_data) {
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);
  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);
  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', BOT_TOKEN, true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);
  if (strcmp($hash, $check_hash) !== 0) {
    throw new Exception('Data is NOT from Telegram');
  }
  if ((time() - $auth_data['auth_date']) > 86400) {
    throw new Exception('Data is outdated');
  }
  return $auth_data;
}

function saveTelegramUserData($auth_data) {
  $auth_data_json = json_encode($auth_data);
  setcookie('tg_user', $auth_data_json);
  $json = json_decode($auth_data_json, true);
  $tgId = $json['id'];
  $search = singleQuery('SELECT * FROM users WHERE id_chat = :id_chat', [':id_chat'=> $tgId]);
  if(!$search){
      $hash = $json['hash'];
      $photo = $json['photo_url'];
      $username = $json['username'];
      $first_name = $json['first_name'];
       
       
       $fileGroup = 0;
       if($photo){
           $fileGroup = loadFiles::getFileGroup();
           insertQuery("INSERT INTO `files` (`id_group`, `small_size`, `medium_size`, `large_size`, `type_file`) VALUES ($fileGroup, '$photo', '$photo', '$photo', 'img');");
       }
       
       $login = $first_name.'_'.randhash(2);
       $password = randhash(3);
       
       
        $ins = insertQuery("INSERT INTO users (`name`, `id_chat`, `status`, `username`, `first_name`, `t_login`, `t_password`, image) VALUES ('$first_name', '$tgId', '1', '$username', '$first_name', '$login', '$password', $fileGroup)");
       
        if(!$ins)
            header('Location: index');
        $kb=[];

        //array_push($kb, [
        //    ['text'=>'ваша ссылка', 'url' => "{$GLOBALS['dir']}/index.php?".urlencode("login=$login&pass=$password")]
        //]);
        //$kb = ["inline_keyboard" =>  $kb]; 
            
            
        send_mess([ 'id_chat' => $tgId, 'body' => "Благодарим за регистрацию!" ]);
        //send_mess([ 'id_chat' => $tgId, 'body' => "\nлогин: $login\nпароль: $password", 'kb'=>$kb ]);
      



        
   

     
  } else {
      $login = $search['t_login'];
      $password = $search['t_password'];
   }
            
            
      
            
        $param = [ 'TFInputLogin'=>$login, 'TFInputPassword'=>$password ];
        $res = post("{$GLOBALS['dir']}/admin/functions/actionlogin.php", $param);
       
        
        $res = json_decode($res, true);
        
        switch($res['success']){
            case 1:
               $hash = $res['myhash'];
               setcookie("myhash", $hash);
               header('Location: '.$GLOBALS['dir']);
            break;
            
    	    default:
    	        
    	       header('Location: '.$GLOBALS['dir']);
        }  
            
           
}


try {
  $auth_data = checkTelegramAuthorization($_GET);
  saveTelegramUserData($auth_data);
} catch (Exception $e) {
  die ($e->getMessage());
}

//header('Location: login_example.php');




















