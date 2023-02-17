<?php 
use system\lib\Db;
$db=DB::getInstance();
//$contentPages = $db->arrayQuery('');

$cats = $db->arrayQuery('SELECT id, IF(parent_id = 0, "#", parent_id) AS `parent`, category as `text` FROM s_categories ORDER BY `parent`, `number`, `category`');

$pages = $db->arrayQuery('SELECT * FROM `web_pages` WHERE ctype = 1');
?>
<head>
<link href="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
 <script src="/admin/js/jquery.min.js"></script>
<script src="//b2bot.ru/components/qw.js"></script>
<script src="//b2bot.ru/components/upload/js.js"></script>
<script src="//b2bot.ru/components/toast.js"></script>
<script src="/admin/js/jsTree/jstree.min.js"></script>
<link rel="stylesheet" href="/admin/js/jsTree/themes/default/style.min.css" />
<!-- <script src="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script> -->
</head>
<body>



<div class="p-1 row" style="width:fit-content">

    <div id="c_cats" class="col-auto border-end"></div>
    
    <div class="col">
    
        <div class="input-group mb-1">
            <span class="input-group-text" id="basic-addon1">Заголовок</span>
          <input type="text" class="form-control" placeholder="" id="title">
        </div>
				

				
<div class="input-group mb-3">
	<label class="input-group-text" for="catPage">Страница категории</label>
	<select class="form-select" id="catPage">
		<option value="">Не выбрана</option>
			<?php foreach($pages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
	</select>
</div>
				

		</div>
</div>



</body>

<script>
var frameid;
var _translatedFields={
     "title":{"name":"Title"},
     //"fields":{"name":"Дополнительное поле", "fields":{ "title":"Название", "placeholder":"подсказка" } },
     }
function _getData( res ){
    let data=res.data||{title:"",catPage:[]};
    qw.qs("#title").value=data.title;
    qw.qs("#catPage").value=data.catPage;
    catInit(data.cats||[]);
		resize()
}


function catInit(ids){
    $("#c_cats").jstree({
        core: {
            multiple:1, data: <?php echo json_encode($cats);?>,
        },
    }).on('loaded.jstree', function(e) {
        ids&&Array.isArray(ids)&&ids.forEach((i)=>{
            $(e.target).jstree("select_node", `#${i}`);
        })
    });
}

function _sendData(){
    let res =
    {
         _translatedFields:_translatedFields,
				cats:$("#c_cats").jstree().get_selected()||[],
        title    : qw.qs("#title").value||'[]',
        catPage    : qw.qs("#catPage").value,
    }
    return res;
}
function resize(){window.parent.postMessage({ method:"blockResize", id:frameid }, "*");}
</script>
