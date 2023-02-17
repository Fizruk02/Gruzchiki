<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$params = explode('/', $_GET['q']);
$method = $params[0];

if (!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']);
$_POST['accessdata']=$gethash;
$class = new cl();

if(!method_exists($class, $method))
    return response_if_error('метод '.$method.' не найден');

$resp =  $class->$method( $_POST ) ;

echo is_array( $resp ) ? json_encode( $resp ) : $resp;



class cl
{
    
    
    public function getList( $POST ){

        query("ALTER TABLE `users` ADD `role_id` INT(10) NOT NULL DEFAULT '0';");

        $res = arrayQuery('SELECT u.id, u.id_chat, u.name, u.`role_id`, u.username, u.first_name, u.t_login login, u.password pass
                           FROM `users` u
                           JOIN `us_roles` r ON r.role_id=u.role_id
                           WHERE (IFNULL(u.id_chat,"")<>"" OR u.name="admin") AND IFNULL(u.id_chat,"")<>"dev"
                           ORDER BY u.`role_id` DESC, u.first_name;', [], true);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);

        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    
    }
    
    public function getRolesList( $POST ){

        $res = arrayQuery('SELECT* FROM `us_roles` ORDER BY name', [], true);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);

        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    }
    
    public function addRole( $POST ){
        if(!$name = $POST['name']) return response_if_error('Не указано имя');
        $role_id = $POST['role_id']*1;
        if($role_id===99) return response_if_error('id 99 зарезервирован, выберите другой id');
        if(singleQuery('SELECT * FROM `us_roles` WHERE role_id=?',[ $role_id ])) return response_if_error('Роль с id «'.$role_id.'» уже есть в базе');
        if(singleQuery('SELECT * FROM `us_roles` WHERE title=?',[ $name ])) return response_if_error('Роль «'.$name.'» уже есть в базе');
        
        $id=query('INSERT INTO `us_roles` (`name`, `title`, `role_id`, `parent`) VALUES (?,?,?,?)', [ $POST['slug'],$name,$role_id,0]);
        $data=singleQuery('SELECT * FROM `us_roles` WHERE id=?',[ $id ]);
        if(!$data) return response_if_error('Ошибка при добавлении данных');
        return [
              'success'=> 'ok'
             ,'data'=> $data
             ,'roles'=> $this-> formatRoles()
        ];
    }
    
    public function editRole( $POST ){
        
        if(!$id = $POST['id']) return response_if_error('Не передан id');
        if(!$name = $POST['name']) return response_if_error('Не указано имя');
        $role_id = $POST['role_id']*1;
        if($role_id===99) return response_if_error('id 99 зарезервирован, выберите другой id');
        if($x=singleQuery('SELECT * FROM `us_roles` WHERE role_id=? AND id<>?',[ $role_id,$id ])) return response_if_error('Роль с id «'.$role_id.'» уже есть в базе');
        if($y=singleQuery('SELECT * FROM `us_roles` WHERE title=? AND id<>?',[ $name,$id ])) return response_if_error('Роль «'.$name.'» уже есть в базе');
        
        query('UPDATE `us_roles` SET `name`=?, `title`=?, `role_id`=?, `parent`=? WHERE `id` = ?', [ $POST['slug'],$name,$role_id,0,$id]);

        return [
              'success'=> 'ok'
             ,'roles'=> $this-> formatRoles()
        ];
    }
    
    public function removeRole( $POST ){
        if(!$id = $POST['id']) return response_if_error('не хватает параметров');
        query('DELETE FROM `us_roles` WHERE id=?', [ $id ]);
        return [
              'success'=> 'ok'
             ,'roles'=> $this-> formatRoles()
        ];
    }
    
    
    private function formatRoles(){
        return arrayQuery('SELECT title as text, role_id as `value` FROM `us_roles` ORDER BY name');
    }
    
    public function edit( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        if($POST['login']!==''&&singleQuery('SELECT * FROM users WHERE t_login=? AND id<>?',[ $POST['login'],$id ])) return response_if_error('Этот логин занят');
        
        query('UPDATE `users` SET name=?, t_login=?, status=?, role_id=? WHERE id=?',
                [ $POST['name'], $POST['login'], $POST['role_id'], $POST['role_id'], $id  ]);
        if($POST['pass']!=='')
            query('UPDATE `users` SET password=? WHERE id=?', [ password_hash($POST['pass'], PASSWORD_BCRYPT), $id  ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }

    public function mess( $POST ){

        if(!($id_chat= $POST['id_chat']) || !($text = $POST['text']))
            return response_if_error('не хватает параметров');

        $sm=send_mess([ 'body'=> $text, 'id_chat'=> $id_chat, 'files'=> $POST['files'] ]);

        if(!$sm[0]['message_id'])
            return response_if_error('Ошибка при отправке сообщения');

        return json_encode([
            'success'=> 'ok'
        ]);

    }


    public function remove( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        
        query('DELETE FROM `users` WHERE id=?', [ $id ]);

        return json_encode([
             'success'=> 'ok'
        ]);
    
    }

}

















