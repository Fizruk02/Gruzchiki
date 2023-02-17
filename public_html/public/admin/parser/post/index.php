<?
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$request = $_GET['request'];
$params = explode('/', $request);
$method = $params[0];
$par = $_POST;

if (!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']);

include(__DIR__.'/../methods.php');

$class = new template();

if(!method_exists($class, $method))
    return response_if_error('метод '.$method.' не найден');
    
$resp =  $class->$method( $par ) ;

echo is_array( $resp ) ? json_encode( $resp ) : $resp;