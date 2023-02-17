<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$params = explode('/', $_GET['q']);
$method = $params[0];

if (!($gethash = permission_to_use())['access'])
    return bterr($gethash['mess']);
$_POST['accessdata']=$gethash;
$class = new cl();

if(!method_exists($class, $method))
    return bterr('метод '.$method.' не найден');

$resp =  $class->$method( $_POST ) ;

echo is_array( $resp ) ? json_encode( $resp ) : $resp;



class cl
{

    public function getList( $POST ){
        if(loadModule('ban')) {
            $res = arrayQuery(
                'SELECT id, chat_id, username, first_name, DATE_FORMAT(`t_date`, "%d.%m.%Y %H:%i") f_date,
                 IF(EXISTS(SELECT * FROM `users_banned` WHERE id_chat=u.chat_id AND date_end>NOW()),1,0) banned
                 FROM `usersAll` u ORDER BY first_name', [], true); 
        } else {
            $res = arrayQuery(
                'SELECT id, chat_id, username, first_name, DATE_FORMAT(`t_date`, "%d.%m.%Y %H:%i") f_date
                 FROM `usersAll` u ORDER BY first_name', [], true);
        }

        
        $ban=loadModule('ban')?1:0;
        $res = array_map(function($it) use($ban){
            $it['ban']=$ban;
            return $it;
        }, $res);
        return [
            'success'=> 'ok'
            ,'data'=> $res
        ];
    }

    public function ban( $POST ){
        if(!$id = $POST['id']) return bterr('не хватает параметров');
        $ban=loadModule('ban');
        $ban->send_ban($id, $POST['term'], $POST['comment'], $POST['notification']);
        return [ 'success'=> 'ok' ];
    }

    public function unban( $POST ){
        if(!$id = $POST['id']) return bterr('не хватает параметров');
        query('DELETE FROM `users_banned` WHERE id_chat=?', [ $id ]);
        return [ 'success'=> 'ok' ];
    }

    public function edit( $POST ){
        if(!$id = $POST['id']) return bterr('не хватает параметров');
        query('UPDATE `usersAll` SET username=?, first_name=? WHERE id=?', [ $POST['username'], $POST['first_name'], $id  ]);
        return [ 'success'=> 'ok' ];
    }

    public function mess( $POST ){

        if(!($id_chat= $POST['id_chat']) || !($text = $POST['text']))
            return bterr('не хватает параметров');

        $sm=send_mess([ 'body'=> $text, 'id_chat'=> $id_chat, 'files'=> $POST['files'] ]);

        if(!$sm[0]['message_id'])
            return bterr('Ошибка при отправке сообщения');

        return [ 'success'=> 'ok' ];
    }


    public function remove( $POST ){

        if(!$id = $POST['id'])
            return bterr('не хватает параметров');

        query('DELETE FROM `usersAll` WHERE id=?', [ $id ]);

        return [ 'success'=> 'ok' ];

    }

    public function add( $POST ){

        $data=singleQuery('SELECT * FROM `usersAll` WHERE id=?',[ insertQuery('INSERT INTO `usersAll` (`username`, `first_name`) VALUES (?,?)', [ $POST['username'],$POST['first_name']]) ]);
        if(!$data)
            return bterr('Ошибка при добавлении данных');

        return [
            'success'=> 'ok'
            ,'data'=> $data
        ];

    }

}

















