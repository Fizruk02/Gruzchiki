<?php

require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$params = explode('/', $_GET['q']);
$method = $params[0];

if (!($gethash = permission_to_use())['access'])
    return bterr($gethash['mess']);
$_POST['accessdata']=$gethash;
$class = new template();

if(!method_exists($class, $method))
    return bterr('метод '.$method.' не найден');

$resp =  $class->$method( $_POST ) ;

echo is_array( $resp ) ? json_encode( $resp ) : $resp;

class template
{
    
    
    public function getList( $POST ){
        $res = arrayQuery(' SELECT * FROM `us_access` WHERE role_id=?', [ $POST['id'] ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return bterr($stmtErr);
//        $res=array_map(function ($it){
//            $it['actions']=json_decode($it['actions'],1);
//            return $it;
//        }, $res);
       // $res = $res['action'];
        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    
    }

    public function add( $POST ){

        $body = $POST['body'];
        $name = $POST['name'];
        $files = $POST['files'];

        $id=query('INSERT INTO `_data` (`body`,  `name`,  `files`) VALUES (?,?,?)',
                                             [ $body,   $name,  $files ] );

        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return bterr($stmtErr);
        if(!$data=singleQuery('SELECT * FROM `_data` WHERE id=?',[ $id ]))
            return bterr('Ошибка при добавлении данных');
        $data['categoryId']=$POST['category'];
        if($POST['category'])
            query('INSERT INTO `_data_categories` (`id_item`, `id_cat`) VALUES (?,?)', [ $id,$POST['category'] ]);
            
        return json_encode([
            'success'=> 'ok'
            ,'data'=> $data
        ]);

    }

    public function edit( $POST ){
        $body = $POST['body'];
        $name = $POST['name'];
        $files = $POST['files'];
        if(!$id = $POST['id'])
            return bterr('не хватает параметров');

        query('UPDATE `_data` SET `body`=?,`name`=?,`files`=? WHERE id=?',
                                 [ $body, $name, $files, $id  ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return bterr($stmtErr);
        
        query('DELETE FROM `_data_categories` WHERE id_item=?', [ $id ]);

        if($POST['category'])
            query('INSERT INTO `_data_categories` (`id_item`, `id_cat`) VALUES (?,?)', [ $id,$POST['category'] ]);

        
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }
    
    public function remove( $POST ){
        
        if(!$id = $POST['id'])
            return bterr('не хватает параметров');
        
        query('DELETE FROM `data` WHERE id=?', [ $id ]);
        query('DELETE FROM `_data_categories` WHERE id_item=?', [ $id ]);
        
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }

    
    public function editCategory( $POST ){
        query('UPDATE `us_roles` SET `name` = ?, `parent` = ? WHERE id = ?', [ $POST['name'], $POST['parent'], $POST['id'] ]);
        return json_encode([
             'success'=> 'ok'
        ]);
    }
    
    public function deleteCategory( $POST ){
        query('DELETE FROM `us_roles` WHERE id=?', [ $POST['id'] ]);
        query('UPDATE `us_roles` SET parent=0 WHERE parent=?', [ $POST['id'] ]);
        query('DELETE FROM `_data_categories` WHERE id_cat=?', [ $POST['id'] ]);
        return json_encode([
             'success'=> 'ok'
        ]);
    }
    
    public function addCategory( $POST ){
        if(!$name = $POST["name"])
            return bterr('не хватает параметров');
        
        if(singleQuery('SELECT * FROM `us_roles` WHERE name=?', [ $name ]))
            return bterr('Категория "'.$name.'" уже существует');
        
        $categoryId = query('INSERT INTO `us_roles` (`name`) VALUES (?)', [ $name ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return bterr($stmtErr);
        return json_encode([
             'success'=> 'ok'
            ,'id'=> $categoryId
        ]);
    }



}

















