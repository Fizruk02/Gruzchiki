<style>
    .pointer {
        cursor:pointer;
    }
    .submenuarea {
        max-height: 200px;
        overflow: auto;
    }
    .ellps {
        max-width:200px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .dropdown-menu {
        /*max-height:300px;*/
        /*overflow: auto;*/
    }

    .eventarea .switch {
        cursor:pointer;
        border-radius: 50rem!important;
        border: 1px solid #0D6EFD;
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: .75em;
        font-weight: 700;
        line-height: 1;
        color: #0D6EFD;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;v
    }

    .eventarea .switch-show {
        background: #0D6EFD;
        color: white;
    }
</style>
<?php
    
    /**
     * get
     * tmplt - template id or name
     * 
     */

    $tempName = 'Рассылка '.singleQuery('SELECT IFNULL(count(*)+1, 1) c FROM `mailings` WHERE `repeat` = ""')['c'];

    $startFunction = setting('open_mailing_page');
    if($f=get_custom_function($startFunction)) {
        $f['funcName']();
    } else {
        //echo $startFunction; exit;
    }

    $templates = arrayQuery('SELECT id,name,parent FROM `mailings_templates`');
    $filters = arrayQuery('SELECT id,name,query,parent FROM `mailings_filters`');

?>

<div class="container">
  <div class="input-group mb-2">
     <span class="input-group-text" id="basic-addon1">mailing name</span>
     <input type="text" class="form-control" placeholder="название рассылки" aria-label="mailing name" aria-describedby="basic-addon1" id="mailing-name" value="<?= $tempName?>">
     <span class="input-group-text" id="basic-addon1">date</span>
     <input type="datetime-local" style="max-width:250px" class="form-control" id="date-begin" value=<?= date("Y-m-d").'T'.date("H:i"); ?>>
  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="repeatBtn"><i class="bi bi-arrow-repeat"></i></button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="#" onclick="repeatBtn.innerHTML=`<i class='bi bi-arrow-repeat'></i>`;repeat='';rps();">Не повторять</a></li>
    <li><a class="dropdown-item" href="#" onclick="repeatInterval()">Выбрать частоту отправки</a></li>
    <li><a class="dropdown-item" href="#" onclick="repeatDaily()">Ежедневно в это время</a></li>
    <li><a class="dropdown-item" href="#" onclick="repeatBtn.innerHTML='повтор: ежемесячно';repeat='monthly';rps();">Ежемесячно в это число в это время</a></li>
<!--     <li><a class="dropdown-item" href="#" onclick="repeatBtn.innerHTML='повтор: еженедельно';repeat='weekly';rps();">Ежедневно в этот день недели в это время</a></li> -->
  </ul>
  <button class="btn btn-outline-secondary showeventbtn" type="button"><i class="bi bi-calendar2-x"></i> Событие</button>
  </div>

  <div class="row border rounded border-primary p-3 pt-1 mb-3 eventarea" style="width:fit-content; display:none">

        <div class="d-flex mb-1">

            <i class="bi bi-x-lg me-2 pointer" onclick="deleteEvent()"></i>
            <span>Настройки события</span>
        </div>
        
      <div class="col-auto">
         <div class="input-group mb-2">
            <span class="input-group-text">завершение события</span>
            <input type="datetime-local" style="max-width:200px" class="form-control" id="eventEnd" value=<?= date("Y-m-d", time()+24*60*60).'T'.date("H:i"); ?>>
         </div>
        
        <div class="border-start ps-3"> 
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="" id="eventCheckNotify">
              <label class="form-check-label" for="eventCheckNotify">Уведомлять о завершении события</label>
            </div>

            <div class="d-float mb-3">
                <span class="switch switch-show" data-t="days">За ... дней</span>
                <span class="switch" data-t="hours">За ... часов</span>
            </div>

            <div event-notify-panel="days">
                <div class="row mb-2">
                    <div class="col-auto pt-2 pe-0"><i>время уведомления</i></div>
                    <div class="col-auto">
                        <input type="time" style="max-width:fit-content" class="form-control" id="evNtfTime" value="12:00">
                    </div>

                </div>
                <?php foreach(['Пн.','Вт.','Ср.','Чт.','Пт.','Сб.','Вс.'] as $k=>$d){ ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input mt-1" type="checkbox" id="EvNtf<?=$k?>" checked>
                        <label class="form-check-label" for="EvNtf<?=$k?>"><?=$d?></label>
                    </div>
                <?php }?>
            </div>

            <div event-notify-panel="hours" style="display: none">
                <div class="input-group mb-2">
                    <span class="input-group-text">за</span>
                    <input type="number" style="max-width:100px" class="form-control" id="evNtfHoursTo">
                    <span class="input-group-text">ч.</span>
                </div>
            </div>


         </div>
     </div>
     
     <div class="col">
        <div class="form-floating mb-2">
          <textarea class="form-control" id="eventNotifyText" style="height: 144px">Событие закончится через #days#</textarea>
          <label for="input-form">Текст уведомления (v: #days#, #hours#)</label>
        </div>
     </div>
     
     
     
  </div>
  
  
<div class="row mb-1">
    <div class="col-sm">
        <div class="" id="editor"> </div>
    </div>
    <div class="col-auto">
        <div class="text-left" id="label-form">
            <span class="badge badge-info pointer" action-type="get-var">#username#</span>
            <span class="badge badge-info pointer" action-type="get-var">#first name#</span>
        </div>
    </div>
</div>
  
<div class="form-floating mb-2">
  <textarea class="form-control" placeholder="message text" id="input-form" style="height: 200px"></textarea>
  <label for="input-form">Message text</label>
</div>



<div id="loadbtn"></div>  

<div id="loadform">

</div>

<div class="mt-2" tableform>
   <div class="form-inline" style="padding-bottom: 4px;width:100%;position:sticky;top:10px;">
      <div class="container" style="max-width: 100%;">
         <div class="row">
             <div class="col-auto p-0 d-flex">
              <div class="input-group-append">
                 <form id="js-form" method="post" class="revealator-slideup revealator-delay1 revealator-once">
                    <div class="file-load-form">
                       <div class="form-group" id="fileloadform">
                          <input type="file" name="file" id="file" class="fl-input-file">
                          <label for="file" class="btn btn-outline-secondary mr-1">download from excel</label>
                          <!--<label for="file" class="btn btn-outline-secondary mr-1 js-labelFile" type="button" id="input-excel-table">download from excel</label>-->
                       </div>
                       <div style="text-align: center;display:none;" id="spinform">
                          <div class="spinner-border" role="status" >
                             <span class="sr-only">Loading...</span>
                          </div>
                       </div>
                    </div>
                 </form>
                 <!-- <button class="btn btn-outline-secondary" type="button" id="btn-input-from-area">input field</button> -->
              </div>
             </div>
             
            <div class="col-auto p-0 ms-1">
                <button class="btn btn-outline-secondary" type="button" onclick="$('#modal-kb').modal('show');" ><i class="bi bi-keyboard"></i> Клавиатура</button>
            </div>
            
             <div class="col-auto p-0 ms-1">
                 
                <div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle ellps" type="button" id="filterBtn" data-bs-toggle="dropdown" aria-expanded="false" style="font-weight: 600;">
                    Все пользователи
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterBtn.innerHTML=this.innerHTML;filterSelect(0);" >Все пользователи</a></li>

                    <? function fltrsitms($it){
                        if($it['sub']){
                            return '<li class="dropdown-item submenu"  style="cursor: pointer" href="#">'.$it['name'].'</li>'.
                                   '<ul class="dropdown-menu submenuarea">'.implode("",array_map(function ($t){return fltrsitms($t);},$it['sub'])).'</ul>';
                        } else
                        return '<li><a class="dropdown-item" href="#" onclick="filterBtn.innerHTML=this.innerHTML;filterSelect('.$it['id'].');" >'.$it['name'].'</a></li>';
                    }
                    foreach(recr($filters,0,'id','parent') as $row) echo fltrsitms($row); ?>

                    <? if($GLOBALS['permission_to_use']['user_status']==99){?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="filtersSettings()" ><i class="bi bi-filter"></i> Настройки фильтров</a></li>
                    <?}?>
                  </ul>
                </div>
             </div>
             
             
             <div class="col-sm p-0 ms-1">
                 
                <div class="dropdown" id="templateDropdown">
                    
                  <button class="btn btn-outline-secondary dropdown-toggle ellps" type="button" id="templateBtn" data-bs-toggle="dropdown" aria-expanded="false" style="font-weight: 600;">
                    Шаблон
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="templateBtn" id="templatesArea">

                  </ul>
                </div>
             </div>

            <div class="col-auto p-0">
               <div class="input-group w-100">
                  <input type="search" class="form-control" placeholder="поиск..." searchtext>
                  <div class="input-group-append" style="height: 38px;">
                    <button class="btn btn-outline-secondary" type="button" search>искать</button>
                    <button class="btn btn-outline-secondary" style="color: #b90605;font-weight: 600;" type="button" exporttopdf>pdf</button>
                    <button class="btn btn-outline-secondary" style="color: #1f7244;font-weight: 600;" type="button" exporttoexcel>excel</button>
                    <button class="btn btn-success" type="button" onclick="create()">create mailing</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div n-div='4' style="overflow-x: auto;width:100%;">
      <table class="table table-hover table-sm" id="table-mailing" display="mailing">
         <thead class="thead-light" id="table-mailing-head">
            <tr>
                <th style="width:40px">
                  <div class="custom-control custom-switch">
                     <input type="checkbox" class="custom-control-input" checked onclick="check_all($(this).is(':checked')?'1':'0')" id="check-all">
                     <label class="custom-control-label" for="check-all"></label>
                  </div>
                </th>
                <th>id</th>
                <th>username</th>
                <th>first name</th>
            </tr>
         </thead>
         <tbody id="table-mailing-tbody">

         </tbody>
      </table>
   </div>
   
</div>


    
</div>

 <div class="modal fade" id="modal-addresses" tabindex="0" role="dialog" aria-labelledby="addressesModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 600px">
       <div class="modal-content">
          <div class="modal-header">
             <h5 class="modal-title" id="addressesModalLabel">список id</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
             <div>
                <div class="input-group">
                   <textarea class="form-control" aria-label="адреса" placeholder="список id через запятую или столбиком" id="input-addresses-form"  rows=10></textarea>
                </div>
             </div>
          </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-secondary" id="addresses-from-area">Ok</button>
          </div>
       </div>
    </div>
 </div>


<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >Настройка фильтров</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
      <div class="col-sm-3 border-end">
          
            <div class="d-flex mb-1">
              <input class="form-control me-2" type="search" placeholder="Search" oninput="if(this.value =='')  {$('[scripts]').css('display', 'block')} else {  $('[scripts]').css('display', 'none'); $(`[scripts][search*='${this.value}']`).css('display', 'block') }">
              <button class="btn btn-outline-success" type="submit" onclick="newFilter()">Создать</button>
            </div>
            <div class="list-group" id="filterslist"></div>
            </div>
            <div class="col-sm">
                <div class="input-group mb-1">
                  <span class="input-group-text">Name</span>
                  <input type="text" class="form-control" placeholder="filter name" id="filterNameEdit">
                </div>
                <textarea class="form-control mb-1" placeholder="sql query" rows=6 id="filterQueryEdit"></textarea>
            
                <div class="input-group">
                  <label class="input-group-text" for="filterCategories">Категория</label>
                  <select class="form-select" id="filterCategories">
                  </select>
                <button type="button" class="btn btn-outline-danger px-4 btn-sm" onclick="deleteFilter()">Delete</button>
                <button type="button" class="btn btn-outline-success px-5 btn-sm" onclick="saveFilter()"> Save </button>
                </div>

           
            </div>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal-kb" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title">Клавиатура</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body" id="kbmodalbody">

        <div class="px-3 py-2 border rounded mb-2"> 
            <div class="row">
                <div class="col">
                    <span style="margin: 3px 3px 0 0;">Опрос </span>
                    <button type="button" class="btn btn-light btn-sm " onclick="polAdd()"><i class="bi bi-plus"></i> вариант</button>
                </div>
                <div class="col-auto">
                <div class="form-check" id="form-poll-ds">
                  <input class="form-check-input" type="checkbox" value="" id="poll-ds">
                  <label class="form-check-label" for="poll-ds"> Разрешать снимать голос </label>
                </div>

                <div class="form-check" id="form-poll-ds">
                    <input class="form-check-input" type="checkbox" value="" id="poll-custom">
                    <label class="form-check-label" for="poll-custom"> Кнопка "Свой вариант" </label>
                </div>

                </div>
            </div>

            <div id="poll-area" class="mt-1">
            </div>
            <div class="input-group input-group-sm mb-3" id="poll-cols" style="display:none">
              <span class="input-group-text">кнопок в ряд (до 8)</span>
              <input type="number" min="1" max="8" class="form-control" style="max-width:60px" id="poll-cols-edit" value="1">
            </div>
        </div>   
          
          
        <div class="px-3 py-2 border rounded mb-2"> 
            <span style="margin: 3px 3px 0 0;">Inline </span>
            <button type="button" class="btn btn-light btn-sm " onclick="inlineAdd()"><i class="bi bi-plus"></i> кнопка</button>
            <div class="p-2 border rounded mb-2" id="mess-inl-kb"> 
                
            </div>  
        </div>  
   
       </div>

    </div>
 </div>
</div>



<script>



var filegroup = '',filter=false,filterEditId=false,repeat="",weekdays=[0,0,0,0,0,0,0], interval=3,eventstatus=0;eventendtype="days";
var ptmplt = "<?=$_GET['tmplt']??($_GET['template']??'');?>";
var filters = <?=json_encode($filters);?>;
var templates = <?=json_encode($templates);?>;

async function deleteEvent(){
    alertmodcreate({'title':'Удалить завершение события?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    $(".eventarea").hide(50);
    $(".showeventbtn").show();
    eventstatus=0;

}

$(document).ready(function () {
    $(".showeventbtn").on("click", function(){
        eventstatus=1;
        $(".eventarea").show(50);
        $(".showeventbtn").hide();
    })

    qw.click(".eventarea .switch", function (){
        eventendtype=this.dataset.t;
        qw.removeClass('.switch','switch-show',this);
        qw.hide("[event-notify-panel]");
        qw.show(`[event-notify-panel="${eventendtype}"]`);
    })

});

async function repeatInterval(){
    promptmodcreate({'title':'Интервал отправки в часах','btnOk':'Сохранить','btnNo':'Отмена','size':'sm'},
    [
        {label:'Каждые ... ч.', value:interval, type:'number'}
    ]);
    let result = await promptmod; if(!result) return;
    interval=result[0];
    repeatBtn.innerHTML='повтор: каждые '+interval+'ч.';repeat='interval';rps();
}

async function repeatDaily(){
    promptmodcreate({'title':'Дни недели','btnOk':'Сохранить','btnNo':'Отмена','size':'sm'},
    [
         {checkbox:true, label:"Понедельник", checked:weekdays[1]}
        ,{checkbox:true, label:"Вторник", checked:weekdays[2]}
        ,{checkbox:true, label:"Среда", checked:weekdays[3]}
        ,{checkbox:true, label:"Четверг", checked:weekdays[4]}
        ,{checkbox:true, label:"Пятница", checked:weekdays[5]}
        ,{checkbox:true, label:"Суббота", checked:weekdays[6]}
        ,{checkbox:true, label:"Воскресение", checked:weekdays[0]}
    ]);
    let result = await promptmod; if(!result) return;
    weekdays[0]=result[6];weekdays[1]=result[0];weekdays[2]=result[1];weekdays[3]=result[2];weekdays[4]=result[3];weekdays[5]=result[4];weekdays[6]=result[5];
    repeatBtn.innerHTML='повтор: ежедневно';repeat='daily';rps();
}




function q(s){return document.querySelector(s);}


$(document).ready(function () {

$("#editor").html(appTelegramEditor.init("input-form"));
   
$('#templateDropdown').on('show.bs.dropdown', function () {
    let h=''
    templates.forEach(function(it) {
        if(it.parent==0)h+= templateTemplate(it);
    });
    h+=`<li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="#" onclick="templateNew()" >Сохранить настройки в новый шаблон*</a></li>`;
    $("#templatesArea").html(h);


});

if(ptmplt!=="") templateSelect(ptmplt);

})

function templateTemplate(it){
if(it.sub){
// return `<li class="dropdown-item submenu"  style="cursor: pointer" href="#">'.$it['name'].'</li>
// <ul class="dropdown-menu">'.implode("",array_map(function ($t){return templateTemplate($t);},$it['sub'])).'</ul>`;
} else
return `<li class="row" templateblock="${it.id}">
<div class="col-sm">
<a class="dropdown-item" href="#" onclick="templateSelect(${it.id});" >${it.name}</a>
</div>
<div class="col-auto me-1 ps-0 mt-1" style="cursor: pointer" title="Сохранить">
    <span onclick="saveTemplate('${it.id}')">💾</span>
</div>
<div class="col-auto me-1 ps-0 mt-1" style="cursor: pointer" title="Удалить">
    <i class="bi bi-x-lg text-danger" onclick="deleteTemplate('${it.id}')"></i>
</div>
</li>`;
}

function templateSelect(id){
    
    $.post("p.php?q=getTemplate", { id:id },function(res) {
        if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
        templateBtn.innerHTML=res.name;
        substitution_of_values(res.data);
    },'json');
}

function substitution_of_values(p){
    q("#mailing-name").value=p.name??"";
    //q("#date-begin").value=p.date??"";
    q("#input-form").value=p.text??"";
    repeat=p.repeat??"";
    interval=p.interval??"";
    weekdays:p.weekdays??[];

    repeatBtn.innerHTML=p.repeatBtn;

    $('#poll-ds').prop('checked', p.ds==="1");
    $('#poll-cols-edit').val(p.plcol);
    $("#poll-area").html("");
    $("#mess-inl-kb").html("");
    if(Array.isArray(p.poll_data))
    p.poll_data.forEach(function(it) {
        polAdd(it);
    });
    
    if(Array.isArray(p.inline))
    p.inline.forEach(function(it) {
        inlineAdd(it[0],it[1]);
    });

    $('#loadbtn').html(appUpload.form({ id:"1", uploadFunc:'updateFilegroup', deleteFunc:'updateFilegroup',  group:p.filegroup }));
    $('#loadform').html(appUpload.container({ id:"1",  files:p.files}));
    
    if(p.event){
        eventstatus=p.event.status;
        eventendtype=p.event.endtype;

        $(`[data-t="${eventendtype}"]`).click();
        if(eventstatus===1||eventstatus==="1"){
            $(".eventarea").show(50); $(".showeventbtn").hide();
        } else {
            $(".eventarea").hide(50); $(".showeventbtn").show();
        }
        
        $("#evNtfHoursTo").val(p.event.hoursto??"");
        $("#evNtfTime").val(p.event.ntftime??"12:00");
        $("#eventNotifyText").val(p.event.ntftext??"Событие закончится через #days#");
        
        $('#eventCheckNotify').prop('checked', p.event.ntf==="1");
    
        if(Array.isArray(p.event.ntfwkdays))
        p.event.ntfwkdays.forEach(function(it, i) {
            $('#EvNtf'+i).prop('checked', it==="1");
        });
    
            
    }

}

async function templateNew(){
    let d=templateData();
    promptmodcreate({'title':'Новый шаблон*','btnOk':'Сохранить','btnNo':'Отмена'},
    [ {label:"Название шаблона", value:d.name} ]);
    let result = await promptmod; if(!result) return;    
    let n =result[0]===""?'Новый шаблон':result[0];
    
    $.post("p.php?q=newTemplate", { n:n,d:d },function(res) {
    if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
        templates.push({id:res.id,name:n,parent:"0"});
        toast('Шаблон', `Шаблон <b>${n}</b> создан`);
    },'json');
}


async function saveTemplate(id){
    alertmodcreate({'title':'Перезаписать шаблон?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    let d=templateData();
    
    $.post("p.php?q=saveTemplate", { id:id,d:d }, function(res) {
        if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
    toast('Шаблон', `Шаблон перезаписан`);
    },"json");
}

async function deleteTemplate(id){
    alertmodcreate({'title':'Удалить шаблон?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("p.php?q=deleteTemplate", { id:id }).done(function(data) {
        var res = jQuery.parseJSON(data);
        if(res.success!=='ok') return toast('Ошибка', res.err, 'error');

        templates.splice(getIndex('id',id,templates), 1);
        $(`[templateblock="${id}"]`).remove();

    });
}

function templateData(){

    let poll_data=[]
        ds = $('#poll-ds').is(':checked') ?1:0,
        plcol=$('#poll-cols-edit').val();

    $("[poll-item]").each(function(i,elem) {
        if($(elem).val()!=="") poll_data.push($(elem).val());
    });
    
    let txt,cb,inl=[];
    $("[inlkbrow]").each(function(i,elem) {
        txt=$(elem).find('[text]').val();
        cb=$(elem).find('[cb]').val();
        if(txt!==""&&cb!=="")inl.push([txt,cb])
    });
    
    let entfs={};
    [0,1,2,3,4,5,6].forEach(function(i) {
    	entfs[i]=$('#EvNtf'+i).is(':checked') ?1:0
    });
    return {
	name: q("#mailing-name").value,
	date: q("#date-begin").value,
	text: q("#input-form").value,
	repeat:repeat,
    repeatBtn:repeatBtn.innerHTML,
	weekdays:weekdays,
	interval:interval,
	filter:filter,
	filegroup: filegroup,
	inline:inl,
	poll_data:poll_data,
	ds:ds,
	plcol:plcol,
	event: {
    	status:eventstatus,
        end: q("#eventEnd").value,
        ntf: $('#eventCheckNotify').is(':checked') ?1:0,
        endtype:eventendtype,
        hoursto:qw.qs("#evNtfHoursTo").value,
        ntftime: q("#evNtfTime").value,
        ntfwkdays: entfs,
        ntftext:q("#eventNotifyText").value,
	},

    }
}

function inlineAdd(vl1="",vl2=""){
    $('#mess-inl-kb').append(`<div class="input-group mb-1" inlkbrow> <input type="text" placeholder="Текст" class="form-control" text value="${escapeHtml(vl1)}"> <input type="text" placeholder="Ссылка или значение" class="form-control" cb value="${escapeHtml(vl2)}">  </div>`);
}

function polAdd(val=""){
    if($("[poll-item]").length>0) $('#poll-cols').show(100); else $('#poll-cols').hide(100);
    $(`<input type='text' class='form-control mb-1' placeholder='Текст' poll-item value="${val}">`).appendTo('#poll-area').show('slow');
}

function rps(){
    if(repeat!==""&&filter===-1){
        $('#filterBtn').removeClass('btn-outline-secondary');
        $('#filterBtn').addClass('btn-outline-warning');
    }  else {
        $('#filterBtn').addClass('btn-outline-secondary');
        $('#filterBtn').removeClass('btn-outline-warning'); 
    }
}

function filtersSettings(){
    let list='';
    filters.forEach(function(it) {
    	if(it.parent==0)list+= filterTemplate(it);
    });
    $('#filterNameEdit').val("");
    $('#filterQueryEdit').val("");
    $('#filterCategories').html("");
    $('#filterslist').html(list);
    filterEditId=false;
    $('#filtersModal').modal('show');
}

function getFilter(id){
    filterEditId=id.toString();
    
    let ind=getIndex('id',filterEditId,filters);
    let h='<option value="0">Без категории</option>';
    filters.forEach(function(it) {
        if(it.id!=filterEditId)
    	h+= `<option value="${it.id}">${it.name}</option>`;
    });
    $('#filterCategories').html(h);
    $('#filterCategories').val(filters[ind].parent);
    $('#filterNameEdit').val(filters[ind].name);
    $('#filterQueryEdit').val(filters[ind].query);

}


async function newFilter(){
    promptmodcreate({'title':'Новый фильтр*','btnOk':'Сохранить','btnNo':'Отмена'},
    [{label:'Название'}]); 
    let result = await promptmod; if(!result) return;
    let name = result[0];
    
    $.post("p.php?q=newFilter", { name:name }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        filterEditId=res.id.toString();
        let p={id:res.id,name:name,parent:"0",query:""};
        $('#filterslist').append(filterTemplate(p));
        filters.push(p);
        $(`[filter="${filterEditId}"]`).click();
    });
}

function saveFilter(){
    let name = $('#filterNameEdit').val();
    let query = $('#filterQueryEdit').val();
    let parent = $('#filterCategories').val();
    if(filterEditId===false) return toast('Ошибка', 'Фильтр не выбран', 'error');
    if(name=="") return toast('Ошибка', 'Укажите название фильтра', 'error');
    
    $.post("p.php?q=saveFilter", { id:filterEditId,name:name,query:query,parent:parent }).done(
    function(data) {
    	let res = jQuery.parseJSON(data);
    	if(res.success!=='ok')
    		return toast('Ошибка', res.err?res.err:'error', 'error');
        toast('Сохранение', 'Фильтр сохранён');
        $(`[filter="${filterEditId}"]`).html(name);
        let ind=getIndex('id',filterEditId,filters);
        filters[ind].name=name;
        filters[ind].query=query;
        filters[ind].parent=parent;
        if(parent==0) parent='';
        if($(`[filterblock="${filterEditId}"]`).parent().attr('id')!='filterslist'+parent){
            $(`[filterblock="${filterEditId}"]`).prependTo( $('#filterslist'+parent) ); 
            if(parent!=0)  
            $('#filterslist'+parent).show(100);
            checkFltrCollape();
        }
        
    });
}

async function deleteFilter(id){
    if(filterEditId===false) return toast('Ошибка', 'Фильтр не выбран', 'error');
    alertmodcreate({'title':'Удалить фильтр?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("p.php?q=deleteFilter", { id:filterEditId }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        filters.forEach(function(item, i) { // переносим дочерние категории в основную
            if(item.parent===filterEditId){
                filters[i]['parent']="0";
                $(`[filterblock="${item.id}"]`).prependTo( $('#filterslist') );
            }
           
        });
        $(`[filterblock="${filterEditId}"]`).remove();
        filters.splice(getIndex('id',filterEditId,filters), 1);
        
        $('#filterNameEdit').val("");
        $('#filterQueryEdit').val("");
        filterEditId=false;
        
    });
}


function checkFltrCollape(){
    filters.forEach(function(it, i) {
        if($('#filterslist'+it.id).children().length==0){
            $('#collapse-btn-'+it.id).hide();
            $('#filterslist'+it.id).hide();
        } else {
            $('#collapse-btn-'+it.id).show();
        }
    });
}
function toggle(id){
  if($('#filterslist'+id).is(':visible'))$("#filterslist"+id).hide(100);else $("#filterslist"+id).show(100);
}
function filterTemplate(f){
    let children='';
    filters.forEach(function(it) {
        if(it.parent==f.id) children += filterTemplate(it);
    });
    return `<div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" filterblock="${f.id}">
            <div class="row">
                <div class="col-sm" style="cursor:pointer" filter="${f.id}" onclick="getFilter(${f.id})">${f.name}</div>
                <div class="col-auto p-0" style="${children!==""?"":"display:none;"}" id="collapse-btn-${f.id}">
                 <button class="btn border-0 p-0 px-1" type="button" onclick="toggle('${f.id}')"> <i class="bi bi-chevron-expand"></i>  </button>
                </div>
            </div>
          <div class="collapse mt-2 list-group" childrenlist id="filterslist${f.id}">${children}</div>
        </div>`;
}


function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

</script>





















