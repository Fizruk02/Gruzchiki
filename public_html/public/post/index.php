<?
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

$request = $_GET['request'];
$params = explode('/', $request);
$class = $params[0];
$method = $params[1];
$par = $_POST;

if (!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']['err']);

$par['userInfo']=$gethash;

if(!file_exists($f=$_SERVER['DOCUMENT_ROOT'].'/../src/project/'.$class.'/'.$class.'.php'))
    return response_if_error('класс «'.$class.'» не найден');
require $f;
$classObj = new $class();
if(!method_exists($classObj, $method))
    return response_if_error('метод «'.$method.'» не найден');
$resp =  $classObj->$method( $par ) ;
if(is_array($resp)) $resp = json_encode($resp);
echo $resp;