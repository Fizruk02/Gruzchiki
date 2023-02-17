<!doctype html>
<html lang="en">
<head>
<title><?=basename(__DIR__)?></title>
<?include_once("../resources/_phpparsite.php");
res("_ass.php");?>
</head>
<body>
<? res("_nav.php")?>
<? include('data/index.php')?>
</body>
<? res("js.php"); ?>
<script src="data/js.js?v=0.1"></script>
<script src="data/kb.js?v=0.1"></script>
</html>