<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/resources/plugins/excel/Classes/PHPExcel.php');
$file = @$_FILES['file'];

if (!($gethash = permission_to_use())['access'])
    return response_if_error($gethash['mess']['err']);

if(!isset($file) || !$file['error'] == 0)
    return response_if_error('файл не загружен');

$childpatch='/files/downloads/';
$destiation_dir = $_SERVER["DOCUMENT_ROOT"] . $childpatch;
$name = $file['name'];

move_uploaded_file($file['tmp_name'], $destiation_dir . $name);
$excel  = PHPExcel_IOFactory::load($destiation_dir . $name);

$worksheet = $excel->getSheet(0);

$colNumber = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
$res=['success'=>'ok'];
$res['header']=[];
 for($i=0;$i<$colNumber;$i++)
     array_push($res['header'], $worksheet->getCellByColumnAndRow($i, 1)->getValue());

$res['data']=[];
$lastRow = $worksheet->getHighestRow();
for ($row = 2; $row <= $lastRow; $row++){
    $arr=[];
    for ($col = 0; $col < $colNumber; $col++)
        $arr[$res['header'][$col]] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
    array_push($res['data'], $arr);
}
foreach($res['data'] as $key=>&$dt){
    if(!isset($res['data'][0]['id']))$dt['id']=$key;
    if(!isset($res['data'][0]['check']))$dt['check']=1;
}
echo json_encode($res);