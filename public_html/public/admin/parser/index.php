<!doctype html>
<html lang="en">
   <head>
      <title>Парсер</title>
      <?
         include_once("../resources/_phpparsite.php");
         res("_ass.php");
         if(!$permission_to_use['access']) return;
         ?> 
   </head>
   <body>
    <? res("_nav.php")?>
    <? include('data/index.php')?>
   </body>
<script src="//cdnjs.cloudflare.com/ajax/libs/exceljs/3.8.2/exceljs.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/pdfmake.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/vfs_fonts.js"></script>
<script src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="//b2bot.ru//components/table.js"></script>
<script src="//b2bot.ru//components/toast.js"></script>
<script src="//b2bot.ru//components/dialogboxes.js"></script>
<script src="data/js.js?v=0.1"></script>
</html>