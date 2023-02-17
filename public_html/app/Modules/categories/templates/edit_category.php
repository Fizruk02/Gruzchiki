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



<div class="p-1 m-0 row">
	<div class="col ps-3 border-end">
		<span class="ps-3">методы и данные:</span>
		<div class="mb-1"><span class="text-primary">$_CAT::$LIST</span><span> - список всех категорий</span></div>
		<div class="mb-1"><span class="text-primary">$_CAT::$CURRENT</span><span> - текущая категория из ЧПУ</span></div>
		<div class="mb-1"><span class="text-primary">$_CAT::$SUBCATS</span><span> - подкатегории 1-го уровня</span></div>
		<div class="form-check mb-1">
		 <input type="checkbox" class="form-check-input" id="goods">
		 <label class="form-check-label" for=""><span class="text-primary">$_CAT::$GOODS</span> - товары</label>
		</div>
			
		<div class="form-check">
		 <input type="checkbox" class="form-check-input" id="console">
		 <label class="form-check-label" for="console">Вывести данные в консоль</label>
		</div>
			
	</div>
		
	<div class="col">
		<span class="ps-4">категория:</span>
		<ul>
				<li>id</li>
				<li>type: cat</li>
				<li>name - имя</li>
				<li>slug - ссылка</li>
				<li>descr - описание</li>
				<li>files - файлы</li>
				<li>preview - картинка</li>
				<li>visible - видимость</li>
				<li>parent - имя родителя</li>
				<li>parent_id - id родителя</li>

		</ul>
</div>
		
	<div class="col">
		<span class="ps-4">товар:</span>
		<ul>
				<li>id</li>
				<li>type: item</li>
				<li>name - имя</li>
				<li>slug - ссылка</li>
				<li>files - файлы</li>
				<li>preview - картинка</li>
				<li>short_description - описание</li>
				<li>description - описание</li>
				<li>visible - видимость</li>
				<li>priority - приоритет</li>
				<li>category - категория (string)</li>
				<li>price_type - Тип цены (string)</li>
				<li>price - цена</li>
				<li>currency - валюта</li>
				<li>unit - ед. изм.</li>
				<li>price_type_id - id типа цены</li>
				<li>price_id - id цены</li>
		</ul>
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
    qw.qs("#goods").checked=data.goods==="1";
		qw.qs("#console").checked=data.console==="1";

    
}

function _sendData(){
    let res =
    {
         _translatedFields:_translatedFields
         ,goods: qw.qs("#goods").checked?1:0
				 ,console: qw.qs("#console").checked?1:0
    }
    return res;
}
function resize(){window.parent.postMessage({ method:"blockResize", id:frameid }, "*");}
</script>
