<!doctype html>
<html lang="en">
<head>
<title>Пользователи</title>
<?include_once("../resources/_phpparsite.php");
res("_ass.php");
if(!$permission_to_use['access']) return;?> 
</head>
<body>
<? res("_nav.php")?>
<? include('data/index.php')?>
</body>
<? res("js.php"); ?>
<script src="data/js.js?v=0.1"></script>
</html>