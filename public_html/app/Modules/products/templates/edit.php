<?php
use system\lib\Db;

$db=DB::getInstance();
$cartPages = $db->arrayQuery('SELECT * FROM `web_pages` WHERE ctype IS NULL');
$itemPages = $db->arrayQuery('SELECT * FROM `web_pages` WHERE ctype IS NOT NULL');
?>
<head>
<link href="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="/admin/js/jquery.min.js"></script>
<script src="//b2bot.ru/components/qw.js"></script>
<script src="//b2bot.ru/components/upload/js.js"></script>
<script src="//b2bot.ru/components/toast.js"></script>
<script src="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
</head>
<body>



<div class="p-1">

<div class="input-group mb-1">
 <span class="input-group-text">Надпись на кнопке количества</span>
 <input type="text" class="form-control" placeholder="" id="btnAddTopPrefix">
</div>
		
<div class="input-group mb-1">
 <span class="input-group-text">Надпись на кнопке добавления</span>
 <input type="text" class="form-control" placeholder="" id="btnAddCaption">
</div>
		
		
<div class="input-group mb-3">
	<label class="input-group-text" for="cartPage">Страница корзины</label>
	<select class="form-select" id="cartPage">
		<option value="">Не указана</option>
			<?php foreach($cartPages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
	</select>
</div>
		
<div class="form-check">
 <input type="checkbox" class="form-check-input" id="gategory_items">
 <label class="form-check-label" for="gategory_items">Отобразить товары в той же категории</label>
</div>
		
<div class="input-group mb-3">
	<label class="input-group-text" for="itemPage">Страница товара</label>
	<select class="form-select" id="itemPage">
		<option value="">Не указана</option>
			<?php foreach($itemPages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
	</select>
</div>
		
		
</div>

</body>

<script>

var _translatedFields={
	 "btnAddTopPrefix":{"name":"Надпись на кнопке количества"},
	 "btnAddCaption":{"name":"Надпись на кнопке добавления"},
}

function _getData( res ){
    let data=res.data;
		qw.qs("#btnAddTopPrefix").value=data.btnAddTopPrefix||"";
		qw.qs("#btnAddCaption").value=data.btnAddCaption||"";
		qw.qs("#cartPage").value=data.cartPage||"";
		qw.qs("#itemPage").value=data.itemPage||"";
		qw.qs("#gategory_items").checked=data.gategory_items||"";
		
}

function _sendData(){
    let res =
    {
			 _translatedFields:_translatedFields
			,btnAddTopPrefix: qw.qs("#btnAddTopPrefix").value
			,btnAddCaption: qw.qs("#btnAddCaption").value
			,cartPage: qw.qs("#cartPage").value
			,itemPage: qw.qs("#itemPage").value
			,gategory_items: qw.qs("#gategory_items").checked?1:0
    }
    return res;
}

</script>
