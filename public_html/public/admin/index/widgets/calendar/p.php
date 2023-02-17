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
    
    
    public function get( $POST ){

        $res = array_map(function($it){
            return $it['udate'];
        }, arrayQuery('SELECT udate FROM `dt_days`'));

        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    
    }

    public function set( $POST ){
        $dt = $POST['dt']??[];
        query('DELETE FROM `dt_days`');
        foreach($dt as $r)
            query('INSERT INTO `dt_days` (`d`, `m`, `y`, `udate`, `date`) VALUES (?,?,?,?,?)', [date('j', $r),date('n', $r),date('Y', $r),$r,date('Y-m-d', $r)]);
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }



}

















