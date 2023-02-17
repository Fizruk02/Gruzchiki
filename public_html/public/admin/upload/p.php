<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
session_start();
if (  !$_SESSION['user']['role_id'] && (!($h = permission_to_use()['access'])&&$_POST['bot_key']!=setting('bot_key'))   ) return response_if_error($h['mess']);

if(!method_exists(($class = new cl()), ($method=$_GET['q'])))
    return response_if_error('метод '.$method.' не найден');
$resp =  $class->$method( $_POST ) ;
echo is_array( $resp ) ? json_encode( $resp ) : $resp;
class cl
{
    function getFiles($input){
        return [
             'success'=> 'ok'
            ,'data'=> loadFiles()->getFilesforweb( $input['group'] )
        ];
    }
    function rnm($input){
        if(!$id=$input['id']) return response_if_error('не передан id файла');
        if(!$f=singleQuery('SELECT * FROM `files` WHERE id=?',[$id])) return response_if_error('файл не найден в базе');
        $old=pathinfo($f['large_size']);
        $new=pathinfo($input['n']);
        if(!$new['filename']) return response_if_error('некорректное имя файла');
        if(!isset($new['extension'])) $new['extension']=$old['extension']??'';
        $new['dirname']=$old['dirname'];
        $fn=$new['dirname'].'/'.$new['filename'].'.'.$new['extension'];
        $ofn=$old['dirname'].'/'.$old['filename'].'.'.$old['extension'];
        if(strpos($ofn,'http')===0) return response_if_error('нельзя переименовать этот файл');
        if(singleQuery('SELECT * FROM `files` WHERE (small_size LIKE(:f) OR medium_size LIKE(:f) OR large_size LIKE(:f)) AND id<>:id',[':f'=> '%'.$fn,':id'=>$id]))
            return response_if_error('файл с таким названием уже существует');
        $r=$_SERVER['DOCUMENT_ROOT'].'/';
        if(!is_file($r.$ofn)) return response_if_error('файл не найден');
        if(rename($r.$ofn,$r.$fn)) query('UPDATE `files` SET `large_size` = ? WHERE `id` = ?',[$fn,$id]);
        return [
             'success'=> 'ok',
             'fn'=>$new['filename'].'.'.$new['extension'],
             'url'=>$fn
        ];
    }
    function delete($input){
        query("DELETE FROM files WHERE id = ?", [ $input['fid'] ]);
        # если в группе больше нет файлов, то обнуляем группу, потом при добавлении она создатся заново
        if(!singleQuery("SELECT * FROM files WHERE id_group = ?",[ $id_group ]))
            $id_group = '';
        echo json_encode([
             'success'=> 'ok'
            ,'gid'=> $id_group
        ]);
    }
}
