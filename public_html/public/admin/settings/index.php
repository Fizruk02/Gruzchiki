<!doctype html>
<html lang="en">
<head>
<title>Настройки</title>
<?include_once("../resources/_phpparsite.php");
res("_ass.php");?>
<style>
    .mw8{max-width:800px}
    .min-input-group-prepend{
        display:none !IMPORTANT;
    }
    @media screen and (max-width: 820px) {
        .input-group-prepend, #div-check-sms{
            display:none !IMPORTANT;
        }
        .min-input-group-prepend, #div-check-sms{
            display:block !IMPORTANT;
        }
    }
    @media screen and (max-width: 480px) {
    }
    .input-overflow{
        width: 400px;
        height: 200px;
        z-index: 0;
        background-repeat: no-repeat;
        background-size: auto;
        background-position: center;
        background-size: cover;
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
<!-- <script src="data/js.js?v=0.1"></script> -->
</html>