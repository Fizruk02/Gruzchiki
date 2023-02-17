<?php
# описание модуля
$description = 'описание модуля Cart';
use system\lib\Db;

$db=DB::getInstance();
$contentPages = $db->arrayQuery('SELECT * FROM `web_pages` WHERE ctype=1');
$endPages = $db->arrayQuery('SELECT * FROM `web_pages` WHERE ctype IS NULL');
?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="/admin/js/jquery.min.js"></script>
		<script src="//b2bot.ru/components/qw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
	#fields {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 10px;
	}
	#fields .custom-field{
			position: relative;
			display: block;
			padding: 0.5rem 1rem 1rem 1rem;
			color: #212529;
			text-decoration: none;
			background-color: #fff;
			border: 2px solid #0d6efd;
			border-radius: 10px;
	}
	
	.btn_add,.btn_remove {
			cursor: pointer;
	}
</style>
</head>
<body class="p-1">
<button class="btn btn-outline-secondary p-0 custom" style="border: 1px solid transparent; border-radius: 11px; height: 18px; width: 18px;" data-bs-toggle="tooltip" data-bs-html="true" title="Description" data-bs-content="<?=htmlspecialchars($description)?>"><i class="bi bi-info-circle"></i></button>
<span>КОРЗИНА</span>
<hr>


        <div class="border shadow p-3 pt-2 mb-3">
						
						<div class="input-group mb-3">
							<label class="input-group-text" for="itemPage">Страница товара</label>
							<select class="form-select" id="itemPage">
								<option value="">Без страницы</option>
									<?php foreach($contentPages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
								

							</select>
						</div>
						
        </div>

        <div class="border shadow p-3 pt-2 mb-3">
						
						<div class="input-group mb-3">
								<div class="form-check">
								 <input type="checkbox" class="form-check-input" id="delivery">
								 <label class="form-check-label" for="delivery">Доставка</label>
								</div>
						</div>
						
        </div>
				
        <div class="border shadow p-3 pt-2 mb-3">
 
						
				<p>
					<button class="btn" type="button" id="collapseFeedbackBtn" data-bs-toggle="collapse" data-bs-target="#collapseFeedback" aria-expanded="false" aria-controls="collapseFeedback">
						ОБРАТНАЯ СВЯЗЬ
					</button>
				</p>
				<div class="collapse" id="collapseFeedback">
					<div class="card card-body">

						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="call" id="fb-call">
							</div>
								<label class="input-group-text" for="fb-call">Звонок</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой номер телефона" data-feedback="call">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="telegram" id="fb-telegram">
							</div>
								<label class="input-group-text" for="fb-telegram">Telegram</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в Telegram" data-feedback="telegram">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="whatsapp" id="fb-whatsapp">
							</div>
								<label class="input-group-text" for="fb-whatsapp">Whatsapp</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в Whatsapp" data-feedback="whatsapp">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="viber" id="fb-viber">
							</div>
								<label class="input-group-text" for="fb-viber">Viber</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в Viber" data-feedback="viber">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="instagram" id="fb-instagram">
							</div>
								<label class="input-group-text" for="fb-instagram">Instagram</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в Instagram" data-feedback="instagram">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="facebook" id="fb-facebook">
							</div>
								<label class="input-group-text" for="fb-facebook">Facebook</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в Facebook" data-feedback="facebook">
						</div>
						
						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="vk" id="fb-vk">
							</div>
								<label class="input-group-text" for="fb-vk">ВКонтакте</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в VK" data-feedback="vk">
						</div>

						<div class="input-group mb-3">
							<div class="input-group-text">
								<input class="form-check-input" type="checkbox" feedback="ok" id="fb-ok">
							</div>
								<label class="input-group-text" for="fb-ok">Одноклассниках</label>
							<input type="text" class="form-control feedback_placeholders" placeholder="Оставьте свой контакт в OK" data-feedback="ok">
						</div>
						
						<span>Подпись к разделу обратной связи</span>
						<div class="input-group mb-1">
						 <input type="text" class="form-control" placeholder="Выберите желаемый способ связи" id="feedback_title">
						</div>
						
						</div>
				</div>
							
        </div>
				
        <div class="border shadow p-3 pt-2 mb-3">
        	<div>Список полей ввода
        			<i class="bi bi-plus-lg ms-3 btn_add" onclick="cl.add()">добавить</i>
        	</div>
        	<div id="fields"> </div>
        </div>
				
				
				
				
				
		<div class="border shadow p-3 pt-2 mb-3">
				<div class="text-end w-100"><b>СТРАНИЦА ПОСЛЕ ОТПРАВКИ ЗАКАЗА</b></div>
				<div class="form-check">
				 <input type="checkbox" class="form-check-input" id="sendBtn">
				 <label class="form-check-label" for="sendBtn">Отобразить кнопку отправки</label>
				</div>
					<select class="form-select" id="end_page">
						<option value="">Не выбрана</option>
							<?php foreach($endPages as $p) echo '<option value="'.$p['id'].'">'.$p['title'].'</option>';?>
					</select>


				
		</div>

</body>

<script>
    var frameid;
    var tree_items=[];
    var active_node=false;
    var tree_multiple = true;
    var tree_plugins = ['dnd', 'wholerow', 'contextmenu'];
		var _translatedFields={
			 "feedback_title":{"name":"Подпись к разделу обратной связи"},
			 "feedback_placeholders":{"name":"Подсказка в поле обратной связи:"},
			 "fields":{"name":"Дополнительное поле", "fields":{ "title":"Название", "placeholder":"подсказка" } },
			};

		
		
var cl={
    add:(id,t,p,r,v)=>{ qw.qs("#fields").insertAdjacentHTML('beforeend', cl.tmpl(id||"",t||"",p||"",r||"",v||""));resize()},
    tmpl:(fid,title,placeholder,required,type)=>{
        return `<div class="custom-field dt-fields">
                <input class="form-check-input me-1 cf-rq" type="checkbox" ${required==="1"?"checked":""}> Required
                <i class="bi bi-x float-end btn_remove" onclick="this.parentNode.remove()"></i>
                <input type="text" class="form-control mb-1 cf-id" placeholder="Field id" value="${fid}">
                <input type="text" class="form-control mb-1 cf-title" placeholder="Title" value="${title}">
                <input type="text" class="form-control mb-1 cf-pl" placeholder="Placeholder" value="${placeholder}">
                <div class="input-group ">
                        <span class="input-group-text">Тип</span>
                        <select class="form-control cf-type">
                            <option value="" ${type===""?"selected":""}></option>
                            <option value="input" ${type==="input"?"selected":""}>Поле ввода</option>
                            <option value="multitext" ${type==="multitext"?"selected":""}>Многострочное поле ввода</option>
                            <option value="checkbox" ${type==="checkbox"?"selected":""}>Чекбокс</option>
                            <option value="file" ${type==="file"?"selected":""}>Ввод файла</option>
                            <option value="append" ${type==="append"?"selected":""}>Добавление полей</option>
                        </select>
                </div>
        </div>`;
    },
}



    function _getData( res ){
				
        let data=res.data;
				if(data.feedback_placeholders){
				for(let q in data.feedback_placeholders)
						if(el=qw.qs(`.feedback_placeholders[data-feedback="${q}"]`)) el.value=data.feedback_placeholders[q];
				} else {
						if(el=qw.qs(`.feedback_placeholders[data-feedback="call"]`)) el.value="Оставьте свой номер телефона";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="telegram"]`)) el.value="Оставьте свой контакт в Telegram";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="whatsapp"]`)) el.value="Оставьте свой контакт в Whatsapp";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="viber"]`)) el.value="Оставьте свой контакт в Viber";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="instagram"]`)) el.value="Оставьте свой контакт в Instagram";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="facebook"]`)) el.value="Оставьте свой контакт в Facebook";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="vk"]`)) el.value="Оставьте свой контакт в VK";
						if(el=qw.qs(`.feedback_placeholders[data-feedback="ok"]`)) el.value="Оставьте свой контакт в OK";
				}
				qw.qs("#feedback_title").value=data.feedback_title||"";
				qw.qs("#itemPage").value=data.itemPage||"";
				qw.qs("#end_page").value=data.end_page||"";
				qw.qs("#sendBtn").checked=data.sendBtn==="1";
				qw.qs("#delivery").checked=data.delivery==="1";

				if(data.feedback&&Array.isArray(data.feedback)) data.feedback.forEach(function(it){
						$(`[feedback="${it}"]`).prop("checked", true);
				})
				if(data.fields&&Array.isArray(data.fields)) data.fields.forEach((it)=>{
						cl.add(it.id,it.title,it.placeholder,it.required,it.type);
				})

        resize();
				
				qw.event("#collapseFeedbackBtn","click",()=>{
						
						setTimeout(resize, 300);
				})
				
    }

    function _sendData(){
		let fields=[];
		qw.qsa(".dt-fields").forEach((it)=>{
            fields.push({
            	id: it.querySelector(".cf-id").value,
            	title: it.querySelector(".cf-title").value,
            	placeholder: it.querySelector(".cf-pl").value,
            	required: it.querySelector(".cf-rq").checked ?1:0,
            	type: it.querySelector(".cf-type").value,
            })
		})
				
				let feedback_placeholders={};
				qw.qsa(".feedback_placeholders").forEach((el)=>{
						feedback_placeholders[el.dataset.feedback]=el.value;
				});
				
        let feedback=[];
        $("[feedback]:checked").each(function(i,it){
            feedback.push($(this).attr("feedback"));
        })
        let res =
        {
						 feedback: feedback
						,fields:fields
						,feedback_placeholders:feedback_placeholders
						,feedback_title: qw.qs("#feedback_title").value
						,itemPage: qw.qs("#itemPage").value
						,end_page: qw.qs("#end_page").value
						,sendBtn: qw.qs("#sendBtn").checked?1:0
						,delivery: qw.qs("#delivery").checked?1:0
						
			,_translatedFields:_translatedFields
        }
        return res;
    }
function resize(){window.parent.postMessage({ method:'blockResize', id:frameid }, '*');}
</script>

