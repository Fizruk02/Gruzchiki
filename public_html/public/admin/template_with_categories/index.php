<!doctype html>
<html lang="en">
   <head>
      <title><?=basename(__DIR__)?></title>
      <?
         include_once("../resources/_phpparsite.php");
         res("_ass.php");
         if(!$permission_to_use['access']) return;
         ?>
         <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <style>
            .cat_name {
                cursor:pointer;
                transition: font-size 100ms;
            }
            .cat_name.selected {
                font-size: 14pt;
                font-weight: bolder;
            }
        </style>
   </head>
<body>

<? res("_nav.php")?>
<? include('data/index.php')?>
</body>
<? res("js.php"); ?>
<script src="data/js.js?v=<?=time()?>"></script>
</html>