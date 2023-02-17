<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$params = explode('/', $_GET['q']);
$method = $params[0];

if (!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']);
$_POST['accessdata']=$gethash;
$class = new index();

if(!method_exists($class, $method))
    return response_if_error('метод '.$method.' не найден');

$resp =  $class->$method( $_POST ) ;

echo is_array( $resp ) ? json_encode( $resp ) : $resp;




class index
{
    
    function wsort($input){
        foreach($input['items'] as $it){
            query('UPDATE s_dashboard SET num=?,parent=? WHERE id=?',[ $it['num'],$it['prnt'],$it['id'] ]);
        }
        return [ 'success'=> 'ok' ];
    }
    
    function search($input){
        if(!$text = $input["text"])
            return response_if_error('не хватает параметров');

        $tables = arrayQuery('SHOW TABLES');
        $data=[];
        foreach ($tables as $tab) {
            $table=array_values($tab)[0];
            if(in_array($table, [ 'steps', 'constructors' ]) || strpos( $table, 's_')===0) continue;
            $columns = [];
            foreach (arrayQuery('SHOW COLUMNS FROM `'.$table.'`') as $col)
            if($col['Field']!=='id'){
                $columns[]='`'.$col['Field'].'` LIKE(:p)';
                //qwer($col['Field']);
            }
            $sql = 'SELECT * FROM `'.$table.'` WHERE '.implode(' OR ', $columns);
            $res = arrayQuery($sql, [ ':p'=> $text ]); //'%'.$text.'%'
            if(count($res)){
                $data[]=[
                    'table'=> $table,
                    'rows'=> $res
                ];
            }
        }

        return [
            'success'=> 'ok'
            ,'data'=> $data
        ];
    }
}