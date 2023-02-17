<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
$id_group = $_POST['gid'];
if(!$_POST['fid'])
    return response_if_error('Переданы не все параметры');
query("DELETE FROM files WHERE id = ?", [ $_POST['fid'] ]);
# если в группе больше нет файлов, то обнуляем группу, потом при добавлении она создатся заново
if(!singleQuery("SELECT * FROM files WHERE id_group = ?",[ $id_group ]))
    $id_group = '';
echo json_encode([
     'success'=> 'ok'
    ,'gid'=> $id_group
]);