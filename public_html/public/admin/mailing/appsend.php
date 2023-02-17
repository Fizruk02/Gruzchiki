<?
use telegram\methods\methods as methods;
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
echo sendMess($_POST);

function sendMess( $param ){
$data = json_decode($param['d'], true);
$param['poll_data'] = json_decode($param['poll_data'], true);
$text = $param['t'];
$date = strtotime($param['date'])?:time();
$dateFormat = date("Y-m-d H:i:s", $date); 
$dismiss = $param['ds'];
$plcol = (int) $param['plcol'];
$filegroup = $param['filegroup']?:0;
if($plcol<1) $plcol=1;
if($plcol>8) $plcol=8;
if($poll_system && (is_array($poll_data)&&count($poll_data)==1))
    return response_if_error('Системный опрос возможен при наличии 2-х вариантов ответа и более');

$inline = json_decode($param['inline'],1);
//1970-01-01 03:00:00
$send=$date<=time();

$mailingId = insertQuery('INSERT INTO `mailings` (`name`, `t_date`, `type_mailing`, `date_begin`,  `status`, `body`, `files`, `repeat`) VALUES ("Рассылка", NOW(), "message", ?, ?, ?, ?, ? )', [ $dateFormat, $send?2:0, $text, $filegroup, $param['repeat'] ]);

if(!$mailingId)
    return response_if_error('Ошибка создания рассылки');
    
if(isset($param['poll_data'])&&is_array($param['poll_data'])&&count($param['poll_data']) && $mailingId){

    
    $param['mailing_id']=$mailingId;
    $kb=array_map(function($i) use($param) {
        $aid = insertQuery('INSERT INTO `mailing_voting_variants` (`id_mailing`, `variant`) VALUES (?,?)', [ $param['mailing_id'], $i ]);
        return  ['text' => $i, 'callback_data' => json_encode(['mtd'=> 'mlngCntr', 'n'=> $aid, 'tp'=> 2, 'mid'=> $param['mailing_id'], 'ds'=> $param['ds']])];
    }, $param['poll_data']);

    $kb = array_chunk($kb, $param['plcol']?:1);
    
    
}



foreach($inline as $key)
    $kb[] =  [['text' => $key[0], strpos($key[1], '//')?'url':'callback_data' => $key[1]]];


if($kb){
    $kb=["inline_keyboard"=>$kb];
    
    query('UPDATE `mailings` SET `keyboard` = ? WHERE id = ?', [ json_encode($kb), $mailingId ]);
}

        


foreach($data as $d){


    $r=insertQuery('INSERT INTO `mailing_address` (`source`, `status`, `id_mailing`, `id_chat`) VALUES ("tg", 0, ?, ?)', [ $mailingId, $d ]);
    
    
    if($send){
        $sm=send_mess([ 'id_chat'=> $d, 'body'=> $text, 'files'=> $filegroup, 'kb'=> $kb  ]);qwer($sm[0]);
        query('UPDATE `mailing_address` SET status=1,id_message=? WHERE id=?', [ $sm[0]['message_id'],$r ]);
    }
    
    
}

return json_encode(['success'=>'ok']);
}














