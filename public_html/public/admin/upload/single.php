<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

session_start();
if (  !$_SESSION['user']['role_id'] && (!($h = permission_to_use()['access'])&&$_POST['bot_key']!=setting('bot_key'))   ) return response_if_error($h['mess']);


$file = @$_FILES['file'];

$warning='';

$deny = array(
    'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 'phps', 'cgi', 'pl', 'asp',
    'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html',
    'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
);

// Директория, куда будут загружаться файлы.
$path = $_SERVER["DOCUMENT_ROOT"] . '/files/loaded/';

if (empty($file))
    return response_if_error('Не удалсь получить файл');

if (!empty($file['error']) || empty($file['tmp_name'])) {
    switch (@$file['error']) {
        case 1:
        case 2: $error = 'Превышен размер загружаемого файла.'; break;
        case 3: $error = 'Файл был получен только частично.'; break;
        case 4: $error = 'Файл не был загружен.'; break;
        case 6: $error = 'Файл не загружен - отсутствует временная директория.'; break;
        case 7: $error = 'Не удалось записать файл на диск.'; break;
        case 8: $error = 'PHP-расширение остановило загрузку файла.'; break;
        case 9: $error = 'Файл не был загружен - директория не существует.'; break;
        case 10: $error = 'Превышен максимально допустимый размер файла.'; break;
        case 11: $error = 'Данный тип файла запрещен.'; break;
        case 12: $error = 'Ошибка при копировании файла.'; break;
        default: $error = 'Файл не был загружен - неизвестная ошибка.'; break;
    }
    return response_if_error($error);
} elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name']))
    return response_if_error('Не удалось загрузить файл!');



// Оставляем в имени файла только буквы, цифры и некоторые символы.
$pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
$name = mb_eregi_replace($pattern, '-', $file['name']);
$name = mb_ereg_replace('[-]+', '-', $name);

$array = explode(".", $name);
$ext = end($array);

$id = uniqid();
$name = $id.'.'.$ext;

$parts = pathinfo($name);
if (empty($name) || empty($parts['extension']))
    return response_if_error('Не удалось загрузить файл');

if(!empty($deny) && in_array(strtolower($parts['extension']), $deny))
    return response_if_error('Недопустимый тип файла');

// Перемещаем файл в директорию.
if (!move_uploaded_file($file['tmp_name'], $path . $name))
    return response_if_error('Не удалось загрузить файл');

$original = $path . $name;

$mimeType = mime_content_type($original);
$fileType = explode('/', $mimeType)[0];

if($fileType=='image')
    $type = 'img';
elseif($fileType=='video')
    $type = 'video';
else $type = 'doc';


$fileUrl='/files/loaded/'.$name;
echo json_encode([
    'success'=>'ok'
    ,'warning'=> trim($warning)
    ,'preview'=>$fileUrl
    ,'file'=> $fileUrl
    ,'name'=> $name
    ,'src'=> $file['name']
    ,'type'=> $type
    ,'ext'=> $ext
]);