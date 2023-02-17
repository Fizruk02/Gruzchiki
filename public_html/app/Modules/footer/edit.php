<?php
use system\lib\Db;

$db=DB::getInstance();
$menu = $db->arrayQuery('SELECT * FROM `web_menu` ORDER BY name');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="/admin/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<script src="//b2bot.ru/components/qw.js"></script>
<script src="//b2bot.ru/components/upload/js.js"></script>
<script src="//b2bot.ru/components/toast.js"></script>
<style>
		.contacts {
				display:grid;
				grid-template-columns: 1fr 1fr;
				gap: 6px;
		}
</style>
<body>

<div class="p-3">
    <div class="input-group mb-1">
        <label class="input-group-text" for="name">Название</label>
        <input type="text" class="form-control" id="name">
    </div>
		
		<div class="input-group mb-3">
			<label class="input-group-text" for="menu">Меню</label>
			<select class="form-select" id="menu">
				<option value="">Не выбрано</option>
					<?php foreach($menu as $p) echo '<option value="'.$p['id'].'">'.$p['name'].'</option>';?>
			</select>
		</div>
		<div class="contacts">
				<div>
						<span>social</span>
						<div class="input-group mb-1">
						 <span class="input-group-text">vk</span>
						 <input type="text" class="form-control" placeholder="" id="vk">
						</div>
						<div class="input-group mb-1">
						 <span class="input-group-text">tg</span>
						 <input type="text" class="form-control" placeholder="" id="tg">
						</div>
						<div class="input-group mb-1">
						 <span class="input-group-text">email</span>
						 <input type="text" class="form-control" placeholder="" id="email">
						</div>
				</div>
				
				<div class="ms-2 ps-2 border-start border-primary">
				<button type="button" class="btn btn-light btn-sm ms-1" onclick="cl.add()">Колонка контактов <i class="bi bi-plus"></i></button>
				<div id="contacts"></div>
				</div>
		</div>
</div>

</body>
<script>
var frameid;
var _translatedFields={
	"contacts":{"name":"Колонка контактов", "fields":{ "title":"Название", "placeholder":"подсказка" } },
	 //"h2":{"name":"h2 заголовок"},
	};
		var cl={
				add:(t,c)=>{ qw.qs("#contacts").insertAdjacentHTML('beforeend', cl.tmpl(t||"",c||""));resize();},
				tmpl:(t,c)=>{ // let num=qw.qs("#contacts").childElementCount;
				return `<div class="mb-1 dt-contacts">
			<div> <i class="ms-3">${c}</i> <i class="bi bi-x float-end text-danger"  onclick="this.parentNode.parentNode.remove();resize();"></i> </div>
			<textarea class="form-control" placeholder="" style="height: 60px">${t}</textarea>
		<div>`
				},
		}
    function _getData(res) {
        let data = res.data;
				if(data.contacts&&Array.isArray(data.contacts))data.contacts.forEach((it)=>{
						cl.add(it.text);
				})
				qw.qs("#name").value=data.name||"";
				qw.qs("#vk").value=data.vk||"";
				qw.qs("#tg").value=data.tg||"";
				qw.qs("#menu").value=data.menu||"";
				qw.qs("#email").value=data.email||"";

    }

    function _sendData() {
				let contacts=[];
				qw.qsa(".dt-contacts").forEach((it)=>{
						let t=it.querySelector("textarea").value;
						contacts.push({ text:t })
				})
        return {
						name: qw.qs("#name").value,
						menu: qw.qs("#menu").value,
						vk: qw.qs("#vk").value,
						tg: qw.qs("#tg").value,
						email: qw.qs("#email").value,
						contacts:contacts,
        }
    }
		function resize(){window.parent.postMessage({ method:'blockResize', id:frameid }, '*');}
</script>