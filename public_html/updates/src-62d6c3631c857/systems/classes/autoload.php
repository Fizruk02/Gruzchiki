<?php
function loadSystemModule($name){
    $d = $_SERVER['DOCUMENT_ROOT'].'/../src/systems/classes/';
    $name = pathinfo($name)['filename'];
    $file = $d.$name.'.php';
    if(file_exists($file)){
        include_once($file);
        $namespace = 'systems\\classes\\'.$name.'\\'.$name;
        return new $namespace();
        
    } else {
        return false;
    }
}

function findSystemMethod($name){
    $d = $_SERVER['DOCUMENT_ROOT'].'/../src/systems/classes';
    $listDir = array_diff(scandir($d), array('..', '.'));

    foreach($listDir as $_dir)
    if(!in_array($_dir, ['autoload.php', 'messageTelegram.php'])){ 
        $class = loadSystemModule($_dir);
        if(method_exists($class, $name))
            return $class;
    }
    return false;   
}