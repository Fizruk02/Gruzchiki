<?php
    require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

    $parameters 	= $_POST["p"];
    $parameters     = json_decode($parameters, true);
    $id_file	    = $parameters["id_file"];
    if(!$id_file) $id_file = $_GET["id_file"];
    
    $res = array();
    $success = 1;
    if(!$id_file) $success = 2;
    

    
    if($success==1){
        $result = $mysqli -> query("SELECT small_size, medium_size, large_size FROM files WHERE id = $id_file") or die(mysql_error());
        while ($row = $result -> fetch_assoc()) {
            $dir = $_SERVER['DOCUMENT_ROOT']."/";
            $unlink = @unlink($dir.$row['small_size']);
            $unlink = @unlink($dir.$row['medium_size']);
            $unlink = @unlink($dir.$row['large_size']);
            
        }
        $mysqli -> query("DELETE FROM files WHERE id = $id_file") or die(mysql_error());
    }

    

    $res['success']=$success;
    
    // echo json_encode($res);
echo 1;
    
    
    
    # success 1 - все хорошо, 2 - не передан параметр
    
?>