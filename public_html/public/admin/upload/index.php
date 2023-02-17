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

$group = $_POST['group'] && $_POST['group']!='undefined'?$_POST['group']:loadFiles()->getFileGroup();


$original = $path . $name;
$small_size = '';
$medium_size = '';



$mimeType = mime_content_type($original);
$fileType = explode('/', $mimeType)[0];

if($fileType=='image'){
    if(!in_array($ext, ['webp'])){
        $cl = new resize();
        $resize = $cl-> get($original,$id);
    
        $small_size = $resize['small_size'];
        $medium_size = $resize['medium_size'];
    } else {
        $small_size=$medium_size='files/loaded/'.$name;
    }
    $preview= $small_size;
}


if($fileType=='image')
    $type = 'img';
elseif($fileType=='video')
    $type = 'video';
else $type = 'doc';


$fileId = insertQuery('INSERT INTO files (name, id_group, small_size, medium_size, large_size, type_file) VALUES ("", ?,?,?,?,?)', [ $group, $small_size, $medium_size,'files/loaded/'.$name, $type ]);

if(!$fileId){
    response_if_error('Ошибка записи в базу');
    exit;
}

if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]){
    response_if_error($stmtErr);
    exit;
}



$ext = strtolower($ext);
if($type=='video' && $ext!='mp4')
    $warning .= 'Телеграм отправляет видео только формата "mp4", поэтому это видео отправит как документ<br>'.PHP_EOL;

switch($ext){
    case 'mov':
        $type = 'doc';
        break;
}

echo json_encode([
    'success'=>'ok'
    ,'warning'=> trim($warning)
    ,'id_group'=>$group
    ,'preview'=>'/'.$preview
    ,'file'=>'/files/loaded/'.$name
    ,'fileid'=>$fileId
    ,'type'=> $type
    ,'ext'=> $ext
]);



class resize
{

    public function get($filename,$id){
        $info   = getimagesize($filename);
        $type   = $info[2];

        switch ($type) {
            case 1:
                $img = imageCreateFromGif($filename);
                imageSaveAlpha($img, true);
                break;
            case 2:
                $img = imageCreateFromJpeg($filename);
                break;
            case 3:
                $img = imageCreateFromPng($filename);
                imageSaveAlpha($img, true);
                break;
        }



        $ext = pathinfo($filename,PATHINFO_EXTENSION);


        if ($ext == "jpeg" || $ext == "jpg") {
            $this-> ExifRotate($img);
        }

        $link_128 = "files/loaded/size_128_$id.$ext";
        $link_650 = "files/loaded/size_650_$id.$ext";
        $this-> create(128,0,$link_128,$img,$info);
        $this-> create(650,0,$link_650,$img,$info);
        return [ 'small_size'=>$link_128, 'medium_size'=>$link_650 ];
    }

    public function create($w,$h,$file,$img,$info){
        $file=$_SERVER['DOCUMENT_ROOT']."/".$file;
        $width  = $info[0];
        $height = $info[1];
        $type   = $info[2];
        if (empty($w)) {
            $w = ceil($h / ($height / $width));
        }
        if (empty($h)) {
            $h = ceil($w / ($width / $height));
        }

        $tmp = imageCreateTrueColor($w, $h);
        if ($type == 1 || $type == 3) {
            imagealphablending($tmp, true);
            imageSaveAlpha($tmp, true);
            $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
            imagefill($tmp, 0, 0, $transparent);
            imagecolortransparent($tmp, $transparent);
        }

        $tw = ceil($h / ($height / $width));
        $th = ceil($w / ($width / $height));
        if ($tw < $w) {
            imageCopyResampled($tmp, $img, ceil(($w - $tw) / 2), 0, 0, 0, $tw, $h, $width, $height);
        } else {
            imageCopyResampled($tmp, $img, 0, ceil(($h - $th) / 2), 0, 0, $w, $th, $width, $height);
        }

        $img=$tmp;
        switch ($type) {
            case 1:
                //header('Content-Type: image/gif');
                imageGif($img, $file);
                break;
            case 2:
                //header('Content-Type: image/jpeg');
                imageJpeg($img, $file, 100);
                break;
            case 3:

                //header('Content-Type: image/x-png');
                imagePng($img, $file);
                break;
        }
        imagedestroy($img);
    }

    public function exifRotate ($file_path) {
        $image = imagecreatefromjpeg($file_path);
        // Прочитать данные EXIF
        $exif = exif_read_data($file_path);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                // Поворот на 180 градусов
                case 3: {
                    $image = imagerotate($image,180,0);
                    break;
                }
                // Поворот вправо на 90 градусов
                case 6: {
                    $image = imagerotate($image,-90,0);
                    break;
                }
                // Поворот влево на 90 градусов
                case 8: {
                    $image = imagerotate($image,90,0);
                    break;
                }
            }
        }

        imagejpeg($image, $file_path, 100);

    }
}