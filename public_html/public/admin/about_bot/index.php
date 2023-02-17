<!doctype html>
<html lang="en">
<head>
<title>О программе</title>
<?include_once("../resources/_phpparsite.php");
res("_ass.php");?>
<style>
.rectang {
    position:relative;
    padding:10px;
    text-align:center;
    width:fit-content;
    border:1px solid #0088cc;
    -webkit-border-radius:10px;
    -moz-border-radius:10px;
    border-radius:10px;
    color:#0088cc;
}
.nav-link-th.active{
    background-color: #b6cfea !IMPORTANT;
}
.specifications {
    width: 260px;
}
.specifications [fl-imagespanel]{
    margin: 0 !IMPORTANT;
    padding: 0 !IMPORTANT;
}
.specifications .container{
    margin: 0 !IMPORTANT;
    padding: 0 !IMPORTANT;
}
</style>
</head>
<body onload="init()">
<? res("_nav.php")?>
<div class="container">
<? include('data/index.php')?>
</div>
</body>
<? res("js.php"); ?>
<script src="data/js.js?v=0.1"></script>
</html>