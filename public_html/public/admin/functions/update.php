<?
//header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
require $_SERVER['DOCUMENT_ROOT'].'/admin/_dev/_createFunctions.php';

if(!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']['err']);
$target = '_dev';

$p=$_SERVER['DOCUMENT_ROOT'].'/admin/_dev/update/files';

removeDirectory($p);
mkdir($p);

if(!is_dir($p)) return response_if_error('Отсутствует папка загрузки');


$resLoad = post('https://b2bot.ru/project/?target='.$target);
$resLoad = json_decode($resLoad,true);
if($resLoad['success']!=1) return response_if_error($resLoad['err']?$resLoad['err']:'Ошибка при получении ссылки');

if(!copy($resLoad['file'], $p.'/file.zip')) return response_if_error('Ошибка при скачивании файла');

$zip = new ZipArchive;
if (!$resZip = $zip->open($p.'/file.zip')){
    removeDirectory($p);
    return response_if_error('ошибка разархивирования файла код:'.$resZip);
}
$zip->extractTo($p);
$zip->close();
   
unlink($p.'/file.zip');
    
if($target=='_dev'){
    removeDirectory($p.'/modules');
    copy_directory($_SERVER['DOCUMENT_ROOT'].'/admin/_dev/modules', $p.'/modules');
    rename($p, $_SERVER['DOCUMENT_ROOT'].'/admin/_dev_temp');
    removeDirectory($_SERVER['DOCUMENT_ROOT'].'/admin/_dev');
    rename($_SERVER['DOCUMENT_ROOT'].'/admin/_dev_temp',$_SERVER['DOCUMENT_ROOT'].'/admin/_dev');
}
    



//print_r($res);
//echo $res;