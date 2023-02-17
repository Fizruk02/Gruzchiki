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
        $cat=$POST['category'];
        if($cat) $cat='c.id_cat='.$cat;
        else $cat='true';
        $res = arrayQuery(' SELECT d.*, IFNULL(c.id_cat, 0) categoryId
                            FROM `_data` d
                            LEFT JOIN _data_categories c ON c.id_item = d.id
                            WHERE '.$cat.'
                            ORDER BY c.id');

        $res = array_map(function($it) {
            $it['file']=loadFiles()->getFilesforweb( $it['files'] );
            return $it;
        }, $res);

        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);
        return [
            'success'=> 'ok'
            ,'data'=> $res
        ];

    }

    public function add( $POST ){

        $body = $POST['body'];
        $name = $POST['name'];
        $files = (int) $POST['files'];

        $id=query('INSERT INTO `_data` (`body`,  `name`,  `files`) VALUES (?,?,?)',
            [ $body,   $name,  $files ] );

        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);
        if(!$data=singleQuery('SELECT * FROM `_data` WHERE id=?',[ $id ]))
            return response_if_error('Ошибка при добавлении данных');
        $data['categoryId']=$POST['category'];
        if($POST['category'])
            query('INSERT INTO `_data_categories` (`id_item`, `id_cat`) VALUES (?,?)', [ $id,$POST['category'] ]);

        return [
            'success'=> 'ok'
            ,'data'=> $data
        ];

    }

    public function edit( $POST ){
        $body = $POST['body'];
        $name = $POST['name'];
        $files = (int) $POST['files'];
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');

        query('UPDATE `_data` SET `body`=?,`name`=?,`files`=? WHERE id=?',
            [ $body, $name, $files, $id  ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);

        query('DELETE FROM `_data_categories` WHERE id_item=?', [ $id ]);

        if($POST['category'])
            query('INSERT INTO `_data_categories` (`id_item`, `id_cat`) VALUES (?,?)', [ $id,$POST['category'] ]);


        return [
            'success'=> 'ok'
        ];

    }

    public function remove( $POST ){

        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');

        query('DELETE FROM `_data` WHERE id=?', [ $id ]);
        query('DELETE FROM `_data_categories` WHERE id_item=?', [ $id ]);

        return [
            'success'=> 'ok'
        ];

    }


    public function editCategory( $POST ){
        query('UPDATE `_data_categories_list` SET `name` = ?, `parent` = ? WHERE id = ?', [ $POST['name'], (int) $POST['parent'], $POST['id'] ]);
        return [
            'success'=> 'ok'
        ];
    }

    public function deleteCategory( $POST ){
        $id = (int) $POST['id'];
        if(!$id) return bterr('Не передан id');
        
        query( 'DELETE FROM `_data_categories_list` WHERE id='.$id );
        query( 'UPDATE `_data_categories_list` SET parent=0 WHERE parent='.$id );
        
        $items = arrayQuery( 'SELECT * FROM `_data_categories` WHERE id_cat='.$id );
        foreach($items as $item) {
            query( 'DELETE FROM `_data` WHERE id='.$item['id_item'] );
        }
        
        query( 'DELETE FROM `_data_categories` WHERE id_cat='.$id );

        return [
            'success'=> 'ok'
        ];
    }

    public function addCategory( $POST ){
        if(!$name = $POST["name"])
            return response_if_error('не хватает параметров');

        //if(singleQuery('SELECT * FROM `_data_categories_list` WHERE name=?', [ $name ]))
        //    return response_if_error('Категория "'.$name.'" уже существует');

        $categoryId = query('INSERT INTO `_data_categories_list` (`name`, `parent`) VALUES (?,?)', [ $name, (int) $POST['parent'] ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);
        return [
            'success'=> 'ok'
            ,'id'=> $categoryId
        ];
    }



}

















