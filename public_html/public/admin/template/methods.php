<?php

class cl
{
    
    
    public function getList( $POST ){

        $res = arrayQuery('SELECT id, chat_id, username, first_name FROM `usersAll` ORDER BY first_name', [], true);
        if($e=db()->err()) return $e;
        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    
    }

    public function edit( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        
        query('UPDATE `usersAll` SET username=?, first_name=? WHERE id=?', [ $POST['username'], $POST['first_name'], $id  ]);
        if($e=db()->err()) return $e;
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }
    
    public function remove( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        
        query('DELETE FROM `usersAll` WHERE id=?', [ $id ]);
        if($e=db()->err()) return $e;
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }
    
    public function add( $POST ){
        
        $data=singleQuery('SELECT * FROM `usersAll` WHERE id=?',[ insertQuery('INSERT INTO `usersAll` (`username`, `first_name`) VALUES (?,?)', [ $POST['username'],$POST['first_name']]) ]);
        if(!$data)
            return response_if_error('Ошибка при добавлении данных');
        if($e=db()->err()) return $e;
        return json_encode([
              'success'=> 'ok'
             ,'data'=> $data
        ]);
    
    }

}

















