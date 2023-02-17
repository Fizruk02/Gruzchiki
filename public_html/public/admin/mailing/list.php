<!doctype html>
<html lang="en">
<head>
<title>Список рассылок</title>
<?
include_once("../resources/_phpparsite.php");
res("_ass.php");
if(!$permission_to_use['access']) return;
?> 
</head>
<body>
<? res("_nav.php")?>
<? include('list/index.php')?>
</body>
<?res('js.php')?>
<script src="list/js.js?<?=time()?>"></script>
</html>
  
    




























