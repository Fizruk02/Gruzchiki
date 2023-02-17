<?
function loadModule($name){

    $d = $_SERVER['DOCUMENT_ROOT'].'/../src/project/modules/'.$name.'/';

    $file = $d.$name.'.php';
    if(file_exists($file)){
        include_once($file);
        $namespace = 'project\\modules\\'.$name.'\\'.$name;
        if(file_exists($file))
        return new $namespace();
    } else {
        return false;
    }
}

function findMethod($name, $par=false){
    $d = $_SERVER['DOCUMENT_ROOT'].'/../src/project/modules';
    $listDir = scandir($d);
    foreach($listDir as $_dir)
    if(strpos($_dir, '.') === false){
        $class = loadModule($_dir);
        if(method_exists($class, $name)){
            return !$par? $class:$class::$name($par);
            
        }

            
    }
     
    return false;   
    
}