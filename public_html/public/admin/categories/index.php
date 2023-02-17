<!doctype html>
<html lang="en">
<head>
<title>Категории</title>
<?include_once("../resources/_phpparsite.php");
res("_ass.php");?> 
<script src="/admin/js/jsTree/jstree.min.js"></script>
<link rel="stylesheet" href="/admin/js/jsTree/themes/default/style.min.css" />
<style>
.img-content{
    background: white;
    border: 1px solid #ced4da;
    padding: 16px;

}
.input-overflow{
    width: 100px;
    height: 100px;
    z-index: 0;
    background-image: url(/files/systems/no_photo_100_100.jpg);
    background-repeat: no-repeat;
    background-size: auto;
    background-position: center;
}
input[type="file"]{
    width: 100px;
    height: 100px;
    cursor: pointer;
    position: absolute;
    z-index: 1;
    opacity: 0;
}
</style>
</head>
<body>
<? res("_nav.php")?>
<? include('data/index.php')?>
</body>
<? res("js.php"); ?>
<script src="data/js.js?v=0.<?php echo time();?>"></script>
</html>