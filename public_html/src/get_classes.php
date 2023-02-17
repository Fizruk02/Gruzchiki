<?php
/**
 * возвращает массив данных файлов
 *  {
 *      [file] => /home/folder/public_html/../src/systems/classes/class/class.php
 *      [class] => class
 *      [namespace] => systems\classes\class
 * }
 */

$ignore_files = ['autoload.php'];

$filelist = [];

$scan = $_SERVER['DOCUMENT_ROOT'].'/../src/systems/classes/';
foreach(scandir($scan) as $fl){
    $file = $scan.$fl;
    if(is_file($file) && !in_array(basename($file), $ignore_files)){
        $f = fopen($file, "r");
        $content = fread($f, filesize ($file));

        $rows = preg_split("/\\r\\n?|\\n/", $content);

        $namespace = '';
        $class = '';

        foreach($rows as $row){
            if(strpos($row, 'namespace ')!==false){
                $namespace = trim(str_replace('namespace', '', $row));
                $namespace = str_replace(';', '', $namespace);
            }
        }

        $class = pathinfo($fl)['filename'];


        if($class && $namespace)
        array_push($filelist, ['file'=> $file, 'class'=> $class, 'namespace'=> $namespace]);

    }
}

$filelist[]=[
     'file' => '/home/w/wmstr2tm/game.b2cb.ru/public_html/../src/systems/curl/curl.php'
    ,'class' => 'curl'
    ,'namespace' => 'systems\curl'
];
$filelist[]=[
     'file' => '/home/w/wmstr2tm/game.b2cb.ru/public_html/../src/telegram/methods/methods.php'
    ,'class' => 'methods'
    ,'namespace' => 'telegram\methods'
];
$filelist[]=[
     'file' => '/home/w/wmstr2tm/game.b2cb.ru/public_html/../src/systems/db/db.php'
    ,'class' => 'db'
    ,'namespace' => 'systems\db'
];


return $filelist;