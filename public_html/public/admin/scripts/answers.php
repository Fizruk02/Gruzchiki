<!doctype html>
<html lang="en">
   <head>
      <title>Ответы</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <style>
         .highlight {
             padding: 4px;
             padding-left: 1rem;
             margin-bottom: 1rem;
             background-color: #f8f9fa
         }
        .truncate-text {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            border:0;
        }
      </style>
      <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
      <? 
         include_once("../resources/_phpparsite.php");
         res("_ass.php");
         if(!$permission_to_use['access']) exit;
         ?>
   </head>
   <body>
    <? res("_nav.php")?>

<div class="container mt-2" tableform>
   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
      <table class="table table-hover table-sm" id="table-answers" display="answers">
         <thead class="thead-dark">
            <tr>
               <th>Дата</th>
               <th>id</th>
               <th>Имя</th>
               <th>Скрипт</th>
               <th>Вопрос</th>
               <th>Ответ</th>
                  <!-- <th style="width:78px"></th> -->
            </tr>
         </thead>
      </table>
   </div>
</div>


<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body" id="filterbody">
      </div>
    </div>
  </div>
</div>


</body>
<? res("js.php"); ?>
<script src="answers.js?<?=time()?>"></script>



<script>
var table = 'table-answers';
</script>
</html>
  
    


























