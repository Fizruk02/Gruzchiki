<?php
session_start();
$res['res']=$_POST;
if($_POST['cmd']==='set') $_SESSION['project'][$_POST['v']] = $_POST['d'];
elseif($_POST['cmd']==='get') $res['res']=is_object($l=json_decode($_SESSION['project'][$_POST['v']]))?$l:$_SESSION['project'][$_POST['v']];
elseif($_POST['cmd']==='delete') unset($_SESSION['project'][$_POST['v']]);
else {echo json_encode(['err'=> 'not found']);return;};
$res['success']='ok';
echo json_encode($res);