<?php 
use system\lib\Db;
$db=DB::getInstance();
//$contentPages = $db->arrayQuery(''); ?>
<head>
<link href="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
 <!-- <script src="/admin/js/jquery.min.js"></script> -->
<script src="//b2bot.ru/components/qw.js"></script>
<script src="//b2bot.ru/components/upload/js.js"></script>
<script src="//b2bot.ru/components/toast.js"></script>
<!-- <script src="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script> -->
</head>
<body>



<div class="p-1">

<!-- <div class="input-group mb-3">
    <span class="input-group-text" id="basic-addon1">Title</span>
  <input type="text" class="form-control" placeholder="" id="title">
</div> -->

</div>
</body>

<script>
    var frameid;
    var _translatedFields={
         //"title":{"name":"Title"},
         //"fields":{"name":"Дополнительное поле", "fields":{ "title":"Название", "placeholder":"подсказка" } },
         }
function _getData( res ){
    let data=res.data;
    //qw.qs("#title").value=data.title||"";
    
}

function _sendData(){
    let res =
    {
         _translatedFields:_translatedFields
        //,title    : qw.qs("#title").value
    }
    return res;
}
function resize(){window.parent.postMessage({ method:"blockResize", id:frameid }, "*");}
</script>
