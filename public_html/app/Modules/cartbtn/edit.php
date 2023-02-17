<?php 
use system\lib\Db;
$db=DB::getInstance();
$cartPages = $db->arrayQuery('SELECT * FROM `web_pages`');
?>
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


<div class="p-1 row">
<div class="col-auto">
<pre>
<?php echo htmlspecialchars('<div class="_cartbtn"> </div>'); ?>
<br>
._cartbtn {
    background: #009144;
    border: 1px solid #009144;
}
._cartbtn:hover {
    background: #02803d;
    border-color: #02803d;
}
</pre>
</div>
<div class="col">
    <label>Страница корзины</label>
    <select class="form-select" id="cartPage">
    <option value="">Не выбрана</option>
    	<?php foreach($cartPages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
    </select>
</div>

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
    qw.qs("#cartPage").value=data.cartPage||"";
    
}

function _sendData(){
    let res =
    {
         _translatedFields:_translatedFields
        ,cartPage    : qw.qs("#cartPage").value
    }
    return res;
}
function resize(){window.parent.postMessage({ method:"blockResize", id:frameid }, "*");}
</script>
