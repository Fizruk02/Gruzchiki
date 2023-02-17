<?php
/**
 * подключаем файлы
 */
$glDir = $_SERVER['DOCUMENT_ROOT'].'/../src/systems';

$ignore = ['project'];

$filelist = [];
$arr = glob_tree_dirs($glDir);
foreach($arr as $d){
    $scan = glob($glDir.'/'.$d."/*");
    foreach($scan as $file)
    if(is_file($file)){
        include($file);
    }   


        
    
}